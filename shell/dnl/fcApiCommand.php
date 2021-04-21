<?php
    ini_set('display_errors', 1);
     
    $username = 'dnlapi';
    $password = '329gvqbu42';
     
    $cli = new SoapClient("https://www.diamondnexus.com/api/soap/?wsdl=1");
     
    //retreive session id from login
    $session_id = $cli->login($username, $password);
    
    if( count($argv) > 2 )
    {
        $action = $argv[1];
        $orderId = $argv[2];

        if( $orderId > 0 )
        {
            switch($action)
            {
                case "info":
                    $result = $cli->call($session_id, 'sales_order.info', $orderId);
                    break;
                    
                case "invoice":
                    $result = $cli->call($session_id, 'sales_order_invoice.create', $orderId);
                    break;
                    
                case "shipment":
                    $result = $cli->call($session_id, 'sales_order_shipment.create', $orderId);
                    break;
                    
                default:
                    echo "Invalid Action: valid values are (info, invoice, shipment)";
                    break;
            }
        } else {
            
            echo "Error: invalid order number\n";
        }

        if( isset($result) == true )
        {
            print_r($result);
        }
    } else {
        
        echo "Error: action and order number are invalid\n";
        echo "Action values are (info, invoice, shipment)\n";
        echo "Example: php fcApiCommand.php info 1100255619\n";
    }

