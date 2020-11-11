<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Console\Command;

use ForeverCompanies\CustomAttributes\Helper\TransformData;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\CertifiedStone;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\ChainLength;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\ChainSize;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\Color;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\CutType;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\MetalType;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\RingSize;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\Shape;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\State;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TransformValues extends AbstractCommand
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var string
     */
    protected $name = 'forevercompanies:values-multiselect';

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * TransformMultiselect constructor.
     * @param State $state
     * @param TransformData $helper
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Config $eavConfig
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        State $state,
        TransformData $helper,
        ModuleDataSetupInterface $moduleDataSetup,
        Config $eavConfig,
        EavSetupFactory $eavSetupFactory
    ) {
        parent::__construct($state, $helper);
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavConfig = $eavConfig;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        $output->writeln("Prepare values for transformation to multiselectable fields...");
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->moveValues('chain_length', $output);
        $output->writeln('Chain Length is updated');
        $this->moveValues('chain_size', $output);
        $output->writeln('Chain Size is updated');
        $this->moveValues('metal_type', $output);
        $output->writeln('Metal Type is updated');
        $this->moveValues('ring_size', $output);
        $output->writeln('Ring Size is updated');
        $this->moveValues('certified_stone', $output);
        $output->writeln('Certified Stone is updated');
        $this->moveValues('color', $output);
        $output->writeln('Color is updated');
        $this->moveValues('cut_type', $output);
        $output->writeln('Cut Type is updated');
        $this->moveValues('shape', $output);
        $output->writeln('Shape is updated');
        $this->moduleDataSetup->getConnection()->endSetup();
        $output->writeln('Transformation is complete! Please execute bin/magento cache:clean');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Transform values from select to multiselect after multiselect transformation");
        parent::configure();
    }

    /**
     * @param $name
     * @param $output
     */
    protected function moveValues($name, $output)
    {
        /**
         * insert into catalog_product_entity_varchar (entity_type_id, attribute_id, store_id, entity_id, value)
         * select entity_type_id, attribute_id, store_id, entity_id, value
         * from catalog_product_entity_int where attribute_id = ? ;
         */
        try {
            $columns = ['store_id', 'row_id', 'value', 'attribute_id'];
            $attributeId = $this->eavConfig->getAttribute(Product::ENTITY, $name)->getAttributeId();
            $select = $this->moduleDataSetup->getConnection()
                ->select()
                ->from($this->moduleDataSetup->getTable('catalog_product_entity_int'), $columns)
                ->where('attribute_id = ?', $attributeId);
            $this->moduleDataSetup->getConnection()->query(
                $this->moduleDataSetup->getConnection()->insertFromSelect(
                    $select,
                    $this->moduleDataSetup->getTable('catalog_product_entity_varchar'),
                    $columns,
                    AdapterInterface::INSERT_ON_DUPLICATE
                )
            );
            $this->moduleDataSetup->getConnection()->query(
                $this->moduleDataSetup->getConnection()->deleteFromSelect(
                    $select,
                    $this->moduleDataSetup->getTable('catalog_product_entity_int')
                )
            );
        } catch (LocalizedException $e) {
            $output->writeln("Can't move values for {$name} - " . $e->getMessage());
        }
    }
}
