<?php

declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Setup\Patch\Data;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Store\Model\StoreManagerInterface;

class SetTfRootCategory implements DataPatchInterface
{
    /**
     * @var State
     */
    private $state;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * The root category that should be used for TF
     * @var int
     */
    private $tfRootCategoryId = 597;

    /**
     * Constructor
     *
     * @param State $state
     * @param StoreManagerInterface $storeManager
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        State $state,
        StoreManagerInterface $storeManager,
        CategoryFactory $categoryFactory
    ) {
        $this->state = $state;
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function apply()
    {
        try {
            $this->state->getAreaCode();
        } catch (LocalizedException $e) {
            $this->state->setAreaCode(Area::AREA_GLOBAL);
        }

        // set current store to TF
        $this->storeManager->setCurrentStore($this->tfStoreCode);
        $tfStore = $this->storeManager->getStore();

        // get the TF group
        $tfGroup = $this->storeManager->getGroup($tfStore->getStoreGroupId());

        // check the group root category id
        if ($tfGroup->getRootCategoryId() != $this->tfRootCategoryId) {
            $tfGroup->setRootCategoryId($this->tfRootCategoryId);
            $tfGroup->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}
