<?php

namespace ForeverCompanies\ProductUrlPrefix\Controller;

use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class Router implements \Magento\Framework\App\RouterInterface
{
    protected \Magento\Framework\App\ActionFactory $actionFactory;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected UrlFinderInterface $urlFinder;

    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        UrlFinderInterface $urlFinder
    ) {
        $this->actionFactory = $actionFactory;
        $this->storeManager = $storeManager;
        $this->urlFinder = $urlFinder;
    }

    public function match(\Magento\Framework\App\RequestInterface $request): ?\Magento\Framework\App\ActionInterface
    {
        $replaceUrl = str_replace("products/", "", $request->getPathInfo());
        $rewrite = $this->getRewrite($replaceUrl, $this->storeManager->getStore()->getId());
        if ($rewrite == null) {
            return null;
        }
        $request->setAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $rewrite->getRequestPath());
        $request->setPathInfo('/' . $rewrite->getTargetPath());
        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
    }

    protected function getRewrite($requestPath, $storeId): ?UrlRewrite
    {
        return $this->urlFinder->findOneByData(
            [
                UrlRewrite::REQUEST_PATH => trim($requestPath, '/'),
                UrlRewrite::STORE_ID => $storeId,
            ]
        );
    }
}
