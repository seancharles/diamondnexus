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

namespace Mageplaza\EditOrder\Controller\Adminhtml\Logs;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Mageplaza\EditOrder\Model\Logs;
use Mageplaza\EditOrder\Model\LogsFactory;

/**
 * Class View
 * @package Mageplaza\EditOrder\Controller\Adminhtml\Logs
 */
class View extends Action
{
    const ADMIN_RESOURCE = 'Mageplaza_EditOrder::logs';

    /**
     * @var LogsFactory
     */
    protected $logsFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * View constructor.
     *
     * @param Action\Context $context
     * @param LogsFactory $logsFactory
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        LogsFactory $logsFactory,
        Registry $coreRegistry
    ) {
        $this->logsFactory = $logsFactory;
        $this->_coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $logId = $this->getRequest()->getParam('id');
        $log = $this->_initLog();

        if (!$log->getId() && $logId) {
            $this->messageManager->addErrorMessage(__('This request no longer exists.'));
            $this->_redirect('*/*/');

            return;
        }

        $this->_view->loadLayout();
        $this->_setActiveMenu('Mageplaza_EditOrder::logs');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Log #%1', $logId));

        $this->_view->renderLayout();
    }

    /**
     * @return bool|Logs
     */
    protected function _initLog()
    {
        $logId = (int) $this->getRequest()->getParam('id');
        $log = $this->logsFactory->create();

        if ($logId) {
            $log->load($logId);
            if (!$log->getId()) {
                $this->messageManager->addErrorMessage(__('This log no longer exists.'));

                return false;
            }
        }

        if (!$this->_coreRegistry->registry('current_log')) {
            $this->_coreRegistry->register('current_log', $log);
        }

        return $log;
    }
}
