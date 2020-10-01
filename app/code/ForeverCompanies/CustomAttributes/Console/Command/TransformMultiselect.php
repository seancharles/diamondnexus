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
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\State;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TransformMultiselect extends AbstractCommand
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
    protected $name = 'forevercompanies:attributes-multiselect';

    /**
     * TransformMultiselect constructor.
     * @param State $state
     * @param TransformData $helper
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        State $state,
        TransformData $helper,
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    )
    {
        parent::__construct($state, $helper);
        $this->moduleDataSetup = $moduleDataSetup;
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
        $output->writeln("Prepare attributes for transformation to multiselectable fields...");
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'chain_length',
            'source_model',
            ChainLength::class
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'chain_length',
            'backend_type',
            'varchar'
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'chain_length',
            'frontend_input',
            'multiselect'
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'chain_length',
            'backend_model',
            ArrayBackend::class
        );
        $output->writeln('Chain Length is updated');
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'chain_size',
            'source_model',
            ChainSize::class
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'chain_size',
            'backend_type',
            'varchar'
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'chain_size',
            'frontend_input',
            'multiselect'
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'chain_size',
            'backend_model',
            ArrayBackend::class
        );
        $output->writeln('Chain Size is updated');
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'metal_type',
            'source_model',
            MetalType::class
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'metal_type',
            'backend_type',
            'varchar'
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'metal_type',
            'frontend_input',
            'multiselect'
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'metal_type',
            'backend_model',
            ArrayBackend::class
        );
        $output->writeln('Metal Type is updated');
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'ring_size',
            'source_model',
            RingSize::class
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'ring_size',
            'backend_type',
            'varchar'
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'ring_size',
            'frontend_input',
            'multiselect'
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'ring_size',
            'backend_model',
            ArrayBackend::class
        );
        $output->writeln('Ring Size is updated');
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'certified_stone',
            'source_model',
            CertifiedStone::class
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'certified_stone',
            'backend_type',
            'varchar'
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'certified_stone',
            'frontend_input',
            'multiselect'
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'certified_stone',
            'backend_model',
            ArrayBackend::class
        );
        $output->writeln('Certified Stone is updated');
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'color',
            'source_model',
            Color::class
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'color',
            'backend_type',
            'varchar'
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'color',
            'frontend_input',
            'multiselect'
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'color',
            'backend_model',
            ArrayBackend::class
        );
        $output->writeln('Color is updated');
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'cut_type',
            'source_model',
            CutType::class
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'cut_type',
            'backend_type',
            'varchar'
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'cut_type',
            'frontend_input',
            'multiselect'
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'cut_type',
            'backend_model',
            ArrayBackend::class
        );
        $output->writeln('Cut Type is updated');
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'shape',
            'source_model',
            Shape::class
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'shape',
            'backend_type',
            'varchar'
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'shape',
            'frontend_input',
            'multiselect'
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'shape',
            'backend_model',
            ArrayBackend::class
        );
        $output->writeln('Shape is updated');
        $this->moduleDataSetup->getConnection()->endSetup();
        $output->writeln('Transformation is complete! Please execute bin/magento indexer:reindex');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Transform fields from select to multiselect after attribute transformation");
        parent::configure();
    }
}
