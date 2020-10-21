<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ForeverCompanies\Rules\Model;

use Magento\Rule\Model\AbstractModel;

/**
 * Class Rule
 * @package ForeverCompanies\Rules\Model
 *
 * @method int|null getRuleId()
 * @method Rule setRuleId(int $id)
 */
class Rule extends AbstractModel
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'forevercompanies_rules';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getRule() in this case
     *
     * @var string
     */
    protected $_eventObject = 'rule';

    /** @var \Magento\SalesRule\Model\Rule\Condition\CombineFactory */

    protected $condCombineFactory;

    /** @var \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory */

    protected $condProdCombineF;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $eavModel;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Repository
     */

    protected $productAttributeRepository;

    /**
     * Store already validated addresses and validation results
     *
     * @var array
     */
    protected $validatedAddresses = [];

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory
     * @param \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavModel,
     * @param \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository;
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavModel,
        \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->condCombineFactory = $condCombineFactory;
        $this->condProdCombineF = $condProdCombineF;
        $this->eavModel = $eavModel;
        $this->productAttributeRepository = $productAttributeRepository;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('ForeverCompanies\Rules\Model\ResourceModel\Rule');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Get rule condition combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    /**
     * Get rule condition product combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        return $this->condProdCombineF->create();
    }

    /**
     * @return array
     */
    public function getDatesForShipperHq()
    {

        $attributeId = 404;
        $this->eavModel->load($attributeId);
        $attributeCode = $this->eavModel->getAttributeCode();
        $options = $this->productAttributeRepository->get($attributeCode)->getOptions();
        $result = [];

        foreach ($options as $option)
        {
            $result[] = [
                'value' => $option->getValue(),
                'label' => __($option->getLabel())
            ];
        }

        return $result;
    }
}
