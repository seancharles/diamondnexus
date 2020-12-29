<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Console\Command;

use Magento\CatalogStaging\Model\ResourceModel\ProductSequence;
use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\PatchHistory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshPatchList extends Command
{

    /**
     * @var string
     */
    protected $name = 'forevercompanies:refresh-patch-list';

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
        $this->state->setAreaCode(Area::AREA_GLOBAL);
        $output->writeln("Delete patch list of custom attributes...");
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(PatchHistory::TABLE_NAME)
            ->where(PatchHistory::CLASS_NAME . ' like "%ForeverCompanies_CustomAttributes%"');
        $delete = $connection->deleteFromSelect($select, $connection->getTableName(PatchHistory::TABLE_NAME));
        $connection->query($delete);
        $select = $connection->select()
            ->from(PatchHistory::TABLE_NAME)
            ->where(PatchHistory::CLASS_NAME . ' like "%ForeverCompanies_Salesforce%"');
        $delete = $connection->deleteFromSelect($select, $connection->getTableName(PatchHistory::TABLE_NAME));
        $connection->query($delete);
        $selectProducts = $connection->select()->from('catalog_product_entity');
        foreach ($connection->fetchAll($selectProducts) as $product) {
            $output->writeln('Update product ID = ' .$product['entity_id']);
            $this->addSequence($product['entity_id']);
        }
        $output->writeln('Refresh is complete! Please execute bin/magento setup:upgrade');
    }

    /**
     * @param $id
     */
    private function addSequence($id)
    {
        $tableName = $this->resource->getTableName(ProductSequence::SEQUENCE_TABLE);
        $this->resource->getConnection()->insertOnDuplicate($tableName, ['sequence_value' => $id]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Refresh setup patch list for correct migrate attributes from 2.3.6 to 2.4.1");
        parent::configure();
    }
}
