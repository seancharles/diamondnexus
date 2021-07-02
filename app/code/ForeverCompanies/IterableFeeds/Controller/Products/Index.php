<?php

declare(strict_types=1);

namespace ForeverCompanies\IterableFeeds\Controller\Products;

use Magento\Framework\Api\Filter;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_searchCriteriaBuilder;
    protected $_productRepositoryInterface;
    protected $_jsonHelper;
    protected $_jsonResultFactory;
    
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper
	) {
		parent::__construct($context);
        
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_productRepositoryInterface = $productRepositoryInterface;
        $this->_jsonHelper = $jsonHelper;
        $this->_jsonResultFactory = $jsonResultFactory;
	}

	public function execute()
	{
        $productArray = [];
        
        $request = $this->getRequest()->getParams();

        $result = $this->_jsonResultFactory->create();
        
        // default reslt
        $result = $result->setData([
            'success' => false,
            'message' => 'Invalid product ids'
        ]);
        
        if (isset($request['pids']) == true) {
            $pids = $request['pids'];
            
            $pidsAry = $this->_jsonHelper->jsonDecode($pids);
            
            // filter products by id
            $searchCriteria = $this->_searchCriteriaBuilder
                ->addFilter('entity_id', $pidsAry, 'in')
                ->create();
            
            // return products list interface
            $productsCollection = $this->_productRepositoryInterface->getList($searchCriteria)->getItems();
            
            foreach($productsCollection as $product) {
                $productArray[] = [
                    'id' => $product->getId(),
                    'type_id' => $product->getTypeId(),
                    'name' => $product->getName(),
                    'shape' => $product->getResource()->getAttribute('cut_type')->getFrontend()->getValue($product),
                    'price' => $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getBaseAmount(),
                    'special_price' => $product->getFinalPrice(),
                    'attribute_set_id' => $product->getAttributeSetId(),
                    'img' =>  $product->getMediaConfig()->getMediaUrl($product->getImage())
                ];
            }
            
            $result = $result->setData([
                'products' => $productArray
            ]);
        }

        return $result; 
	}
}
    