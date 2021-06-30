<?php
namespace ForeverCompanies\SystemCron\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class FailedDeliveryReport
{
    protected $orderCollectionFactory;
    protected $orderFactory;
    protected $date;
    protected $directory;
    
    public function __construct(
        OrderCollectionFactory $orderCollectionF,
        OrderFactory $orderF
    ) {
        $this->orderCollectionFactory = $orderCollectionF;
        $this->orderFactory = $orderF;
        $this->date = date('Y-m-d', strtotime('now'));
        $this->directory = $fileS->getDirectoryWrite(DirectoryList::VAR_DIR);
    }
    
    public function execute()
    {
        $date_range = 45;
        
        while ($date_range > 0) {
            
            $fromDate = date('Y-m-d H:i:s', strtotime($this->date . ' - ' . $date_range .' day'));
            $toDate = date('Y-m-d H:i:s', strtotime($this->date . ' - ' . ($date_range - 5) .' day'));
            
            $order_collection = $this->orderCollectionFactory->create()
            ->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
            ->addFieldToFilter('status', array('in' => array('Shipped', 'delivered')))
            ->load();
            
            $filename = $_SERVER['HOME'].'/var/www/magento/var/report/failed_delivery_' . $this->date  . '_' . $date_range . '.csv';
            
            $track_count = array();
            
            $stream = $this->directory->openFile($filename, 'w+');
            $stream->lock();
            
            foreach ($order_collection as $or) {
                foreach($or->getTracksCollection() as $track) {
                    
                    $order = $this->orderFactory->create()->load($or->getId());
                    
                    $tracksCollection = $order->getTracksCollection();
                    foreach($tracksCollection as $track) {
                        
                        // TODO
                        // Figure out when/where the info is being updated with delivery results and adjust accordingly, if needed.
                        /*
                         echo '<pre>';
                         var_dump("track keys", array_keys($track->getData()));
                         var_dump( "data", $track->getData() );
                         echo 'carrier code ' . $track->getCarrierCode() . '<Br />';
                         echo 'track number ' . $track->getTrackNumber() . '<br />';
                         echo 'the track creatd at is ' . $track->getCreatedAt() . '<br />';
                         var_dump( "track description", $track->getDescription() );
                         var_dump("track number detail", get_class_methods($track));
                         die;
                         */
                        
                        if ($track->getCarrierCode() == "fedex" && strlen($track->getTrackNumber()) > 12) {
                            continue;
                        }
                        if ($track->getCarrierCode() == "usps" && strlen($track->getTrackNumber()) < 22) {
                            continue;
                        }
                        $trackingInfo = $track->getNumberDetail();
                        if(preg_match('/attempted to deliver/i', $trackingInfo->getTrackSummary(), $track->getCreatedAt())) {
                            echo $order->getIncrementId() . "," . $trackingInfo->getTracking() . ",Failed Delivery/Notice Left";
                        } elseif(preg_match('/redelivery/i', $trackingInfo->getTrackSummary(), $track->getCreatedAt())) {
                            echo $order->getIncrementId() . "," . $trackingInfo->getTracking() . ",Return to Sender Imminent";
                        }
                    }
                    
                    if (strlen($report_line) > 2) {
                        $stream->writeCsv(explode(",", $report_line));
                    }
                }
            }
            
            $date_range -= 5;
        }
        
    }
}