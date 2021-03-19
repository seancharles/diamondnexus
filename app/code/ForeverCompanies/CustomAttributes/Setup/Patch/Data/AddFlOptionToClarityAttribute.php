<?php

namespace ForeverCompanies\CustomAttributes\Setup\Patch\Data;


use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;


/**
 * Class AddFlOptionToClarityAttribute
 */
class AddFlOptionToClarityAttribute implements DataPatchInterface
{
    protected $moduleDataSetup;
    protected $eavSetupFactory;
    protected $eavAttributeFactory;
    protected $attributeOptionManagement;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        AttributeFactory $eavAttributeFactory,
        AttributeOptionManagementInterface $attributeOptionManagement
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavAttributeFactory = $eavAttributeFactory;
        $this->attributeOptionManagement = $attributeOptionManagement;
    }

    /**
     * @return AddHoverImageAttribute|void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function apply()
    {
        $magentoAttribute = $this->eavAttributeFactory->create()->loadByCode('catalog_product', 'clarity');

        $attributeCode = $magentoAttribute->getAttributeCode();
        $magentoAttributeOptions = $this->attributeOptionManagement->getItems(
            'catalog_product',
            $attributeCode
        );
        $attributeOptions = ['FL'];
        $existingMagentoAttributeOptions = [];
        $newOptions = [];
        $counter = 0;
        foreach ($magentoAttributeOptions as $option) {
            if (!$option->getValue()) {
                continue;
            }
            if ($option->getLabel() instanceof Phrase) {
                $label = $option->getText();
            } else {
                $label = $option->getLabel();
            }

            if ($label == '') {
                continue;
            }

            $existingMagentoAttributeOptions[] = $label;
            $newOptions['value'][$option->getValue()] = [$label, $label];
            $counter++;
        }

        foreach ($attributeOptions as $option) {
            if ($option == '') {
                continue;
            }

            if (!in_array($option, $existingMagentoAttributeOptions)) {
                $newOptions['value']['option_'.$counter] = [$option, $option];
            }

            $counter++;
        }

        if (count($newOptions)) {
            $magentoAttribute->setOption($newOptions)->save();
        }

    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}
