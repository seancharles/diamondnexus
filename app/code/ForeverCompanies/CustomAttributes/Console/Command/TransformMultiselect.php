<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Console\Command;

use Codeception\Step\Meta;
use ForeverCompanies\CustomAttributes\Helper\TransformData;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\BandWidth;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\CertifiedStone;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\ChainLength;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\ChainSize;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\Color;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\CutType;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\Gemstone;
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

    protected $attributes = [
        'chain_length' => ChainLength::class,
        'chain_size' => ChainSize::class,
        'metal_type' => MetalType::class,
        'ring_size' => RingSize::class,
        'certified_stone' => CertifiedStone::class,
        'color'=> Color::class,
        'cut_type' => CutType::class,
        'shape' => Shape::class,
        'gemstone' => Gemstone::class,
        'band_width' => BandWidth::class
    ];

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
    ) {
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
        foreach ($this->attributes as $name => $class) {
            $eavSetup->updateAttribute(
                Product::ENTITY,
                $name,
                'source_model',
                $class
            );
            $eavSetup->updateAttribute(
                Product::ENTITY,
                $name,
                'backend_type',
                'varchar'
            );
            $eavSetup->updateAttribute(
                Product::ENTITY,
                $name,
                'frontend_input',
                'multiselect'
            );
            $eavSetup->updateAttribute(
                Product::ENTITY,
                $name,
                'backend_model',
                ArrayBackend::class
            );
            $output->writeln($name . ' is updated');
        }
        $output->writeln('Get products for update selectable options ...');
        $productCollection = $this->helper->getProductsAfterTransformCollection();
        $output->writeln('Products for update selectable options: ' . $productCollection->count());
        foreach ($productCollection->getItems() as $item) {
            $output->writeln('In process product ID = ' . $item->getData('entity_id'));
            $this->helper->transformProductSelect((int)$item->getData('entity_id'));
        }
        $this->moduleDataSetup->getConnection()->endSetup();
        $output->writeln('Transformation is complete! Please execute bin/magento cache:clean');
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
