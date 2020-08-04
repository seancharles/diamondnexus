<?php
namespace Progressive\PayWithProgressive\Plugin;

use Progressive\PayWithProgressive\Helper\ConfigInfo;
use Progressive\PayWithProgressive\Helper\EcomSystem;

class ConfigPlugin
{
	protected $_config;
	protected $_files;
	protected $_system;
	
	public function __construct(
		EcomSystem $eComSystem,
		ConfigInfo $configInfo,
		\Magento\Framework\App\Utility\Files $files
	)
	{
		$this->_config=$configInfo;
		$this->_system=$eComSystem;
		$this->_files = $files;
	}
	
	public function aroundSave(
      \Magento\Config\Model\Config $subject,
      \Closure $proceed)
	{    
		//Temporarily disabled until the receiving service is verified
/*		$payload = array();
		$payload['progressiveStoreId'] = $this->_config->getStoreId();
		$payload['pluginType'] = 1;
		$payload['pluginVersion'] = $this->getVersion();
		$payload['finalizationUrl'] = $this->getFinalizationUrl();

		$this->_system->postMerchantConfiguration($payload);*/
		return $proceed();
	}

	private function getVersion()
    {
        $composerFilePaths = array_keys(
            $this->_files->getComposerFiles(\Magento\Framework\Component\ComponentRegistrar::MODULE)
        );

        foreach ($composerFilePaths as $path) 
        {
            if (strpos($path, 'Progressive/PayWithProgressive/composer.json'))
            {
                $content = file_get_contents($path);
                if ($content) 
                {
                    $jsonContent = json_decode($content, true);

					return isset($jsonContent['version']) ? $jsonContent['version'] : '';
                }
                return '';
            }
        }
        return '';
    }

	private function getFinalizationUrl()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        return $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB) . "/progressive/payment/finalize";
    }
}
