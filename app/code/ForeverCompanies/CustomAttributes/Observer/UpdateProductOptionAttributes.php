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
    ) {
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
            $stringFlag = 0;
            if (!is_array($value)) {
                $value = explode(',', $value);
                $stringFlag = 1;
            }
            $optionValues = $option->getValues() ?? $option->getData('values');
            foreach ($optionValues as $optionValue) {
                $isSetBefore = false;
                $setValue = $source->getOptionId($optionValue['title']);
                if ($value !== null) {
                    foreach ($value as $item) {
                        if ($item == $setValue) {
                            $isSetBefore = true;
                        }
                    }
                }
                if (!$isSetBefore) {
                    $value[] = $setValue;
                }
            }
            if ($stringFlag == 1) {
                $value = implode(',', $value);
            }
            $product->setData($attribute, $value);
        }
    }
}
