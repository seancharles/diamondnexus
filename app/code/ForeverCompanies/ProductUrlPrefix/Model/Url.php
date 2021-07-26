<?php

/**
 * @TODO this could be refactored as a plugin vs a preference but
 * due to time constraints we need to get this live ASAP.
 */

namespace ForeverCompanies\ProductUrlPrefix\Model;

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
        $this->urlFactory = $urlFactory;
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
        $requestPath = $product->getRequestPath();
        if (!empty($requestPath)) {
            $params['_direct'] = $requestPath;
        }
        
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $productUrl = $this->getUrlInstance()->setScope(
            $product->getStoreId()
        )->getUrl(
            $product->getUrlKey(),
            $params
        );
        
        $remainingUrl = str_replace($baseUrl, '', $productUrl);
        
        return str_replace("magento/", "", $baseUrl . "products/" . $remainingUrl);
    }

    private function getUrlInstance(): \Magento\Framework\UrlInterface
    {
        return $this->urlFactory->create();
    }
}
