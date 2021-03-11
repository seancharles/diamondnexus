<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\PatchHistory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompleteSequence extends Command
{

    /**
     * @var string
     */
    protected $name = 'forevercompanies:update-all-sequence';

    /**
     * @var State
     */
    protected $state;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * RefreshPatchList constructor.
     * @param State $state
     * @param ResourceConnection $resourceConnection
     * @param string|null $name
     */
    public function __construct(
        State $state,
        ResourceConnection $resourceConnection,
        string $name = null
    ) {
        parent::__construct($name);
        $this->state = $state;
        $this->resource = $resourceConnection;
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
        $output->writeln("Get all categories for create sequences...");
        $connection = $this->resource->getConnection();
        $selectCategories = $connection->select()->from('catalog_category_entity');
        foreach ($connection->fetchAll($selectCategories) as $category) {
            $output->writeln('Update category ID = ' .$category['entity_id']);
            $this->addSequence($category['entity_id']);
        }
        $selectProducts = $connection->select()->from('catalog_product_entity');
        foreach ($connection->fetchAll($selectProducts) as $product) {
            $output->writeln('Update product ID = ' .$product['entity_id']);
            $this->addSequenceProduct($product['entity_id']);
        }
        $output->writeln('Setting sequences are complete! Please execute bin/magento cache:clean');
    }

    /**
     * @param string $id
     */
    private function addSequence($id)
    {
        $tableName = $this->resource->getTableName('sequence_catalog_category');
        $this->resource->getConnection()->insertOnDuplicate($tableName, ['sequence_value' => $id]);
    }

    /**
     * @param string $id
     */
    private function addSequenceProduct($id)
    {
        $tableName = $this->resource->getTableName('sequence_product');
        $this->resource->getConnection()->insertOnDuplicate($tableName, ['sequence_value' => $id]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Set sequences for all categories");
        parent::configure();
    }
}
