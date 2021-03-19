<?php

namespace ForeverCompanies\CustomAttributes\Console\Command;

use ForeverCompanies\CustomAttributes\Helper\TransformData;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\CategoryFactory;

class CreateLooseDiamondsCategory extends Command
{
    /**
     * @var string
     */
    protected $name = 'forevercompanies:create-loose-diamonds-category';

    /**
     * @var State
     */
    private $state;

    /**
     * @var ProductInterface
     */
    protected $productRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var
     */
    protected $helper;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * The root category that should be used for TF
     * @var int
     */
    private $tfRootCategoryId = 597;

    /**
     * The new category that should be used for Loose Diamonds
     * @var int
     */
    private $newCategoryId = 926;

    /**
     * Clearance Lab Grown Diamonds category id
     * @var int
     */
    private $clearanceCategoryId = 906;

    /**
     * The store code for TF
     * @var string
     */
    private $tfStoreCode = 'www_1215diamonds_com';

    /**
     * Loose Diamonds category name
     * @var string
     */
    private $looseDiamondsCatName = 'TF Loose Diamonds';

    /**
     * CreateLooseDiamondsCategory constructor.
     * @param State $state
     * @param ProductInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param CategoryFactory $categoryFactory
     * @param TransformData $helper
     */
    public function __construct(
        State $state,
        ProductInterface $productRepository,
        StoreManagerInterface $storeManager,
        CategoryFactory $categoryFactory,
        TransformData $helper
    ) {
        $this->state = $state;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryFactory;
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
        try {
            $this->state->getAreaCode();
        } catch (LocalizedException $e) {
            $this->state->setAreaCode(Area::AREA_GLOBAL);
        }

        $output->writeln("Area code: " . $this->state->getAreaCode());

        $output->writeln("Create new Loose Diamonds category...");

        // set current store to TF
        $this->storeManager->setCurrentStore($this->tfStoreCode);
        $tfStore = $this->storeManager->getStore();

        // get the TF group
        $tfGroup = $this->storeManager->getGroup($tfStore->getStoreGroupId());

        // check the group root category id
        $output->writeln("Group id: " . $tfGroup->getId() . " | " . $tfGroup->getName());
        $output->writeln("Group Root Cat: " . $tfGroup->getRootCategoryId());

        if ($tfGroup->getRootCategoryId() != $this->tfRootCategoryId) {
            $output->writeln("Update group root cat...");
            $tfGroup->setRootCategoryId($this->tfRootCategoryId);
            $tfGroup->save();
        }

        // check the group root category id
        $output->writeln("Group id: " . $tfGroup->getId() . " | " . $tfGroup->getName());
        $output->writeln("Group Root Cat: " . $tfGroup->getRootCategoryId());

        // get the root category (parent)
        $rootCategory = $this->categoryFactory->create()->load($this->tfRootCategoryId);

        // create new category for loose diamonds if it doesn't already exist
        $category = $this->categoryFactory->create();

        $cat = $category->getCollection()
            ->addAttributeToFilter('entity_id', $this->newCategoryId)
            ->getFirstItem();

        if (!$cat->getId()) {
            $category->setPath($rootCategory->getPath())
                ->setParentId($this->tfRootCategoryId)
                ->setName($this->looseDiamondsCatName)
                ->setisActive(true)
                ->setIncludeInMenu(false);
            $category->save();

            $cat = $category->getCollection()
                ->addAttributeToFilter('name', $this->looseDiamondsCatName)
                ->getFirstItem();
        }

        if ($cat->getId() != $this->newCategoryId) {
            $output->writeln("TF Loose Diamonds Category ID not found.");
            return;
        }

        // get all products assigned to the "Migration_Loose Diamonds" attribute set and add them to
        // the new Loose Diamonds category...
        $output->writeln("Add loose diamonds to new category...");
        $productCollection = $this->helper->getProductsLooseDiamonds();
        $output->writeln('Loose diamonds found: ' . $productCollection->count());
        $i = 1;
        foreach ($productCollection->getItems() as $item) {
            $result = $this->helper->updateLooseDiamond((int)$item->getData('entity_id'), $this->newCategoryId);
            $output->writeln("#" . $i . " - Product ID: " . $item->getData('entity_id') . " - " . $result);
            $i++;
//            if ($i > 17) {
//                return;
//            }
        }
        $output->writeln('Loose stones are updated! Please execute bin/magento cache:clean');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Create Loose Diamonds category and assign products");
        parent::configure();
    }
}
