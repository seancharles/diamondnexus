<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Promotions\Helper;

use ForeverCompanies\Promotions\Logger\Logger;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\Rule\Job;
use Magento\CatalogRule\Model\RuleFactory;


class Data extends AbstractHelper
{
    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    protected $customOptionRepository;

    /**
     * @var CollectionFactory
     */
    protected $productCollection;

    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var Job
     */
    protected $job;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Model constructor.
     *
     * @param Rule $rule
     * @param RuleFactory $ruleFactory
     * @param Job $job
     * @param ProductCustomOptionRepositoryInterface $customOptionsRepository
     * @param CollectionFactory $productCollection
     * @param Logger $logger
     */

    public function __construct(
        Context $context,
        RuleFactory $ruleFactory,
        Rule $rule,
        Job $job,
        ProductCustomOptionRepositoryInterface $customOptionsRepository,
        CollectionFactory $productCollection,
        Logger $logger
    )
    {
        parent::__construct($context);
        $this->ruleFactory = $ruleFactory;
        $this->rule = $rule;
        $this->job = $job;
        $this->customOptionRepository = $customOptionsRepository;
        $this->productCollection = $productCollection;
        $this->logger = $logger;
    }

    public function selectProductsWithCustomOptions()
    {
        $collection = $this->productCollection->create()
            ->addAttributeToSelect(['name','sku','metal_type'])
            ->load();
        $collection->getSelect()->limit(4);
        foreach ($collection as $product){
            $name = $product->getName();
            $sku = $product->getSku();
            $customOptions = $this->getCustomOptions($sku);
            if ($customOptions){
                if($sku == 'LRRHXX7033XPCWHXX0171CS0XXXX'){
                    echo "Found custom options for product with sku = " . $sku . " exists \n";
                    $this->createCatalogRulesForProduct($name,$sku);
                }

            }
        }
    }

    public function createCatalogRulesForProduct($name, $sku){
        try {


            $catalogPriceRule =  $this->ruleFactory->create();
            $desc = 'Create Catalog Price Rule for product ' . $name . '.';
            $catalogPriceRule->setName($desc)
                ->setDescription($desc)
                ->setIsActive(1)
                ->setCustomerGroupIds(array(1))
                ->setWebsiteIds(array(1))
                ->setFromDate('')
                ->setToDate('')
                ->setSimpleAction('by_fixed')
                ->setDiscountAmount(1)
                ->setStopRulesProcessing(0);

            /**
                $conditions = array();

                $conditions[1] = array(
                    'type' => 'catalogrule/rule_condition_product',
                    'attribute' => 'sku',
                    'operator' => '==',
                    'value' => $sku,
                );
                $catalogPriceRule->setData('conditions',$conditions);
             */

            // Validating rule data before Saving
            $validateResult = $this->rule->validateData(new \Magento\Framework\DataObject($catalogPriceRule->getData()));
            if ($validateResult !== true) {
                foreach ($validateResult as $errorMessage) {
                    echo $errorMessage;
                }
                return;
            }

            $catalogPriceRule->loadPost($catalogPriceRule->getData());
            $this->logger->addInfo($catalogPriceRule->getData('name'));
            $catalogPriceRule->save();
            $this->job->applyAll();
            echo "Catalog Price Rule for product with sku " . $sku . " created! \n";
        } catch (Exception $e) {
            echo $e->getMessage();
        }

    }

    /**
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionInterface[]
     */
    public function getCustomOptions(string $sku)
    {
        return $this->customOptionRepository->getList($sku);
    }

}
