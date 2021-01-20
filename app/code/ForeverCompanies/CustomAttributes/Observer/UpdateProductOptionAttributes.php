<?php

namespace ForeverCompanies\CustomAttributes\Observer;

use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Eav\Model\Config;
use Magento\Framework\App\State;
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

    /**
     * @var State
     */
    protected $state;

    /**
     * UpdateProductOptionAttributes constructor.
     * @param Config $eavConfig
     * @param ProductRepository $productRepository
     * @param State $state
     */
    public function __construct(
        Config $eavConfig,
        ProductRepository $productRepository,
        State $state
    ) {
        $this->eavConfig = $eavConfig;
        $this->productRepository = $productRepository;
        $this->state = $state;
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
        $errors = [];
        /** @var Product $product */
        $product = $observer->getData('data_object');
        if ($product->getId() == null || $product->getData('is_transformed') != 1) {
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
            if ($attribute == null) {
                continue;
            }
            $source = $this->eavConfig->getAttribute(Product::ENTITY, $attribute)->getSource();
            $value = [];
            $optionValues = $option->getValues() ?? $option->getData('values');
            foreach ($optionValues as $optionValue) {
                $setValue = $source->getOptionId($optionValue['title']);
                if ($setValue == null) {
                    $errors[] = $optionValue['title'];
                }
                if ($setValue == null && $optionValue['title'] == 'Round Brilliant') {
                    $setValue = $source->getOptionId('Round');
                }
                $value[] = $setValue;
            }
            $value = array_filter(array_unique($value));
            $product->setData($attribute, implode(',', $value));
        }
        if (count($errors) > 0) {
            if ($this->state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
                throw new LocalizedException(__('Can\'t save product attributes: ' . implode(', ', $errors)));
            }
        }
        foreach ($oldProductOptions as $oldProductOption) {
            $attribute = $oldProductOption->getData('customization_type');
            $product->setData($attribute, '');
        }
        if ($product->getTypeId() == Type::TYPE_CODE) {
            $childrenIds = $product->getTypeInstance()->getChildrenIds($product->getId());
            $childrenIds = array_filter($childrenIds, function ($element) {
                return !empty($element);
            });
            $source = $this->eavConfig->getAttribute(Product::ENTITY, 'matching_band')->getSource();
            /** @var ProductExtensionInterface $extension */
            $extension = $product->getExtensionAttributes();
            $flag = $this->checkMatchingBands($extension->getBundleProductOptions());
            if (count($childrenIds) > 0 && $flag) {
                $product->setData('matching_band', $source->getOptionId('Yes'));
            } else {
                $product->setData('matching_band', $source->getOptionId('None'));
            }
        }
    }

    /**
     * @param array|null $options
     * @return bool
     */
    protected function checkMatchingBands($options)
    {
        if ($options == null) {
            return false;
        }
        /** @var Option $option */
        foreach ($options as $option) {
            if ($option->getTitle() == 'Matching Band') {
                return true;
            }
        }
        return false;
    }
}
