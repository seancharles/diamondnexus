<?php
namespace ForeverCompanies\ProductUrlPrefix\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\UrlInterface;

class CatalogControllerCategoryInitAfter implements ObserverInterface
{
    protected $responseFactory;
    protected $url;

    public function __construct(
        ResponseFactory $responseF,
        UrlInterface$url
    ) {
        $this->responseFactory = $responseF;
        $this->url = $url;
    }
    
    public function execute(Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();
        $controllerAction = $observer->getEvent()->getControllerAction();
        $redirectUrl= $this->url->getUrl('collections/' . $category->getUrlKey());
        $this->responseFactory->create()->setRedirect($redirectUrl)->sendResponse();
        return $observer;
    }
}
