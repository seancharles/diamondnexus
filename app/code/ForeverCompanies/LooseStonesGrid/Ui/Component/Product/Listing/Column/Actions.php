<?php

namespace ForeverCompanies\LooseStonesGrid\Ui\Component\Product\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Url;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    protected $_urlBuilder;
    protected $_viewUrl;
    
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Url $urlBuilder,
        $viewUrl = '',
        array $components = [],
        array $data = []
        ) {
            $this->_urlBuilder = $urlBuilder;
            $this->_viewUrl    = $viewUrl;
            parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['entity_id'])) {
                    $item[$name]['view']   = [
                        'href'  => $this->_urlBuilder->getUrl($this->_viewUrl, ['id' => $item['entity_id']]),
                        'target' => '_blank',
                        'label' => __('View on Frontend')
                    ];
                }
            }
        }
        return $dataSource;
    }
}
