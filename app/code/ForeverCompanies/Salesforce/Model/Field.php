<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model;

use ForeverCompanies\Salesforce\Model\ResourceModel\Field as ResourceField;
use ForeverCompanies\Salesforce\Model\ResourceModel\Field\Collection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

/**
 * Class Field
 *
 * @package ForeverCompanies\Salesforce\Model
 *
 */
class Field extends AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'map';

    /**
     * @var \ForeverCompanies\Salesforce\Model\Connector
     */
    protected $_connector;

    /**
     * @var string
     */
    protected $mageField;

    /**
     * @var string
     */
    protected $mageType;

    /**
     * @var string
     */
    protected $salesType;

    /**
     * @var string
     */
    protected $salesField;

    /**
     * @param Context       $context
     * @param Registry      $registry
     * @param ResourceField $resource
     * @param Collection    $resourceCollection
     * @param Connector     $connector
     * @param array         $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ResourceField $resource,
        Collection $resourceCollection,
        Connector $connector,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection);
        $this->_connector = $connector;
    }

    /**
     * Initialize resources
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('ForeverCompanies\Salesforce\Model\ResourceModel\Field');
    }

    /**
     * @return mixed
     */
    public function getSalesforceFields()
    {
        $salesFields = $this->getSalesforce();
        if ($salesFields) {
            return unserialize($salesFields);
        } else {
            $this->setSalesforceFields($this->salesType);
            return unserialize($this->salesField);
        }
    }

    /**
     * @param $table
     * @param bool|false $update
     * @return $this
     */
    public function loadByTable($table, $update = false)
    {
        $this->load($table, 'type');
        if (!$this->getId() || $update) {
            $this->setType($table);
            $this->saveFields($update);
        } else {
            $this->salesField = unserialize($this->getData('salesforce'));
            $this->mageType = $this->getData('magento');
        }

        return $this;
    }

    /**
     * @param $salesType
     * @return mixed
     */
    public function setSalesforceFields($salesType)
    {
        $this->salesField = $this->_connector->getFields($salesType);
    }

    /**
     * Set Type of field
     *
     * @param $type
     */
    public function setType($type)
    {
        $this->salesType = $type;
        $table = $this->getAllTable();
        if (!empty($table[$type])) {
            $this->mageType = $table[$type];
        }
    }

    /**
     * Map two table of Magento and Salesforce
     *
     * @return array
     */
    public function getAllTable()
    {
        $table = [
            'Account'  => 'customer',
            'Order' => 'order'
        ];
        return $table;
    }

    /**
     * Return option table to select in Admin
     *
     * @return array
     */
    public function changeFields()
    {
        $table = $this->getAllTable();
        $data  = ['' => '--- Select Option ---'];
        foreach ($table as $key => $value) {
            $length = strlen($key);
            $subkey = substr($key, ($length -3), $length);
            if ($subkey == '__c') {
                $data[$key] = substr($key, 0, ($length -3));
            } else {
                $data[$key] = $key;
            }
        }

        return $data;
    }

    /**
     * @param bool|false $update
     * @return $this
     */
    public function saveFields($update = false)
    {
        $this->setSalesforceFields($this->salesType);
        $data = [
            'type'       => $this->salesType,
            'salesforce' => $this->salesField,
            'magento'    => $this->mageType,
            'status'     => 1,
        ];
        if ($this->getId() && $update) {
            $this->addData($data);
        } else {
            $this->setData($data);
        }

        $this->save();

        return $this;
    }

    /**
     * Get Magento Field
     *
     * @return array
     */
    public function getMagentoFields()
    {
        if (isset($this->mageField[$this->mageType]) === false) {
            $this->setMagentoFields($this->mageType);
        }

        return $this->mageField[$this->mageType];
    }

    /**
     * Set field magento to map
     *
     * @param $table
     * @return array
     */
    public function setMagentoFields($table)
    {
        $m_fields = [];
        switch ($table) {
            case 'customer':
                $m_fields = [
                    'entity_id'       => 'ID',
                    'email'           => 'Email',
                    'created_at'      => 'Created At',
                    'update_at'       => 'Updated At',
                    'is_active'       => 'is Active',
                    'created_in'      => 'Created in',
                    'prefix'          => 'Prefix',
                    'firstname'       => 'First name',
                    'middlename'      => 'Middle Name/Initial',
                    'lastname'        => 'Last name',
                    'taxvat'          => 'Tax/VAT Number',
                    'gender'          => 'Gender',
                    'dob'             => 'Date of Birth',
                    'bill_firstname'  => 'Billing First Name',
                    'bill_middlename' => 'Billing Middle Name',
                    'bill_lastname'   => 'Billing Last Name',
                    'bill_company'    => 'Billing Company',
                    'bill_street'     => 'Billing Street',
                    'bill_city'       => 'Billing City',
                    'bill_region'     => 'Billing State/Province',
                    'bill_country_id' => 'Billing Country',
                    'bill_postcode'   => 'Billing Zip/Postal Code',
                    'bill_telephone'  => 'Billing Telephone',
                    'bill_fax'        => 'Billing Fax',
                    'ship_firstname'  => 'Shipping First Name',
                    'ship_middlename' => 'Shipping Middle Name',
                    'ship_lastname'   => 'Shipping Last Name',
                    'ship_company'    => 'Shipping Company',
                    'ship_street'     => 'Shipping Street',
                    'ship_city'       => 'Shipping City',
                    'ship_region'     => 'Shipping State/Province',
                    'ship_country_id' => 'Shipping Country',
                    'ship_postcode'   => 'Shipping Zip/Postal Code',
                    'ship_telephone'  => 'Shipping Telephone',
                    'ship_fax'        => 'Shipping Fax',
                    'vat_id'          => 'VAT number',
                ];
                break;
            case 'order':
                $m_fields = [
                    'entity_id'                => 'ID',
                    'state'                    => 'State',
                    'status'                   => 'Status',
                    'coupon_code'              => 'Coupon Code',
                    'coupon_rule_name'         => 'Coupon Rule Name',
                    'increment_id'             => 'Increment ID',
                    'created_at'               => 'Created At',
                    'company'                  => 'Company',
                    'customer_firstname'       => 'Customer First Name',
                    'customer_middlename'      => 'Customer Middle Name',
                    'customer_email'           => 'Customer Email',
                    'customer_lastname'        => 'Customer Last Name',
                    'bill_firstname'           => 'Billing First Name',
                    'bill_middlename'          => 'Billing Middle Name',
                    'bill_lastname'            => 'Billing Last Name',
                    'bill_company'             => 'Billing Company',
                    'bill_street'              => 'Billing Street',
                    'bill_city'                => 'Billing City',
                    'bill_region'              => 'Billing State/Province',
                    'bill_postcode'            => 'Billing Zip/Postal Code',
                    'bill_telephone'           => 'Billing Telephone',
                    'bill_country_id'          => 'Billing Country',
                    'ship_firstname'           => 'Shipping First Name',
                    'ship_middlename'          => 'Shipping Middle Name',
                    'ship_lastname'            => 'Shipping Last Name',
                    'ship_company'             => 'Shipping Company',
                    'ship_street'              => 'Shipping Street',
                    'ship_city'                => 'Shipping City',
                    'ship_region'              => 'Shipping State/Province',
                    'ship_poscode'             => 'Shipping Zip/Postal Code',
                    'ship_country_id'          => 'Shipping Country',
                    'shipping_amount'          => 'Shipping Amount',
                    'shipping_description'     => 'Shipping Description',
                    'order_currency_code'      => 'Currency Code',
                    'total_item_count'         => 'Total Item Count',
                    'store_currency_code'      => 'Store Currency Code',
                    'shipping_discount_amount' => 'Shipping Discount Amount',
                    'discount_description'     => 'Discount Description',
                    'shipping_method'          => 'Shipping Method',
                    'store_name'               => 'Store Name',
                    'discount_amount'          => 'Discount Amount',
                    'tax_amount'               => 'Tax Amount',
                    'subtotal'                 => 'Sub Total',
                    'grand_total'              => 'Grand Total',
                ];
                break;
            default:
                break;
        }
        $this->mageField[$this->mageType] = $m_fields;
    }
}
