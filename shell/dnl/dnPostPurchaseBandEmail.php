<?php

    require_once $_SERVER['HOME'] . '/html/app/Mage.php';
    
    Mage::app();
    
    class PostPurchase
    {
        const MatchingBandTemplateId = 73;
        
        public $debug;
        
        public function __construct($debug = false)
        {
            $this->debug = $debug;
            
            $this->readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
            $this->emailTransactionModel = Mage::getSingleton('postpurchase/email');
        }
        
        public function filterImages($imageList, $metalType = null)
        {
            $tmp = array();
            
            foreach($imageList as $image)
            {
                if( strpos( $image['label'], $metalType ) !== false )
                {
                    $tmp[] = $image;
                }
            }
            
            return $tmp;
        }
        
        public function formatHeadlineHTML($title = null)
        {
            $html = '<tr>
                        <td>
                            <table class="responsive-with-padding" width="600" border="0" cellspacing="0" cellpadding="0" align="center" style="width: 600px;">
                                <tr>
                                    <td valign="top" style="color: #b59d74; font-size:14px; font-family: \'Montserrat\', Arial, \'Open Sans\', sans-serif; line-height: 24px; text-align: center;">
                                        <span style="line-height:44px; padding-top: 10px; padding-bottom: 10px; border-top:1.1px solid #b59d74; border-bottom:1.1px solid #b59d74; border-left: 0; border-right:0; padding-left: 20px; padding-right: 20px;">
                                            ' . $title . '
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" height="30">&nbsp;</td>
                    </tr>';
                    
            return $html;
        }
        
        public function formatBandHTML($configurableId = 0, $bandId = 0, $metalType = null)
        {
            $_product = Mage::getModel('catalog/product')->load($configurableId);
            
            // get ring and band composite images
            $imageList = Mage::helper('diamondnexus_product')->getCrossSellImagesByParent($bandId, $configurableId);
            
            // filter to the correct metal type
            $filteredImages = $this->filterImages($imageList, $metalType);
            
            // if a default image in the selected metal type was not found default to the first image
            if( strlen($filteredImages[0]['large']) > 0 )
            {
                $imageUrl = 'https://content.diamondnexus.com/' . $filteredImages[0]['large'];
                
            } else {
                
                $galleryImageList = $_product->getMediaGalleryImages()->toArray();
                
                $filteredImages = $this->filterImages($galleryImageList, $metalType);
                
                $imageUrl = $filteredImages[0]['url'];
            }
            
            $utmParams = 'utm_source=magento&utm_medium=email&utm_campaign=Wedding%20Band%20Up%20Sell%20email';
            
            if( strlen($metalType) > 0 )
            {
                $productUrl = $_product->getProductUrl() . '?precious-metal=' . str_replace(" ","-", $metalType) . '&' . $utmParams;
                
            } else {
                
                $productUrl = $_product->getProductUrl() . '?' . $utmParams;
            }
            
            $productName = $_product->getName();
            $html = '';
            
            if( strlen($imageUrl) > 0 && strlen($productUrl) > 0 && strlen($productName) > 0 )
            {
                $html = '<tr>
                            <td>
                               <table align="center" width="100%" border="0" cellspacing="0" cellpadding="0" style="width:100%;">
                                    <tr>
                                      <td width="85" class="side"  style="width:85px">&nbsp;</td>
                                      <td class="middle" width="430" align="center" style="text-align: center; width: 430px;">
                                            <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="width:100%; text-align: center;">
                                                <tr>
                                                    <td style="line-height: 24px; text-align: center; ">
                                                        <a href="' . $productUrl . '">
                                                            <img src="' . $imageUrl . '" alt="' . $productName . '" width="300" height="300"/>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td valign="top" height="5">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td style="color: #000001; font-size:14px; font-family: \'Montserrat\', Arial, \'Open Sans\', sans-serif; line-height: 24px; text-align: center; ">
                                                        <a href="' . $productUrl . '" style="text-decoration: underline; color: #000001 !important;" class="blacklink"><font color="#000001" style="display:inline;">' . $productName . '</a>
                                                    </td>
                                                </tr>
                                            </table>
                                      </td>
                                      <td width="85" class="side" style="width:85px;">&nbsp;</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                               <table class="responsive-table" align="center" width="600" border="0" cellspacing="0" cellpadding="0" style="width: 600px; text-align: center;">
                                    <tr>
                                      <td width="155" class="side"  style="width:155px;">&nbsp;</td>
                                      <td width="290" class="middle" align="center" style="width: 290px; text-align: center;">
                                            <table class="responsive-80" width="100%"  border="0" cellpadding="0" cellspacing="0" align="center" style="width: 100%; text-align: center; color: #ffffff;">
                                                <tr>
                                                    <td valign="top" height="5">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td height="50" align="center" bgcolor="#000000" style="font-size:17px; font-family: \'Montserrat\', Arial, \'Open Sans\', sans-serif; line-height: 24px; text-decoration: none; text-align: center; color: #ffffff; font-weight: bold; letter-spacing: 2px">
                                                        <a href="' . $productUrl . '" style="color: #ffffff !important; text-decoration: none; background-color:#000000; padding: 15px; display: block"  class="whitelink">
                                                            <span class="whitelink" style="color:#ffffff">SHOP NOW</span>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td valign="top" height="60">&nbsp;</td>
                                                </tr>
                                            </table>
                                      </td>
                                      <td width="155" class="side"  style="width:155px;">&nbsp;</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td valign="top" height="30">&nbsp;</td>
                        </tr>';
            } else {
                echo "Error product configuration issue detected\n";
            }
            
            return $html;
        }
        
        public function sendEmail($orderList = null, $templateId = 0)
        {
            echo "sendEmail\n";
            
            foreach($orderList as $order)
            {
                // load the order object to pass to the email
                $orderModel = Mage::getModel('sales/order')->load($order['order_id']);
            
                $bandList = $this->getCustomerOrderList( $orderModel->getCustomerId(), $orderModel->getCustomerEmail(), $orderModel->getCreatedAt() );
            
                // we only send a band email if they haven't purchased one
                if( count($bandList) == 0 )
                {
                    $emailQuery = "SELECT * FROM diamondnexus_postpurchase_email WHERE order_id = '" . $orderModel->getId() ."' AND template_id = '" . $templateId . "';";
                    
                    if( $this->debug )
                        echo $emailQuery . "\n";
                    
                    $emailLog = $this->readConnection->fetchAll($emailQuery);

                    // check if we found any logs for this email
                    if( $orderModel->getId() == $order['order_id'] && count($emailLog) == 0 || ($emailLog[0]['sent'] == 0 && $emailLog[0]['failed'] <= 5) )
                    {
                        // we reset error to false for every item
                        $error = false;
                        
                        echo "Sending email to " . $orderModel->getCustomerEmail() . "...\n";

                        // Set sender information
                        $senderName = Mage::getStoreConfig('trans_email/ident_support/name');
                        $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');

                        $sender = array(
                            'name' => $senderName,
                            'email' => $senderEmail
                        );

                        $buyRequest = unserialize($order['product_options']);
                        
                        // get the original product metal type from the order
                        $metalTypeId = $buyRequest['info_buyRequest']['super_attribute'][145];
                        
                        $configurableId = $buyRequest['info_buyRequest']['cpid'];
                        $engagementRingId = $buyRequest['info_buyRequest']['product'];
                        
                        // load the product model to get metal type
                        $productModel = Mage::getModel('catalog/product')->load($engagementRingId);
                        
                        $metalType = $productModel->getAttributeText('metal_type');
                        
                        // product does not appear to h
                        if( $configurableId > 0  )
                        {
                            $crossProducts = Mage::helper('diamondnexus_product')->getCrossSellProducts($configurableId);
                            
                            // do not send emails with no matching bands
                            if( count($crossProducts) > 0 )
                            {
                                switch(count($crossProducts))
                                {
                                    case 1:
                                        $bandHTML = $this->formatHeadlineHTML("IT'S THE PERFECT MATCH");
                                        $bandHTML .= $this->formatBandHTML($crossProducts[0]['entity_id'], $configurableId, $metalType);
                                        break;
                                    
                                    case 2:
                                        $bandHTML = $this->formatHeadlineHTML("CHOOSE YOUR MATCH");
                                        $bandHTML .= $this->formatBandHTML($crossProducts[0]['entity_id'], $configurableId, $metalType);
                                        $bandHTML .= $this->formatBandHTML($crossProducts[1]['entity_id'], $configurableId, $metalType);
                                        break;
                                    
                                    case 3:
                                        $bandHTML = $this->formatHeadlineHTML("THE PERFECT MATCH");
                                        $bandHTML .= $this->formatBandHTML($crossProducts[0]['entity_id'], $configurableId, $metalType);
                                        $bandHTML .= $this->formatHeadlineHTML("MORE GREAT MATCHES");
                                        $bandHTML .= $this->formatBandHTML($crossProducts[1]['entity_id'], $configurableId, $metalType);
                                        $bandHTML .= $this->formatBandHTML($crossProducts[2]['entity_id'], $configurableId, $metalType);
                                }
                                
                                // Set variables that can be used in email template
                                $vars = array(
                                    'bands_html' => $bandHTML,
                                    'order' => $orderModel
                                );

                                $translate  = Mage::getSingleton('core/translate');
                                
                                try {
                                    
                                    // Send Transactional Email
                                    Mage::getModel('core/email_template')->sendTransactional(
                                        PostPurchase::MatchingBandTemplateId,
                                        $sender,
                                        $orderModel->getCustomerEmail(),
                                        //'paul.baum@forevercompanies.com',
                                        $orderModel->getCustomerFirstname() . " " . $orderModel->getCustomerLastname(),
                                        $vars,
                                        $orderModel->getStoreId()
                                    );
                                    
                                    $translate->setTranslateInline(true);
                                }
                                catch (Exception $e) {

                                    print($e->getMessage());

                                    // this will log and increment the failed attempts number
                                    $error = true;

                                } finally {
                                
                                    $fields = array(
                                        'order_id' => $orderModel->getId(),
                                        'template_id' => $templateId,
                                        'created_at' => date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()))
                                    );
                                    
                                    if ($order['email_id']) {
                                        $fields['email_id'] = $order['email_id'];
                                    }

                                    // add failure or success flag
                                    if ( $error == true ) {
                                        $fields['failed'] = $order['failed'] +1;
                                    } else {
                                        $fields['sent'] = 1;
                                    }
                                    
                                    $this->emailTransactionModel->setData($fields);
                                    $this->emailTransactionModel->save();
                                    
                                }
                                
                            } else {
                                echo "No cross-sell products found for ring.\n";
                            }
                        } else {
                            echo "No engagement ring found.\n";
                        }
                    }
                    
                }
            }
        }
        
        /*
         * Pull completed orders with date diff = 10 from the first time they went completed
         */
        public function getEngagementRingList()
        {
            $ringOrdersQuery = "SELECT
                                o.entity_id order_id,
                                o.customer_id,
                                o.customer_email,
                                e.entity_id product_id,
                                i.product_options,
                                datediff(now(), min(h.created_at)) diff,
                                o.created_at,
                                h.created_at
                            FROM
                                sales_flat_order o
                            INNER JOIN
                                sales_flat_order_item i ON o.entity_id = i.order_id
                            INNER JOIN
                                catalog_product_entity e ON i.product_id = e.entity_id 
                            INNER JOIN
                                sales_flat_order_status_history h ON o.entity_id = h.parent_id
                                
                            WHERE
                                e.attribute_set_id = 18
                            AND
                                e.sku LIKE 'LREN%'
                            AND
                                e.sku NOT LIKE 'LRWB%'
                            AND
                                o.status IN('complete')
                            AND
                                o.created_at > CURRENT_DATE - INTERVAL 180 DAY
                            AND
                                datediff(now(), h.created_at) = 10
                            AND
                                h.status IN('complete')
                            GROUP BY
                                o.entity_id;";
            
            if( $this->debug )            
                echo $ringOrdersQuery;
            
            $ringOrderList = $this->readConnection->fetchAll($ringOrdersQuery);
            
            foreach($ringOrderList as $ringOrder)
            {
                $orderList[] = $ringOrder;
            }
			
            return $orderList;
        }
        
        /*
         * Pull completed orders with date diff = 10 from the first time they went completed
         */
        public function getCustomerOrderList($customerId = null, $customerEmail = null, $date = null)
        {
            if( $customerId > 0 && strlen($customer_email) > 0 )
            {
                $customerQuery = "customer_id = " . $customerId . " OR " . "customer_email = " . $customerEmail; 
            }
            elseif( $customerId > 0 )
            {
                $customerQuery = "customer_id = " . $customerId;
            }
            elseif( strlen($customerEmail) > 0 )
            {
                $customerQuery = "customer_email = '" . $customerEmail . "'";
            }
            
            $bandOrdersQuery = "SELECT
                                    o.entity_id
                                FROM
                                    sales_flat_order o
                                INNER JOIN
                                    sales_flat_order_item i ON o.entity_id = i.order_id
                                INNER JOIN
                                    catalog_product_entity e ON i.product_id = e.entity_id 
                                WHERE
                                    (e.attribute_set_id = 27 OR e.sku LIKE 'LRWB%')
                                AND
                                    (" . $customerQuery . ")
                                AND
                                    o.created_at >= '" . $date . "';";
                
            if( $this->debug )            
                echo $bandOrdersQuery;
            
            $bandOrderList = $this->readConnection->fetchAll($bandOrdersQuery);
            
            foreach($bandOrderList as $bandOrder)
            {
                $orderList[] = $bandOrder;
            }
            
            return $orderList;
        }
    }
    
    $enableDebug = true;
    
    $postPurchase = new PostPurchase($enableDebug);
    
    echo "getting matching band list\n";
    
    $engagementRingList = $postPurchase->getEngagementRingList();
    
    if( count($engagementRingList) > 0 )
    {
        echo "sending matching band list\n";
        
        $postPurchase->sendEmail($engagementRingList, PostPurchase::MatchingBandTemplateId);
        
    } else {
        
        echo "no matching band emails found\n";
    }

