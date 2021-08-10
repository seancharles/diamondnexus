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
        $attributesOption = $cartItem->getProduct()->getCustomOption('attributes');
        
        if ($attributesOption) {
            $selectedConfigurableOptions = $this->serializer->unserialize($attributesOption->getValue());
        }
        
        if (isset($selectedConfigurableOptions) && is_array($selectedConfigurableOptions)) {
            $configurableOptions = [];
            foreach ($selectedConfigurableOptions as $optionId => $optionValue) {
                /** @var \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface $option */
                $option = $this->itemOptionValueFactory->create();
                $option->setOptionId($optionId);
                $option->setOptionValue($optionValue);
                $configurableOptions[] = $option;
            }
            $productOption = $cartItem->getProductOption()
            ? $cartItem->getProductOption()
            : $this->productOptionFactory->create();
            /** @var  \Magento\Quote\Api\Data\ProductOptionExtensionInterface $extensibleAttribute */
            $extensibleAttribute =  $productOption->getExtensionAttributes()
            ? $productOption->getExtensionAttributes()
            : $this->extensionFactory->create();
            $extensibleAttribute->setConfigurableItemOptions($configurableOptions);
            $productOption->setExtensionAttributes($extensibleAttribute);
            $cartItem->setProductOption($productOption);
        }
        return $cartItem;
    }
}
