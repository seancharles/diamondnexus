<?php

namespace ForeverCompanies\Profile\Model\Quote\Item;

use Magento\ConfigurableProduct\Model\Quote\Item\CartItemProcessor as OrigCartItemProcessor;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Framework\DataObject\Factory;
use Magento\Quote\Model\Quote\ProductOptionFactory;
use Magento\Quote\Api\Data\ProductOptionExtensionFactory;
use Magento\ConfigurableProduct\Model\Quote\Item\ConfigurableItemOptionValueFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;

class CartItemProcessor extends OrigCartItemProcessor
{
    protected $objectFactory;
    protected $productOptionFactory;
    protected $extensionFactory;
    protected $itemOptionValueFactory;
    protected $serializer;
    
    protected $logger;
    
    public function __construct(
        Factory $objectFactory,
        ProductOptionFactory $productOptionFactory,
        ProductOptionExtensionFactory $extensionFactory,
        ConfigurableItemOptionValueFactory $itemOptionValueFactory,
        Json $serializer = null
        ) {
            $this->objectFactory = $objectFactory;
            $this->productOptionFactory = $productOptionFactory;
            $this->extensionFactory = $extensionFactory;
            $this->itemOptionValueFactory = $itemOptionValueFactory;
            $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
            
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/checkouterrorlog.log');
            $this->logger = new \Zend\Log\Logger();
            $this->logger->addWriter($writer);
            
            $this->logger->info('000');
            
            parent::__construct(
                $objectFactory,
                $productOptionFactory,
                $extensionFactory,
                $itemOptionValueFactory,
                $serializer
            );
    }
    
    public function processOptions(CartItemInterface $cartItem)
    {
        
        $this->logger->info('111');
        
        $attributesOption = $cartItem->getProduct()->getCustomOption('attributes');
        
        /*
         echo '<pre>';
         var_dump("attributes option", array_keys($attributesOption->getData() ));
         var_dump("value", $attributesOption->getValue());
         die;
         */
        
        if ($attributesOption) {
            $this->logger->info('222');
            $selectedConfigurableOptions = $this->serializer->unserialize($attributesOption->getValue());
        }
        $this->logger->info('333');
        
        if (isset($selectedConfigurableOptions) && is_array($selectedConfigurableOptions)) {
            $this->logger->info('444');
            $configurableOptions = [];
            foreach ($selectedConfigurableOptions as $optionId => $optionValue) {
                /** @var \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface $option */
                $option = $this->itemOptionValueFactory->create();
                $option->setOptionId($optionId);
                $option->setOptionValue($optionValue);
                $configurableOptions[] = $option;
            }
            $this->logger->info('555');
            $productOption = $cartItem->getProductOption()
            ? $cartItem->getProductOption()
            : $this->productOptionFactory->create();
            $this->logger->info('666');
            /** @var  \Magento\Quote\Api\Data\ProductOptionExtensionInterface $extensibleAttribute */
            $extensibleAttribute =  $productOption->getExtensionAttributes()
            ? $productOption->getExtensionAttributes()
            : $this->extensionFactory->create();
            $this->logger->info('777');
            $extensibleAttribute->setConfigurableItemOptions($configurableOptions);
            $productOption->setExtensionAttributes($extensibleAttribute);
            $cartItem->setProductOption($productOption);
            $this->logger->info('888');
        }
        $this->logger->info('999');
        return $cartItem;
    }
    
}