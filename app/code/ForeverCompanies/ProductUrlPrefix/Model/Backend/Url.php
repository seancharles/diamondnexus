<?php

/**
 * @TODO this could be refactored as a plugin vs a preference but
 * due to time constraints we need to get this live ASAP.
 */

namespace ForeverCompanies\ProductUrlPrefix\Model\Backend;

use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class Url extends \Magento\Catalog\Model\Product\Url
{
    protected $urlFactory;
    protected $storeManager;

    public function __construct(
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filter\FilterManager $filter,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        UrlFinderInterface $urlFinder
    ) {
        parent::__construct($urlFactory, $storeManager, $filter, $sidResolver, $urlFinder);
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array $params
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrl(\Magento\Catalog\Model\Product $product, $params = []): string
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        return $baseUrl . "products/" . $product->getUrlKey();
    }
}
