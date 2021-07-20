<?php
namespace ForeverCompanies\Forms\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\Config\ScopeConfigInterface;

// if( !class_exists( 'IterableClass' ) ) {
class IterableClass extends AbstractModel
{
    private $api_key;
    private $api_url;
    private $debug = false;
    
    protected $scopeConfig;
    protected $storeScope;

    /*
    public function __construct( $api_key, $debug = false ) {
        $this->api_key = $api_key;
        $this->debug = $debug;
    }
    */
    
    public function __construct(
        ScopeConfigInterface $scopeC
    ) {
        //  $this->api_key = $api_key;
        //  $this->debug = $debug;
        
        $this->scopeConfig = $scopeC;
        $this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        
        $this->api_key = $this->scopeConfig->getValue('forevercompanies_forms/iterable/api_key', $this->storeScope);
        $this->api_url = $this->scopeConfig->getValue('forevercompanies_forms/iterable/api_url', $this->storeScope);
    }
    
    private function setOptionals(&$array, $values)
    {
        foreach ($values as $key => $value) {
            if ($value !== false) {
                $array[ $key ] = $value;
            }
        }
    }

    private function queryString($query)
    {
        $query_array = array();

        foreach ($query as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    $query_array[] = sprintf('%s=%s', urlencode($key), urlencode($v));
                }
            } else {
                $query_array[] = sprintf('%s=%s', urlencode($key), urlencode($value));
            }
        }

        return implode('&', $query_array);
    }

    // iterable limits request size to 3000kb
    private function chunkRequests($input, $max_size = 2)
    {
        $total_length = strlen(json_encode($input));
        $max_length = $max_size * 1024 * 1024;
        $num_chunks = ceil($total_length / $max_length);

        return array_chunk($input, floor(count($input) / $num_chunks));
    }

    private function sendRequest(
        $resource,
        $params = array(),
        $request = 'GET',
        $decode = true
    ) {
        $curl_handle = curl_init();

        $url = $this->api_url . $resource . '?api_key=' . $this->api_key;

        if ($request == 'GET') {
            $url .= '&' . $this->queryString($params);
        } elseif ($request == 'POST') {
            curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $params);
            curl_setopt($curl_handle, CURLOPT_POST, 1);
            curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params)
            ));
        } else {
            throw new Exception('Invalid request parameter specified.');
        }
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl_handle, CURLOPT_TIMEOUT, 0);

        $buffer = curl_exec($curl_handle);

        // handle curl error
        if (curl_errno($curl_handle)) {
            return array(
                'success' => false,
                'error_message' => curl_error($curl_handle),
            );
        } else {
            $result = array(
                'response_code' => curl_getinfo(
                    $curl_handle,
                    CURLINFO_HTTP_CODE
                ),
            );

            if ($result[ 'response_code' ] === 200) {
                $result[ 'success' ] = true;

                // try to decode as json
                $decoded_output = $decode ? json_decode($buffer, true) : null;
                if ($decoded_output !== null) {
                    $result[ 'content' ] = $decoded_output;
                } else {
                    $result[ 'content' ] = $buffer;
                }
            } else {
                $result[ 'success' ] = false;
                $result[ 'error_message' ] = $buffer;
            }

            return $result;
        }
    }

    /* Lists */
    public function lists()
    {
        $result = $this->sendRequest('lists');
        if ($result[ 'success' ]) {
            $result[ 'content' ] = $result[ 'content' ][ 'lists' ];
        }

        return $result;
    }

    public function listSubscribe(
        $list_id,
        $subscribers,
        $resubscribe = false
    ) {
        // the structure of subscribers has been caused us a huge
        // number of problems so search for and remove potentially
        // dangerous values
        foreach ($subscribers as $index => &$subscriber) {
            if (!isset($subscriber[ 'email' ]) || $subscriber[ 'email' ] == '') {
                unset($subscribers[ $index ]);
            } elseif (isset($subscriber[ 'dataFields' ]) &&
                ( !is_array($subscriber[ 'dataFields' ]) ||
                count($subscriber[ 'dataFields' ]) == 0 )) {
                unset($subscriber[ 'dataFields' ]);
            }
        }

        // potentially we've emptied the array
        if (count($subscribers) == 0) {
            trigger_error('Subscribers array is empty', E_USER_WARNING);
            return array(
                'success' => false,
                'error_message' => 'zero_subscribers',
            );
        }

        // avoid hitting the iterable request size limit
        $result = array();
        foreach ($this->chunkRequests($subscribers) as $chunk) {
            $body = array(
                'listId' => (int) $list_id,
                'subscribers' => $chunk,
                'resubscribe' => $resubscribe
            );

            $result = $this->sendRequest(
                'lists/subscribe',
                json_encode($body),
                'POST'
            );

            if (!$result[ 'success' ]) {
                break;
            }
        }

        return $result;
    }

    public function listUnsubscribe(
        $list_id,
        $subscribers,
        $campaign_id = false,
        $channel_unsubscribe = false
    ) {
        $request = array(
            'listId' => (int) $list_id,
            'subscribers' => $subscribers,
        );

        $this->setOptionals($request, array(
            'campaignId' => $campaign_id,
            'channelUnsubscribe' => $channel_unsubscribe
        ));

        return $this->sendRequest(
            'lists/unsubscribe',
            json_encode($request),
            'POST'
        );
    }

    /* Events */

    public function eventTrack(
        $email,
        $event_name,
        $created_at = false,
        $data_fields = false,
        $user_id = false,
        $campaign_id = false,
        $template_id = false
    ) {
        $request = array(
            'email' => $email,
            'eventName' => $event_name,
        );

        $this->setOptionals($request, array(
            'createdAt' => (int) $created_at,
            'dataFields' => $data_fields,
            'user_id' => $user_id,
            'campaignId' => (int) $campaign_id,
            'templateId' => (int) $template_id,
        ));

        return $this->sendRequest(
            'events/track',
            json_encode($request),
            'POST'
        );
    }

    public function eventTrackConversion()
    {
        throw new Exception('Not yet implemented');
    }

    public function eventTrackPushOpen()
    {
        throw new Exception('Not yet implemented');
    }

    /* Users */

    public function userDelete($email)
    {
        $result = $this->sendRequest('users/delete', json_encode(array(
            'email' => $email
        )), 'POST');
        return $result;
    }

    public function user($email)
    {
        $result = $this->sendRequest('users/get', json_encode(array(
            'email' => $email
        )), 'POST');

        if ($result[ 'success' ]) {
            if (isset($result[ 'content' ][ 'user' ][ 'dataFields' ])) {
                $result[ 'content' ] = $result[ 'content' ][ 'user' ][ 'dataFields' ];
            } else {
                $result[ 'content' ] = array();
            }
        }

        return $result;
    }

    public function userUpdateEmail($current_email, $new_email)
    {
        $result = $this->sendRequest(
            'users/updateEmail',
            json_encode(array(
            'currentEmail' => $current_email,
            'newEmail' => $new_email
            )),
            'POST'
        );
        return $result;
    }

    public function userBulkUpdate($users)
    {
        $result = $this->sendRequest(
            'users/bulkUpdate',
            json_encode(array(
            'users' => $users
            )),
            'POST'
        );
        return $result;
    }

    public function userRegisterDeviceToken()
    {
        throw new Exception('Not yet implemented');
    }

    public function userUpdateSubscriptions(
        $email,
        $email_list_ids = false,
        $unsub_channel_ids = false,
        $unsub_message_ids = false,
        $campaign_id = false,
        $template_id = false
    ) {

        $request = array( 'email' => $email );

        $this->setOptionals($request, array(
            'emailListIds' => $email_list_ids,
            'unsubscribedChannelIds' => $unsub_channel_ids,
            'unsubscribedMessageTypeIds' => $unsub_message_ids,
            'campaignId' => $campaign_id,
            'templateId' => $template_id
        ));
        return $this->sendRequest(
            'users/updateSubscriptions',
            json_encode($request, JSON_NUMERIC_CHECK),
            'POST'
        );
    }

    public function userGetByEmail($email, $data_fields = false, $user_id = false)
    {
        throw new Exception('Not yet implemented');
    }

    public function userFields()
    {
        $result = $this->sendRequest('users/getFields');

        if ($result[ 'success' ]) {
            $result[ 'content' ] = array_keys($result[ 'content' ][ 'fields' ]);
        }

        return $result;
    }

    public function userUpdate(
        $email = false,
        $data_fields = false,
        $user_id = false
    ) {
        // need either an email or user id
        if ($email === false && $user_id === false) {
            throw new Exception('Must specify email or user ID');
        }

        $request = array();
        $this->setOptionals($request, array(
            'email' => $email,
            'dataFields' => $data_fields,
            'userId' => $user_id
        ));

        $result = $this->sendRequest(
            'users/update',
            json_encode($request),
            'POST'
        );
        return $result;
    }

    public function userGetSentMessages(
        $email,
        $limit,
        $campaign_id = false,
        $exclude_blast_campaigns = false,
        $start_date_time = false,
        $end_date_time = false
    ) {
        throw new Exception('Not yet implemented');
    }

    public function userDisableDevice()
    {
        throw new Exception('Not yet implemented');
    }

    /* Push */

    public function push()
    {
        throw new Exception('Not yet implemented');
    }

    /* SMS */

    public function sms($campaign_id, $recipient_email, $data_fields = false, $send_at = false)
    {
        throw new Exception('Not yet implemented');
    }

    /* Campaigns */

    public function campaignsCreate(
        $name,
        $list_ids,
        $template_id,
        $suppression_list_ids = false,
        $send_at = false,
        $send_mode = false,
        $data_fields = false
    ) {

        // Iterable have deprecated listId, convert to listIds
        if (!is_array($list_ids)) {
            $list_ids = array( $list_ids );
        }

        $request = array(
            'name' => $name,
            'listIds' => $list_ids,
            'templateId' => $template_id
        );

        $this->setOptionals($request, array(
            'suppressionListIds' => $suppression_list_ids,
            'sendAt' => $send_at,
            'sendMode' => $send_mode,
            'dataFields' => $data_fields
        ));

        return $this->sendRequest('campaigns/create', json_encode($request, JSON_NUMERIC_CHECK), 'POST');
    }

    public function campaigns()
    {
        return $this->sendRequest('campaigns');
    }

    /* Commerce */

    public function commerceTrackPurchase(
        $user,
        $items,
        $total = false,
        $campaign_id = false,
        $template_id = false,
        $data_fields = false
    ) {

        // create user object from email if necessary
        if (is_string($user)) {
            $user = array( 'email' => $user );
        }

        // calculate total purchase amount if necessary
        if (!$total) {
            $total = 0;
            foreach ($items as $i) {
                if (isset($i[ 'price' ])) {
                    $total += (int) $i[ 'price' ];
                }
            }
        }

        $request = array(
            'user' => $user,
            'items' => $items,
            'total' => $total
        );

        $this->setOptionals($request, array(
            'campaignId' => $campaign_id,
            'templateId' => $template_id,
            'dataFields' => $data_fields
        ));

        $result = $this->sendRequest(
            'commerce/trackPurchase',
            json_encode($request),
            'POST'
        );

        return $result;
    }

    public function commerceUpdateCart($user, $items)
    {
        $request = array(
            'user' => $user,
            'items' => $items
        );
        $result = $this->sendRequest(
            'commerce/updateCart',
            json_encode($request),
            'POST'
        );

        return $result;
    }

    /* Email */

    public function email(
        $campaign_id,
        $recipient,
        $data_fields = false,
        $send_at = false,
        $attachments = false
    ) {

        $request = array(
            'campaignId' => $campaign_id,
            'recipientEmail' => $recipient
        );

        $this->setOptionals($request, array(
            'dataFields' => $data_fields,
            'sendAt' => $send_at,
            'attachments' => $attachments
        ));

        return $this->sendRequest('email/target', json_encode($request, JSON_NUMERIC_CHECK), 'POST');
    }

    public function viewInBrowser($email, $message_id)
    {
        throw new Exception('Not yet implemented');
    }

    /* Export */

    private function export(
        $type,
        $data_type_name,
        $range,
        $start_date_time,
        $end_date_time,
        $omit_fields,
        $only_fields
    ) {

        $request = array(
            'dataTypeName' => $data_type_name,
            'range' => $range
        );

        $this->setOptionals($request, array(
            'startDateTime' => $start_date_time,
            'endDateTime' => $end_date_time,
            'omitFields' => $omit_fields,
            'onlyFields' => $only_fields
        ));

        return $this->sendRequest('export/data.' . $type, $request, 'GET', false);
    }

    public function exportJson(
        $data_type_name = 'user',
        $range = 'Today',
        $start_date_time = false,
        $end_date_time = false,
        $omit_fields = false,
        $only_fields = false
    ) {

        $result = $this->export(
            'json',
            $data_type_name,
            $range,
            $start_date_time,
            $end_date_time,
            $omit_fields,
            $only_fields
        );

        // transform into valid json
        if ($result[ 'success' ] && $result[ 'content' ] !== '') {
            $result[ 'content' ] = '[' . trim(str_replace("\n", ',', $result[ 'content' ]), ',') . ']';
        }

        return $result;
    }

    public function exportCsv(
        $data_type_name = 'user',
        $range = 'Today',
        $start_date_time = false,
        $end_date_time = false,
        $omit_fields = false,
        $only_fields = false
    ) {
        return $this->export(
            'csv',
            $data_type_name,
            $range,
            $start_date_time,
            $end_date_time,
            $omit_fields,
            $only_fields
        );
    }

    /* Workflows */

    public function triggerWorkflow($workflow_id, $data_fields = false, $list_id = false, $email = false)
    {
        $request = array(
            'workflowId' => $workflow_id,
        );

        $this->setOptionals($request, array(
            'email' => $email,
            'listId' => $list_id,
            'dataFields' => $data_fields
        ));

        $result = $this->sendRequest(
            'workflows/triggerWorkflow',
            json_encode($request),
            'POST'
        );
        return $result;
    }
}
    
// }
