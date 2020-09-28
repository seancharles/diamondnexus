<?php

namespace ForeverCompanies\CustomAttributes\Ui\DataProvider\Product\Form\Modifier;

use ForeverCompanies\CustomAttributes\Logger\Logger;
use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundlePanel;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Api\ProductOptionRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Composite extends \Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\Composite
{

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        LocatorInterface $locator,
        ObjectManagerInterface $objectManager,
        ProductOptionRepositoryInterface $optionsRepository,
        ProductRepositoryInterface $productRepository,
        Logger $logger,
        array $modifiers = []
    ) {
        parent::__construct($locator, $objectManager, $optionsRepository, $productRepository, $modifiers);
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();
        $modelId = $product->getId();
        $isBundleProduct = $product->getTypeId() === Type::TYPE_CODE;
        if (!$isBundleProduct && !$modelId) {
            return $data;
        }
        $data[$modelId][BundlePanel::CODE_BUNDLE_OPTIONS][BundlePanel::CODE_BUNDLE_OPTIONS] = [];
        try {
            /** @var Option $option */
            foreach ($this->optionsRepository->getList($product->getSku()) as $option) {
                $selections = [];
                /** @var LinkInterface $productLink */
                foreach ($option->getProductLinks() as $productLink) {
                    $linkedProduct = $this->productRepository->get($productLink->getSku());
                    $integerQty = 1;
                    if ($linkedProduct->getExtensionAttributes()->getStockItem()) {
                        if ($linkedProduct->getExtensionAttributes()->getStockItem()->getIsQtyDecimal()) {
                            $integerQty = 0;
                        }
                    }
                    $selections[] = [
                        'selection_id' => $productLink->getId(),
                        'option_id' => $productLink->getOptionId(),
                        'product_id' => $linkedProduct->getId(),
                        'name' => $linkedProduct->getName(),
                        'sku' => $linkedProduct->getSku(),
                        'is_default' => ($productLink->getIsDefault()) ? '1' : '0',
                        'selection_price_value' => $productLink->getPrice(),
                        'selection_price_type' => $productLink->getPriceType(),
                        'selection_qty' => $integerQty ? (int)$productLink->getQty() : $productLink->getQty(),
                        'selection_can_change_qty' => $productLink->getCanChangeQuantity(),
                        'selection_qty_is_integer' => (bool)$integerQty,
                        'position' => $productLink->getPosition(),
                        'delete' => '',
                    ];
                }
                $data[$modelId][BundlePanel::CODE_BUNDLE_OPTIONS][BundlePanel::CODE_BUNDLE_OPTIONS][] = [
                    'position' => $option->getPosition(),
                    'option_id' => $option->getOptionId(),
                    'title' => $option->getTitle(),
                    'bundle_customization_type' => $option->getData('bundle_customization_type'),
                    'is_dynamic_selection' => $option->getData('is_dynamic_selection'),
                    'default_title' => $option->getData('default_title'),
                    'type' => $option->getType(),
                    'required' => ($option->getRequired()) ? '1' : '0',
                    'bundle_selections' => $selections,
                ];
            }
        } catch (InputException $e) {
            $this->logger->error('Can\'t show bundle custom options - ' . $e->getMessage());
        } catch (NoSuchEntityException $e) {
            $this->logger->error('Can\'t find bundle product - ' . $e->getMessage());
        }
        return $data;
    }
}
