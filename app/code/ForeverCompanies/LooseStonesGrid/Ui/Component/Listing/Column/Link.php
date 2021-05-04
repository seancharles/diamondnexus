<?php
declare(strict_types=1);

namespace ForeverCompanies\LooseStonesGrid\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Link extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        $name = $this->getData('name');
        
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if ($item[$name] != "") {
                    if ($name == "cert_url_key") {
                        $item[$name] = '<a href="' . $item['cert_url_key'] . '" target="_blank">Cert</a>';
                    } elseif($name == "diamond_img_url") {
                        $item[$name] = '<a href="' . $item['diamond_img_url'] . '" target="_blank">Image</a>';
                    } elseif($name == "video_url") {
                        $item[$name] = '<a href="' . $item['video_url'] . '" target="_blank">Video</a>';
                    }
                }
            }
        }
        
        return $dataSource;
    }
}
