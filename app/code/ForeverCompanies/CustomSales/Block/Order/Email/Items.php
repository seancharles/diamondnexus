<?php

namespace ForeverCompanies\CustomSales\Block\Order\Email;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

use Magento\Framework\App\State;
use ForeverCompanies\LinkProduct\Model\Accessory;

class Items extends \Magento\Sales\Block\Order\Email\Items
{
    /**
     * @param Context $context
     * @param array $data
     * @param OrderRepositoryInterface|null $orderRepository
     */
    public function __construct(
        Context $context,
        ?OrderRepositoryInterface $orderRepository = null,
        State $state,
        Accessory $accessory,
        array $data = []
    ) {
        parent::__construct($context, $data, $orderRepository);
        
        $this->state = $state;
        $this->accessory = $accessory;
    }
    
    protected function getCrossSellProducts($productId) {
        //return $this->accessory->getAccessoryProducts($product);
    }
}