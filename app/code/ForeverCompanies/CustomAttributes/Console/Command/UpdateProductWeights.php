<?php

namespace ForeverCompanies\CustomAttributes\Console\Command;

use ForeverCompanies\CustomAttributes\Helper\TransformData;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;


class UpdateProductWeights extends Command
{
    /**
     * @var string
     */
    protected $name = 'forevercompanies:update-product-weights';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Action
     */
    protected $productActionObject;

    /**
     * CreateLooseDiamondsCategory constructor.
     * @param CollectionFactory $collectionFactory
     * @param Action $action
     * @param TransformData $helper
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Action $action
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->productActionObject = $action;
        parent::__construct($this->name);
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Update product weights");

        // array of all product ids to change visibility on
        $entityIds = [];

        // get product collection
        $productCollection = $this->collectionFactory->create();
        $productCollection->addAttributeToSelect(['entity_id', 'sku', 'weight']);
        //$productCollection->addAttributeToFilter('status', Status::STATUS_ENABLED);
        $productCollection->addAttributeToFilter(
            [
                ['attribute' => 'weight', 'null' => true],
                ['attribute' => 'weight', 'eq' => '']
            ],
            '',
            'left'
        );
        $productCollection->addAttributeToFilter(array(array('attribute'=>'type_id','in' => [
            Type::TYPE_SIMPLE,
            Type::TYPE_BUNDLE,
            Configurable::TYPE_CODE
        ])));

        $output->writeln('Products found: ' . $productCollection->count());

        // loop through each product
        $i = 1;
        foreach ($productCollection->getItems() as $item) {
            $entityIds[] = (int) $item->getData('entity_id');
            $output->writeln("#" . $i . " - " . $item->getData('entity_id'));
            $i++;
        }

        // if there are entity ids in our array, lets do the update...
        $output->writeln('Starting update...');
        if (!empty($entityIds)) {
            $this->productActionObject->updateAttributes(
                $entityIds,
                ['weight' => 1.0],
                0
            );
        }

        $output->writeln('Total products updated: ' . sizeof($entityIds));
        $output->writeln('Weights set! Please execute bin/magento cache:clean');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Update Loose Diamonds visibility");
        parent::configure();
    }
}
