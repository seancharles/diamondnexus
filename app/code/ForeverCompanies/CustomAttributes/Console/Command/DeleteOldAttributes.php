<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Console\Command;

use ForeverCompanies\CustomAttributes\Helper\TransformData;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteOldAttributes extends AbstractCommand
{

    /**
     * @var string
     */
    protected $name = 'forevercompanies:delete-old-attributes';

    /**
     * @var string[]
     */
    protected $oldAttributes = [
        'deactivated_date',
        'default_category',
        'display_price_range',
        'engraving',
        'exclude_from_tealium',
        //'filter_carat_weight',
        //'filter_color',
        //'filter_metal',
        //'filter_shape',
        //'filter_ship_date',
        'gender',
        'gift_card_amount',
        'img_matching_band',
        'important_info',
        'in_sitemap',
        'information_advisory',
        'lifestyle_0',
        'limelight',
        'limited_warranty',
        'location',
        'miusa',
        'product_hash',
        'resizing_advisory',
        'returnable',
        'ring_size_advisory',
        'tcw',
        'video_url',
        'youtube'
    ];

    /**
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * DeleteOldAttributes constructor.
     * @param State $state
     * @param TransformData $helper
     * @param CategorySetupFactory $categorySetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        State $state,
        TransformData $helper,
        CategorySetupFactory $categorySetupFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        parent::__construct($state, $helper);
        $this->categorySetupFactory = $categorySetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        try {
            $this->state->getAreaCode();
        } catch (LocalizedException $e) {
            $this->state->setAreaCode(Area::AREA_GLOBAL);
        }
        $output->writeln("Delete old attributes...");
        $eavSetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        foreach ($this->oldAttributes as $oldAttribute) {
            if ($eavSetup->getAttributeId(Product::ENTITY, $oldAttribute)) {
                $output->writeln("Delete $oldAttribute");
                $eavSetup->removeAttribute(Product::ENTITY, $oldAttribute);
            }
        }
        $output->writeln('Transformation is complete! Please execute bin/magento indexer:reindex');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Delete old attributes after M1 - M2 media migration");
        parent::configure();
    }
}
