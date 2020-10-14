<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Block\Adminhtml\OrderEdit\Tab;

use ForeverCompanies\Salesforce\Model\Connector;

class View extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Template
     *
     * @var string
     */
    protected $template = 'tab/view/salesforce_order_info.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;


    protected $logFactory;

    /**
     * View constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \ForeverCompanies\Salesforce\Model\ReportFactory $logFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \ForeverCompanies\Salesforce\Model\ReportFactory $logFactory,
        array $data = []
    ) {
        $this->logFactory = $logFactory;
        $this->coreRegistry  = $registry;
        parent::_construct($context, $data);
    }

    /**
     * Retrieve oredr model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * Retrieve order increment id
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Salesforce Integration');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Sync History');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get customer creation date
     *
     * @return string
     */
    public function getCreatedAt()
    {
        $log = $this->logFactory->create()->getCollection()
            ->addFieldToFilter('salesforce_table','Order')
            ->addFieldToFilter('magento_id', $this->getOrderIncrementId())
            ->getFirstItem();
        return $log->getData('datetime') ?
            $this->formatDate($log->getData('datetime'),
                \IntlDateFormatter::MEDIUM, true) : 'Never';
    }

    /**
     * Get customer creation date
     *
     * @return string
     */
    public function getLastUpdatedAt()
    {
        $log = $this->logFactory->create()->getCollection()
            ->addFieldToFilter('salesforce_table','Order')
            ->addFieldToFilter('magento_id', $this->getOrderIncrementId())
            ->getLastItem();
        return $log->getData('datetime') ?
            $this->formatDate($log->getData('datetime'),
                \IntlDateFormatter::MEDIUM, true) : 'Never';

    }

    /**
     * @return string
     */
    public function getSalesforceId()
    {
        $log = $this->logFactory->create()->getCollection()
            ->addFieldToFilter('salesforce_table', 'Order')
            ->addFieldToFilter('magento_id', $this->getOrderIncrementId());
        foreach ($log as $v) {
            if ($v->getData('record_id')){
                return $v->getData('record_id');
            }
        }
        return '';
    }

    public function getSalesforceUrl()
    {
        $url = $this->_scopeConfig->getValue(Connector::XML_PATH_SALESFORCE_INSTANCE_URL)
            .'/'. $this->getSalesforceId();
        return $url;
    }

    public function getSyncLog()
    {
        $log = $this->logFactory->create()->getCollection()
            ->addFieldToFilter('salesforce_table', 'Order')
            ->addFieldToFilter('magento_id', $this->getOrderIncrementId())
            ->addOrder('datetime', 'DESC')
            ->setPageSize(10)
            ->setCurPage(1);
        return $log;
    }

    public function getSyncButtonUrl()
    {
        return $this->getUrl('salesforce/sync/order', [
            'id' => $this->getOrderIncrementId()
        ]);
    }


}
