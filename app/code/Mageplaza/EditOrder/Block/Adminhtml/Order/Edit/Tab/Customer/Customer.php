<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_EditOrder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\EditOrder\Block\Adminhtml\Order\Edit\Tab\Customer;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\Registry;

/**
 * Class Customer
 * @package Mageplaza\EditOrder\Block\Adminhtml\Order\Edit\Tab\Customer
 */
class Customer extends Extended
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Customer constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param Registry $registry
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Registry $registry,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('mpeditorder-customer-grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->collectionFactory->create()->setOrder('entity_id', 'asc');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('customer_id', [
            'type'             => 'radio',
            'html_name'        => 'customer_id',
            'required'         => true,
            'align'            => 'center',
            'index'            => 'entity_id',
            'header_css_class' => 'col-select',
            'column_css_class' => 'col-select'
        ]);

        $this->addColumn('entity_id', [
            'header'   => __('ID'),
            'sortable' => true,
            'index'    => 'entity_id'
        ]);

        $this->addColumn('firstname', [
            'header'   => __('First Name'),
            'index'    => 'firstname',
            'type'     => 'text',
            'sortable' => true,
        ]);

        $this->addColumn('middlename', [
            'header'   => __('Middle Name'),
            'index'    => 'middlename',
            'type'     => 'text',
            'sortable' => true,
        ]);

        $this->addColumn('lastname', [
            'header'   => __('Last Name'),
            'index'    => 'lastname',
            'type'     => 'text',
            'sortable' => true,
        ]);

        $this->addColumn('email', [
            'header'   => __('Email'),
            'index'    => 'email',
            'type'     => 'text',
            'sortable' => true,
        ]);

        $this->addColumn('group_id', [
            'header'           => __('Customer Group Id'),
            'index'            => 'group_id',
            'type'             => 'text',
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
            'sortable'         => true,
        ]);

        $this->addColumn('website_id', [
            'header'           => __('Website Id'),
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
            'index'            => 'website_id'
        ]);

        $this->addColumn('prefix', [
            'header'           => __('Prefix'),
            'sortable'         => true,
            'index'            => 'prefix',
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display'
        ]);

        $this->addColumn('suffix', [
            'header'           => __('Suffix'),
            'sortable'         => true,
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
            'index'            => 'suffix'
        ]);

        $this->addColumn('dob', [
            'header'           => __('Dob'),
            'sortable'         => true,
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
            'index'            => 'dob'
        ]);

        $this->addColumn('taxvat', [
            'header'           => __('Tax Vat'),
            'sortable'         => true,
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
            'index'            => 'taxvat'
        ]);

        $this->addColumn('gender', [
            'header'           => __('Gender'),
            'sortable'         => true,
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
            'index'            => 'gender'
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/customer', ['_current' => true]);
    }
}
