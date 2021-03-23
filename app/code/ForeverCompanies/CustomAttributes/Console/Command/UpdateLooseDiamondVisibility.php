<?php

namespace ForeverCompanies\CustomAttributes\Console\Command;

use ForeverCompanies\CustomAttributes\Helper\TransformData;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class UpdateLooseDiamondVisibility extends Command
{
    /**
     * @var string
     */
    protected $name = 'forevercompanies:update-loose-diamonds-visibility';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Action
     */
    protected $productActionObject;

    /**
     * @var
     */
    protected $helper;

    /**
     * ID for loose diamond clearance category
     * @var int
     */
    private $looseDiamondClearanceCategoryId = 906;

    /**
     * CreateLooseDiamondsCategory constructor.
     * @param CollectionFactory $collectionFactory
     * @param Action $action
     * @param TransformData $helper
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Action $action,
        TransformData $helper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->productActionObject = $action;
        $this->helper = $helper;
        parent::__construct($this->name);
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Update Loose Diamonds visibility...");

        // array of all product ids to change visibility on
        $entityIds = [];

        // get product collection
        $productCollection = $this->helper->getProductsLooseDiamonds();
        $output->writeln('Loose diamonds found: ' . $productCollection->count());

        // loop through each product and determine if it's in loose diamond clearance category
        // if not, add it to our array to change
        $i = 1;
        foreach ($productCollection->getItems() as $item) {
            $categoryIds = $item->getCategoryIds();
            if (is_array($categoryIds) && !empty($categoryIds) && !in_array($this->looseDiamondClearanceCategoryId, $categoryIds)) {
                $entityIds[] = (int) $item->getData('entity_id');
                $str = "added to update";
            } else {
                $str = "skipped";
            }
            $output->writeln("#" . $i . " - " . $item->getData('entity_id') . " - " . $str);
            $i++;
        }

        // if there are entity ids in our array, lets do the update...
        $output->writeln('Starting update...');
        if (!empty($entityIds)) {
            $this->productActionObject->updateAttributes(
                $entityIds,
                ['visibility' => Visibility::VISIBILITY_BOTH],
                0
            );
        }

        $output->writeln('Total products updated: ' . sizeof($entityIds));
        $output->writeln('Loose stones are updated! Please execute bin/magento cache:clean');
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
