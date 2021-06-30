<?php

/* ------------------------------------------

    Init

--------------------------------------------- */

    ini_set('display_errors', '1');
//    require_once $_SERVER['HOME'].'magento//Mage.php';
    Mage::app();

    date_default_timezone_set("America/Chicago");

    $tealData = [
        'brands' => [
            'dn'=> [
                'store'=>1
                ,'name'=>'Diamond Nexus'
                ,'site'=>["diamondnexus","www"]
            ]
            ,'fa'=> [
                'store'=>2
                ,'name'=>'Forever Artisans'
                ,'site'=>["foreverartisans","www"]
            ]
            ,'tf'=>
            [
                'store'=>3
                ,'name'=>'1215 Diamonds'
                ,'site'=>["1215diamonds","www"]
            ]
        ],
        'config' => [
            'orders' => [
                'table' => 'teal_orders_new'
            ],
            'accounts' => [
                'table' => 'teal_accounts'
            ]
        ]
    ];

    /*
    $brand = (!empty($_REQUEST['brand'])) ? $_REQUEST['brand'] : 'dn';

    $tealData = $tealDataSrc[$brand];

    switch ($brand) {

        case 'dn':
            $tealData = $tealDataSrc[$brand];
            break;
        case 'fa':
            $tealData = $tealDataSrc[$brand];
            break;
        case 'tf':
            $tealData = $tealDataSrc[$brand];
            break;
        default:
            echo "Sorry, brand not recognized. Exiting.";
            exit;
    }
    */

    // Default message
    $msg = 'Ready.';



/* ------------------------------------------

    Functions

--------------------------------------------- */

    function dbReadViaSql($q) {
        $mdb = Mage::getSingleton('core/resource')->getConnection('core_read');
        return $mdb->fetchAll($q);
    }
    function dbWriteViaSql($q) {
        $mdb = Mage::getSingleton('core/resource')->getConnection('core_write');
        return $mdb->query($q);
    }
    function fcGetUrlPath ($url = '') {
        if (empty($url)) {
            $url = Mage::helper('core/url')->getCurrentUrl();
        }
        $parsed = Mage::getSingleton('core/url')->parseUrl($url);
        return explode('/', $parsed->getPath());
    }
    function fcTealFormatStr($str,$subSpaces = true) {
        $output = trim(strtolower($str));
        if ($subSpaces) $output = str_replace(' ', '_', $output);
        return $output;
    }
    function fcTealFormatSlug($str) {
        $output = trim(strtolower($str));
        $output = str_replace(' ', '_', $output);
        $output = preg_replace("/[^a-z0-9_]+/i", "", $output);
        return $output;
    }
    function fcTealFormatAry($ary) {
        array_map('fcTealFormatStr',$ary);
        return '["'.implode('","', $ary).'"]';
    }
    function fcTealFormatNumAry($ary,$format = '') {
        return '['.implode(',', $ary).']';
    }
    function fcTealFormatPrice($amt) {
        return number_format((float)$amt, 2, '.', '');
    }
    function fcTealFormatDate($ary) {
        array_map('fcTealFormatStr',$ary);
        return '["'.implode('","', $ary).'"]';
    }
    function fcTealFormatUtagData($utagDataSrc) {
        $utagData = '';
        foreach ($utagDataSrc as $k=>$v) {
            if (is_array($v)) {
                $utagData .= ",\n\"" . $k . '": '. $this->fcTealFormatAry($v);
            } else {
                $utagData .= ",\n\"" . $k . '": "' . $v . '"';
            }
        }
        return $utagData;
    }
    function fcTealTruncate($tblname) {

        global $tealData;

        $exportTables = [
            $tealData['config']['orders']['table']
            ,$tealData['config']['accounts']['table']
        ];
        if (in_array($tblname,$exportTables)) {
            $q = 'TRUNCATE TABLE `' . $tblname . '`';
            dbWriteViaSql($q);
            return true;
        }
        return false;
    }