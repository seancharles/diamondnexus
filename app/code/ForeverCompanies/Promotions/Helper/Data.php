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
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\RuleFactory;
use Magento\CatalogRule\Model\Rule\Job;
use Magento\Catalog\Model\ProductRepository;

class Data extends AbstractHelper
{

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
     * Data constructor.
     * @param Rule $rule
     * @param RuleFactory $ruleFactory
     * @param Job $job
     * @param Logger $logger
     * @param Context $context
     */

    public function __construct(
        RuleFactory $ruleFactory,
        Job $job,
        Logger $logger,
        Context $context
    )
    {
        $this->ruleFactory = $ruleFactory;
        $this->job = $job;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function createCatalogRulesConditions(){
        try {
            $catalogPriceRule =  $this->ruleFactory->create();
            $catalogPriceRule->setName('forevercompanies_promotions')
                ->setDescription('forevercompanies_promotions')
                ->setIsActive(1)
                ->setCustomerGroupIds(array(1))
                ->setWebsiteIds(array(1))
                ->setFromDate('')
                ->setToDate('')
                ->setSimpleAction('by_fixed')
                ->setDiscountAmount(1)
                ->setStopRulesProcessing(0);
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
            echo "Catalog Price Rule created\n";
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
