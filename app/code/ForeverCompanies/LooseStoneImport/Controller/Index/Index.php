<?php
namespace ForeverCompanies\LooseStoneImport\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use ForeverCompanies\LooseStoneImport\Model\StoneImport;
use Magento\Framework\App\Config\ScopeConfigInterface;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;


class Index extends Action
{
    protected $stoneModel;
    
    protected $scopeConfig;
    protected $storeScope;
    
    protected $file;
    protected $mediaTmpDir;
    
	public function __construct(
		Context $context,
	    StoneImport $stone,
	    ScopeConfigInterface $scopeC,
	    DirectoryList $directoryList,
	    File $fil
	) {
		$this->stoneModel = $stone;
		
		$this->scopeConfig = $scopeC;
		$this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		
		$this->file = $fil;
		
		$this->mediaTmpDir = $directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp';
		$this->file->checkAndCreateFolder($this->mediaTmpDir );
		
		return parent::__construct($context);
	}
	
	public function execute()
	{
	    echo 'fff';die;
	    
//	    $img = 'http://www.altr.nyc/videos/imaged/302330141/still.jpg';
	    $img = 'https://assets.solitaires.info/image/tpincn-1';
	    $img = 'https://fenix-hyb.s3-us-west-2.amazonaws.com/sys-master/images/h0d/hb3/8823736139806';
	    
	    
	    $imageFileName = $this->mediaTmpDir . DIRECTORY_SEPARATOR . baseName($img);
	    
	    $imagePathInfo = pathinfo($imageFileName);
	    
	    if (!isset($imagePathInfo['extension'])) {
	        if (isset($imagePathInfo['mime']) && $imagePathInfo['mime'] == 'image/jpeg') {
	            $imageFileName .= ".jpg";
	        } else {
	            $imageFileName .= ".jpg";
	        }
	        
	    } else {
	        echo 'the extension is ' . $imagePathInfo['extension'] . '<br />';
	    }
	    
	    
	    $imageResult = $this->file->read($img, $imageFileName);
	    
	    $fileParts = pathinfo($imageFileName);
	    
	    echo 'the image file name is ' . $imageFileName . '<br />';
	    echo 'ok and the image type is ' . exif_imagetype($imageFileName) . '<br />';
	    echo '<pre>';
	    var_dump("image result", $imageResult);
	    var_dump("asdf", getimagesize($imageFileName));
	    var_dump("pathinfo", $fileParts);
	    
	    die;
	    
	    echo 'Comment out at app/code/ForeverCompanies/LooseStoneImport/Controller/Index/Index.php';die;
	    $this->stoneModel->run();
	    return;
	}
}