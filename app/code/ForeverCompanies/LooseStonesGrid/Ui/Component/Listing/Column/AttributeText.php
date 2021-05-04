<?php
declare(strict_types=1);

namespace ForeverCompanies\LooseStonesGrid\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Eav\Model\Config;

class AttributeText extends Column
{
    protected $eavConfig;
    
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Config $config,
        array $components = [],
        array $data = []
    ) {
        $this->eavConfig = $config;    
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', $this->getName());
        $options = $attribute->getSource()->getAllOptions();
        $res = [];
        
        foreach($options as $opt) {
            $res[$opt['value']] = $opt['label'];
        }
        
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($res[$item[$this->getData('name')]])) {
                    $item[$this->getData('name')] = $res[$item[$this->getData('name')]];
                }  
            }
        }
        
        return $dataSource;
    }
}
