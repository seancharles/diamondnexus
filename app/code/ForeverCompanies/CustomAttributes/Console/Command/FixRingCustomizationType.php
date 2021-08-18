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

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Option;

class FixRingCustomizationType extends Command
{

    /**
     * @var string
     */
    protected $name = 'forevercompanies:fix-ring-customization-type';

    /**
     * @var State
     */
    protected $state;

    /**
     * @var ResourceConnection
     */
    protected $resource;
    
    protected $productColl;
    protected $productOptionModel;

    /**
     * RefreshPatchList constructor.
     * @param State $state
     * @param ResourceConnection $resourceConnection
     * @param string|null $name
     */
    public function __construct(
        State $state,
        ResourceConnection $resourceConnection,
        CollectionFactory $collectionFac,
        Option $opt,
        string $name = null
    ) {
        parent::__construct($name);
        $this->state = $state;
        $this->resource = $resourceConnection;
        
        $this->productColl = $collectionFac->create();
        $this->productOptionModel = $opt;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $output->writeln('Starting..');
        
        $this->productColl->addFieldToFilter('attribute_set_id', 18);
        $this->productColl->addAttributeToSelect("name")->load();
        
        foreach ($this->productColl as $product) {
            $options = $this->productOptionModel->getProductOptionCollection($product);
            foreach ($options as $o) {
                $o->setCustomizationType('ring_size');
                try {
                    $o->save();
                } catch(\Magento\Framework\Validator\Exception $e) {
                    continue;
                } catch(\Exception $e) {
                    continue;
                }
            }
        }
        $output->writeln('Complete');
    }

    /**
     * @param string $id
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
