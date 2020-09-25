<?php

namespace ForeverCompanies\CustomAttributes\Observer;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class UpdateProductOptionAttributes implements ObserverInterface
{
    /**
     * @var Config
     */
    protected $eavConfig;

    public function __construct(
        Config $eavConfig
    )
    {
        $this->eavConfig = $eavConfig;
    }

    /**
     * Handle catalog_product_save_before event
     *
     * @param EventObserver $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getData('data_object');
        foreach ($product->getOptions() as $option) {
            $attribute = $option->getData('customization_type');
            $source = $this->eavConfig->getAttribute(Product::ENTITY, $attribute)->getSource();
            $value = $product->getData($attribute);
            foreach ($option->getData('values') as $optionValue) {
                $isSetBefore = false;
                $setValue = $source->getOptionId($optionValue['title']);
                foreach ($value as $item) {
                    if ($item == $setValue) {
                        $isSetBefore = true;
                    }
                }
                if (!$isSetBefore) {
                    $value[] = $setValue;
                }
            }
            $product->setData($attribute, $value);
        }
    }
}
