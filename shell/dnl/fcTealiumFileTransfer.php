<?php
/**
 * This script will grab all csv files generated and transfer them to the Tealium S3 bucket for processing
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

// include s3 class
require_once $_SERVER['HOME'] . '/html/lib/ForeverCompanies/S3.php';

/**
 * ========================================================
 * Configuration Variables
 * ========================================================
 */

// aws bucket info
const S3_ACCESS_KEY = 'AKIASITVWH2FANWSIFVT';
const S3_SECRET_KEY = '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';
const S3_BUCKET = 'collect-us-east-1.tealium.com';
const S3_PREFIX = 'bulk-downloader/forevercompanies-main/';
const S3_REGION = 'us-east-1';

// file extension for files we want to transfer
const FILE_EXTENSION = 'csv';

// base output directory
$baseDirectory = Mage::getBaseDir('var') . DS . 'export' . DS . 'call_center_orders';

// production queue directory for files to be transferred to tealium
$queueDirectory = $baseDirectory . DS . 'queue';

// archive directory
$archiveDirectory = $baseDirectory . DS . 'archive';

// log directory
$logDirectory = $baseDirectory . DS . 'logs';

// error log
$errorLogFile = $logDirectory . DS . 'errors.log';

/**
 * ========================================================
 * Begin script...
 * ========================================================
 */

try {
    // first verify/create our archive directory
    $io = new Varien_Io_File();
    $io->checkAndCreateFolder($archiveDirectory);
    if (!$io->isWriteable($archiveDirectory)) {
        throw new Exception('Cannot write to archive directory ' . $archiveDirectory);
    }

    // verify our queue directory exists
    $io->checkAndCreateFolder($queueDirectory);
    if (!is_dir($queueDirectory) || !$io->isWriteable($queueDirectory)) {
        throw new Exception('Queue directory ' . $queueDirectory . ' does not exist or is not writable.');
    }

    // verify our log directory exists
    $io->checkAndCreateFolder($logDirectory);
    if (!is_dir($logDirectory) || !$io->isWriteable($logDirectory)) {
        throw new Exception('Log directory ' . $logDirectory . ' does not exist or is not writable.');
    }

    // navigate to the queue
    $io->cd($queueDirectory);

    // get a list of files in queue directory
    $files = $io->ls(Varien_Io_File::GREP_FILES);

    // if there are no files, we can end the script
    if (empty($files)) {
        exit;
    }

    // initialize our s3 bucket class
    $s3 = new ForeverCompanies_S3(S3_ACCESS_KEY, S3_SECRET_KEY, true);
    $s3->setRegion(S3_REGION);
    $s3->setExceptions(true);

    // loop through each file and transfer to s3 bucket if it's a csv file
    foreach ($files as $file) {
        // we only want to upload files that match our defined file extension
        if (FILE_EXTENSION === $file['filetype']) {
            $filePath = $queueDirectory . DS . $file['text'];
            $uploaded = $s3->putObject(
                $s3->inputFile($filePath),
                S3_BUCKET,
                S3_PREFIX . $file['text'],
                ForeverCompanies_S3::ACL_PRIVATE,
                [],
                ['Content-Type' => 'text/csv']
            );
            // if our upload was successful, we can archive this file
            if ($uploaded) {
                $archivePath = $archiveDirectory . DS . $file['text'];
                // log a warning if the file could not be moved
                if (!$io->mv($filePath, $archivePath)) {
                    $errMsg = basename(__FILE__)
                        . ': Could not archive file '
                        . $filePath . ' to ' . $archivePath
                        . '(' . __LINE__ . ')';
                    Mage::log($errMsg, Zend_Log::ERR, $errorLogFile);
                }
            } else {
                $errMsg = basename(__FILE__)
                    . ': Could not be uploaded ' . $filePath . '(' . __LINE__ . ')';
                Mage::log($errMsg, Zend_Log::ERR, $errorLogFile);
            }
        }
    }
} catch (ForeverCompanies_S3Exception $e) {
    Mage::logException($e);
} catch (Exception $e) {
    Mage::logException($e);
}
