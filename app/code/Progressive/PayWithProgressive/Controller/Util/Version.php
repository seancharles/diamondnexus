<?php
namespace Progressive\PayWithProgressive\Controller\Util;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;

class Version extends Action
{
	protected $_files;
	
	 public function __construct(
	 Context $context,
        \Magento\Framework\App\Utility\Files $files
    ) {
        $this->files = $files;
		parent::__construct($context);
    }
	
	public function execute()
	{
/*        $composerFilePaths = array_keys(
            $this->files->getComposerFiles(\Magento\Framework\Component\ComponentRegistrar::MODULE)
        );

        foreach ($composerFilePaths as $path) 
		{
            if (strpos($path, 'Progressive/PayWithProgressive/composer.json'))
			{
				$content = file_get_contents($path);
				if ($content) 
				{
					$jsonContent = json_decode($content, true);
					if (!empty($jsonContent['version'])) 
					{
						echo $jsonContent['version'];
					}
				}
				return;
	    }
	}
	echo 'No installed plugin version detected';*/
		echo 'v2.4.0';
	}
}

