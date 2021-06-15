<?php
namespace ForeverCompanies\TealiumCron\Controller\Index;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\Context;
use ForeverCompanies\TealiumCron\Controller\Index\S3;
use Magento\Framework\Filesystem\Io\File;

require_once '/var/www/magento/app/code/ForeverCompanies/TealiumCron/Controller/Index/ForeverCompanies_Pid.php';
require_once '/var/www/magento/app/code/ForeverCompanies/TealiumCron/Controller/Index/S3.php';


const S3_ACCESS_KEY = 'AKIASITVWH2FANWSIFVT';
const S3_SECRET_KEY = '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';
const S3_BUCKET = 'collect-us-east-1.tealium.com';
const S3_PREFIX = 'bulk-downloader/forevercompanies-main/';
const S3_REGION = 'us-east-1';

// file extension for files we want to transfer
const FILE_EXTENSION = 'csv';

class Index extends \Magento\Framework\App\Action\Action
{
    protected $ioFile;
    
	protected $logger;
	
	public function __construct(
		Context $context,
	    LoggerInterface $logger,
	    File $ioF
	) {
		$this->logger = $logger;
		$this->ioFile = $ioF;
		
		return parent::__construct($context);
	}
	
	public function execute()
	{   
	    // base output directory
	    $baseDirectory = '/var/www/magento/' . DS . 'export' . DS . 'call_center_orders';
	    
	    // production queue directory for files to be transferred to tealium
	    $queueDirectory = $baseDirectory . DS . 'queue';
	    
	    // archive directory
	    $archiveDirectory = $baseDirectory . DS . 'archive';
	    
	    // log directory
	    $logDirectory = $baseDirectory . DS . 'logs';
	    
	    // error log
	    $errorLogFile = $logDirectory . DS . 'errors.log';
	    
	    // include PID class and check if process already running; if so, kill script
	    // require_once '/var/www/magento/lib/ForeverCompanies/Pid.php';
	    try {
	        $pid = new ForeverCompanies_Pid(
	            basename(__FILE__, '.php'),
	            '/var/www/magento/var/locks/'
	            );
	        if ($pid->alreadyRunning) {
	            die('The script ' . __FILE__ . ' is already running. Halting execution.');
	        }
	    } catch (Exception $e) {
	        $this->logger->info($e->getMessage());
	    }
	    
	    // base output directory
	    $baseDirectory = '/var/www/magento' . DS . 'export' . DS . 'call_center_orders';
	    
	    // production queue directory for files to be transferred to tealium
	    $queueDirectory = $baseDirectory . DS . 'queue';
	    
	    // archive directory
	    $archiveDirectory = $baseDirectory . DS . 'archive';
	    
	    // log directory
	    $logDirectory = $baseDirectory . DS . 'logs';
	    
	    // error log
	    $errorLogFile = $logDirectory . DS . 'errors.log';
	    
	    
	    try {
	        // first verify/create our archive directory
	        
	        $this->ioFile->checkAndCreateFolder($archiveDirectory);
	        if (!$this->ioFile->isWriteable($archiveDirectory)) {
	            throw new Exception('Cannot write to archive directory ' . $archiveDirectory);
	        }
	        
	        // verify our queue directory exists
	        $this->ioFile->checkAndCreateFolder($queueDirectory);
	        if (!is_dir($queueDirectory) || !$this->ioFile->isWriteable($queueDirectory)) {
	            throw new Exception('Queue directory ' . $queueDirectory . ' does not exist or is not writable.');
	        }
	        
	        // verify our log directory exists
	        $this->ioFile->checkAndCreateFolder($logDirectory);
	        if (!is_dir($logDirectory) || !$this->ioFile->isWriteable($logDirectory)) {
	            throw new Exception('Log directory ' . $logDirectory . ' does not exist or is not writable.');
	        }
	        
	        // navigate to the queue
	        $this->ioFile->cd($queueDirectory);
	        
	        // get a list of files in queue directory
	        $files = $this->ioFile->ls($this->ioFile::GREP_FILES);
	        
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
	                    if (!$this->ioFile->mv($filePath, $archivePath)) {
	                        $errMsg = basename(__FILE__)
	                        . ': Could not archive file '
	                            . $filePath . ' to ' . $archivePath
	                            . '(' . __LINE__ . ')';
	                            $this->logger->info($message, \Zend_Log::ERR, $errorLogFile);
	                    }
	                } else {
	                    $errMsg = basename(__FILE__)
	                    . ': Could not be uploaded ' . $filePath . '(' . __LINE__ . ')';
	                    $this->logger->info($message, \Zend_Log::ERR, $errorLogFile);
	                }
	            }
	        }
	    } catch (ForeverCompanies_S3Exception $e) {
	        $this->logger->info($e->getMessage());
	    } catch (Exception $e) {
	        $this->logger->info($e->getMessage()); 
	    }
	}
}