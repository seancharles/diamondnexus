<?php

namespace ForeverCompanies\CustomAttributes\Observer;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
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

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    public function __construct(
        Config $eavConfig,
        ProductRepository $productRepository
    ) {
        $this->eavConfig = $eavConfig;
        $this->productRepository = $productRepository;
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
        $product = $observer->getData('data_object');
        if ($product->getId() == null) {
            return;
        }
        $oldProductOptions = $this->productRepository->getById($product->getId())->getOptions();
        foreach ($product->getOptions() as $option) {
            foreach ($oldProductOptions as $key => $oldProductOption) {
                if ($option->getTitle() == $oldProductOption->getTitle()) {
                    unset($oldProductOptions[$key]);
                }
            }
            $attribute = $option->getData('customization_type');
            $source = $this->eavConfig->getAttribute(Product::ENTITY, $attribute)->getSource();
            $value = [];
            $optionValues = $option->getValues() ?? $option->getData('values');
            foreach ($optionValues as $optionValue) {
                $setValue = $source->getOptionId($optionValue['title']);
                if ($setValue == null && $optionValue['title'] == 'Round Brilliant') {
                    $setValue = $source->getOptionId('Round');
                }
                $value[] = $setValue;
            }
            $product->setData($attribute, implode(',', $value));
        }
        foreach ($oldProductOptions as $oldProductOption) {
            $attribute = $oldProductOption->getData('customization_type');
            $product->setData($attribute, '');
        }
        if ($product->getTypeId() == Type::TYPE_CODE) {
            $source = $this->eavConfig->getAttribute(Product::ENTITY, 'matching_band')->getSource();
            if (count($product->getTypeInstance()->getChildrenIds($product->getId())) > 0) {
                $product->setData('matching_band', $source->getOptionId('Yes'));
            } else {
                $product->setData('matching_band', $source->getOptionId('None'));
            }
        }
    }
}
