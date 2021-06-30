<?php
/**
 *
 * Supports scheduling NPS surveys with the Delighted API.
 * Intended to be run daily... via cron; in support of multiple brands.
 *
 */

/* ------------------------------------------

    Init

--------------------------------------------- */

    ini_set('display_errors', '1');
    // require_once $_SERVER['HOME'].'magento//Mage.php';
    Mage::app();

    date_default_timezone_set("America/Chicago");

    $mode = 'live'; // test or live

    $nps = null;
    $nps['config']['prefs'] = [
        'days_to_wait' => 5 // Recommendation 5-7
    ];
    $nps['config']['api']['key'] = [
        'dn' => 'mcu8Fa6aPMFUY3XlMjlamskKiE1uW8ql'
        ,'fa' => 'hB1qb8yq1gmmtsas81l1KShdoSS8tN8m'
        ,'1215' => 'NsXB7HEl9MGlk0ptZ8gbQLLUz109bID5'
        ,'fc' => 'tfZuI4Tl5SJcwGRclbq9CvH7ZqycjM5A'
    ];


/* ------------------------------------------

    Functions

--------------------------------------------- */

    function dbReadViaSql ($q) {
        $mdb = Mage::getSingleton('core/resource')->getConnection('core_read');
        return $mdb->fetchAll($q);
    }
    function dbWriteViaSql ($q) {
        $mdb = Mage::getSingleton('core/resource')->getConnection('core_write');
        return $mdb->query($q);
    }

    function getDelayToWeekday($sendDay = '', $targetDay = '') {

        if (empty($sendDay)) $sendDay = date('Y-m-d', time());
        $sendDate = new DateTime($sendDay);
        $nowDate = new DateTime();
        $nextWeekDay = new DateTime($sendDay . ' +0 Weekday');

        $daysDiff = $sendDate->diff($nextWeekDay);

        return ($daysDiff->format('%a') * 86400) + 300; // Current time on a weekday, plus 5 mins

    }

/**
 * Get orders data from X number of days ago, including new reseller ratings link
 * @param int $numDaysAgo
 * @return mixed
 */
    function getNpsOrdersData($numDaysAgo = 1) {

        global $nps;
        if ($numDaysAgo <= 0) $numDaysAgo = 1;

        $sql = "select sfosh.parent_id as order_id, sfosh.status, DATE_FORMAT(sfosh.created_at,'%Y-%m-%d') as date_delivered, sfo.increment_id as order_number, sfo.customer_email, sfo.customer_id, sfo.customer_firstname, sfo.customer_lastname, sfo.new_customer, DATE_FORMAT(c.created_at,'%Y-%m-%d') as customer_account_created, sfo.store_id, 'Diamond Nexus' as store_name,
CASE
    WHEN sfo.store_id = 1 THEN 'Web'
    WHEN sfo.store_id = 5 THEN 'Call Center'
    WHEN sfo.store_id = 8 THEN 'Warranty'
    WHEN sfo.store_id = 18 THEN 'Repair'
    ELSE null
END
as store_channel,
sfo.sales_person_id,
u.firstname as sales_person_firstname,
sfo.new_customer, DATE_FORMAT(sfo.created_at,'%Y-%m-%d') as order_date
from sales_flat_order_status_history sfosh
join sales_flat_order sfo on (sfosh.parent_id = sfo.entity_id)
left join admin_user u on (u.user_id = sales_person_id)
left join customer_entity c on (sfo.customer_id = c.entity_id)
where sfosh.status = 'delivered'
and sfo.status = 'delivered'
and sfosh.created_at >= ( CURDATE() - INTERVAL ".$numDaysAgo." DAY )
and sfosh.created_at <= ( CURDATE() - INTERVAL ".($numDaysAgo - 1)." DAY )
and sfo.store_id IN (1,5,8,18)
order by sfosh.created_at asc
limit 0, 1000";
        $srcOrdersData = dbReadViaSql($sql);

        // Add links for Promotors; switching to WeddingWire by default 5/29/2019 as per Bill
        //$ordersData = addResellerRatingsLinks($srcOrdersData);
        $ordersData = addWeddingWireRatingsLinks($srcOrdersData);

        return $ordersData;

    }

    function addWeddingWireRatingsLinks($ordersData) {

        foreach ($ordersData as &$o) {

            $output = 'https://www.weddingwire.com/shared/rate/new?vid=a12857f9b977423e';

            // Must define both CTA and link for it to be valid
            $o['thank_you_link_url_if_promoter'] = $output;
            $o['thank_you_link_text_if_promoter'] = 'WeddingWire >';

        }

        return $ordersData;

    }

    function addResellerRatingsLinks($ordersData) {

        foreach ($ordersData as &$o) {

            $output = 'https://www.resellerratings.com';

            if (!empty($o['order_number']) && !empty($o['customer_email']) && !empty($o['order_date'])) {

                $output = 'https://www.resellerratings.com/store/survey/direct/Diamondnexus_com/email/'.$o['customer_email'].'/invoice/'.$o['order_number'].'/date/'.substr($o['order_date'],0,10);

            }

            // Must define both CTA and link for it to be valid
            $o['thank_you_link_url_if_promoter'] = $output;
            $o['thank_you_link_text_if_promoter'] = 'Reseller Ratings >';

        }

        return $ordersData;

    }

    function connectSurveysApi($brand = 'dn') {

        global $nps;

        $key = $nps['config']['api']['key'][$brand];
        $delightedConnection = \Delighted\Client::setApiKey($key);
        return $delightedConnection;

    }

    function prepareSurveysData($ordersData) {

        $surveysData = [];

        foreach ($ordersData as $o) {

            $surveyProperties = null;
            $surveyProperties = [
                'properties[survey_version]' => 'NPS v1'
                ,'properties[survey_requested]' => date('Y-m-d')
                ,'properties[order_id]' => $o['order_id']
                ,'properties[order_number]' => $o['order_number']
                ,'properties[order_created]' => $o['order_date']
                ,'properties[order_delivered]' => $o['date_delivered']
                ,'properties[product_types]' => ''
                ,'properties[customer_id]' => $o['customer_id']
                ,'properties[customer_email]' => $o['customer_email']
                ,'properties[customer_phone]' => '' // placeholder for sending sms surveys
                ,'properties[customer_firstname]' => ucwords($o['customer_firstname'])
                ,'properties[customer_lastname]' => ucwords($o['customer_lastname'])
                ,'properties[customer_new]' => $o['new_customer']  //unsure if values are reliable
                ,'properties[customer_created]' => $o['customer_account_created'] //when account was created
                ,'properties[cca_id]' => $o['sales_person_id']
                ,'properties[cca_firstname]' => $o['sales_person_firstname']
                ,'properties[store_id]' => $o['store_id']
                ,'properties[store_name]' => $o['store_name']
                ,'properties[store_channel]' => $o['store_channel']
                ,'properties[thank_you_link_text_if_promoter]' => $o['thank_you_link_text_if_promoter']
                ,'properties[thank_you_link_url_if_promoter]' => $o['thank_you_link_url_if_promoter']
            ];
            $surveysData[] = $surveyProperties;

        }

        return $surveysData;

    }


    function scheduleEmailSurvey($data, $brand = 'dn', $delay = 300, $send = true) {

        global $mode;
        $apiConnect = connectSurveysApi($brand);

        $apiRequest = [

                'email' => $data['properties[customer_email]'],
                'name' => $data['properties[customer_firstname]'] . ' ' . $data['properties[customer_lastname]'],
                'channel' => 'email',
                'delay' => $delay,
                'send' => $send

        ];
        // Add special properties
        $surveyRequest = array_merge($apiRequest,$data);

        // If in test mode -- just return requestData
        if (strtolower($mode) == 'test') {
            return $surveyRequest;
        }

        // If live -- send survey data to Delighted
        $results = \Delighted\Person::create($surveyRequest);
        return $results;


    }


    // Delighted is rate limited to 10 api requests per second
    function sendEmailSurveys($surveysData) {

        global $nps;

        $results = [];

        $sendDelay = getDelayToWeekday();

        foreach ($surveysData as $s) {

            $req = null;
            $req = scheduleEmailSurvey($s,'dn', $sendDelay);
            $results[] = $req;

            // Delay for .1 seconds to avoid api rate limit issues
            usleep(100000); //

        }

        return $results;

    }



/* ------------------------------------------

    Check for orders to process

--------------------------------------------- */


    $srcOrdersData = getNpsOrdersData($nps['config']['prefs']['days_to_wait']);
    if (!$srcOrdersData) return false;

    $batchSurveysData = prepareSurveysData($srcOrdersData);


/* ------------------------------------------

    Send applicable data to Delighted

--------------------------------------------- */


    if ($mode == 'test') {

        echo '<hr><h3>Config</h3><pre>';
        print_r($nps);
        echo '</pre><br>';

    }


// Configure delighted with correct brands' Survey key
$delightedConnect = connectSurveysApi($nps['config']['api']['key']['dn']);

//return print_r($batchSurveysData);

$results = sendEmailSurveys($batchSurveysData);

echo '<hr><h3>Results</h3><div><pre>';
return print_r($results);
echo '</pre></div>';











