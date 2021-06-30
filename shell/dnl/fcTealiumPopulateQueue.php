<?php
/**
 * This script will fetch various entities that need to be pushed over to Tealium and insert them into a queue table
 * for future processing
 */

// include Mage app
// require_once $_SERVER['HOME'] . 'magento//Mage.php';
umask(0);

// set current store to admin store id
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

// include PID class and check if process already running; if so, kill script
require_once $_SERVER['HOME'] . '/html/lib/ForeverCompanies/Pid.php';
try {
    $pid = new ForeverCompanies_Pid(
        basename(__FILE__, '.php'),
        $_SERVER['HOME'] . '/html/var/locks/'
    );
    if ($pid->alreadyRunning) {
        die('The script ' . __FILE__ . ' is already running. Halting execution.');
    }
} catch (Exception $e) {
    Mage::logException($e);
    die;
}

/**
 * ========================================================
 * Configuration Variables
 * ========================================================
 */

// max page size
const RESULTS_PER_PAGE = 500;

/**
 * ========================================================
 * Begin script...
 * ========================================================
 */

// set initial orders collection
$orders = Mage::getModel('sales/order')
    ->getCollection()
    ->addFieldToSelect('entity_id')
    ->addAttributeToFilter('store_id', ['in' => [5, 14, 17]])
    ->addAttributeToFilter('created_at', ['gteq' => '2013-09-01 00:00:00']) // orders prior have some issues
    ->addFieldToFilter(
        ['total_refunded', 'total_refunded'],
        [
            ['eq' => 0],
            ['null' => true]
        ]
    )
    ->addAttributeToFilter('state', ['nin' => ['canceled', 'holded']])
    ->addAttributeToSort('created_at', 'asc')
    ->setPageSize(RESULTS_PER_PAGE);


// join to the tealium event table to exclude any sales order that already exists in that table
$orders->getSelect()
    ->joinLeft(
        ['event' => Mage::getSingleton('core/resource')->getTableName('forevercompanies_tealium/event')],
        'main_table.entity_id = event.entity_id AND event.entity_type = "sales_order"',
        ['id']
    )
    ->where('
        (`main_table`.`total_paid` = 0 AND `main_table`.`total_due` = 0)
        OR (
            (`main_table`.`total_due` = 0 OR `main_table`.`total_due` IS NULL)
            AND `main_table`.`total_paid` = `main_table`.`grand_total`
         )
    ')
    ->where('event.id is null');

$orders->load();

// get total number of pages in collection
$pages = $orders->getLastPageNumber();

// loop through each page, starting with page 1
for ($i = 1; $i <= $pages; $i++) {
    //$orders->setCurPage($curPage);

    foreach ($orders as $order) {
        try {
            // load our event model
            $event = Mage::getModel('forevercompanies_tealium/event');

            // Set the transaction data
            $event->setData([
                'event' => 'order',
                'entity_id' => $order->getEntityId(),
                'entity_type' => 'sales_order',
            ]);

            //Mage::log("i= " . $i . ", entityId=" . $order->getEntityId(), null, 'tealium-cron.log');

            // Save the transaction
            $event->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    // make the collection unload the data in memory so it will pick up the next page when load() is called.
    $orders->clear();
}