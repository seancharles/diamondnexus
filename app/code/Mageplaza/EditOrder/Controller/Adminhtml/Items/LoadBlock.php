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

namespace Mageplaza\EditOrder\Controller\Adminhtml\Items;

use Magento\Backend\App\Action\Context;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Catalog\Helper\Product;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Escaper;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Controller\Adminhtml\Order\Create;

/**
 * Class LoadBlock
 * @package Mageplaza\EditOrder\Block\Adminhtml\Items
 */
class LoadBlock extends Create
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * LoadBlock constructor.
     *
     * @param Action\Context $context
     * @param Product $productHelper
     * @param Escaper $escaper
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param RawFactory $resultRawFactory
     */
    public function __construct(
        Context $context,
        Product $productHelper,
        Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        RawFactory $resultRawFactory
    ) {
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct(
            $context,
            $productHelper,
            $escaper,
            $resultPageFactory,
            $resultForwardFactory
        );
    }

    /**
     * Loading page block
     *
     * @return ResponseInterface|Raw|Redirect|ResultInterface
     */
    public function execute()
    {
        $request = $this->getRequest();
        try {
            $this->_initSession()->_processData();
        } catch (Exception $e) {
            $this->_reloadQuote();
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }

        $asJson = $request->getParam('json');

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        if ($asJson) {
            $resultPage->addHandle('sales_order_create_load_block_json');
        } else {
            $resultPage->addHandle('sales_order_create_load_block_plain');
        }

        $resultPage->addHandle('mpeditorder_load_block_items');
        $resultPage->addHandle('mpeditorder_load_block_message');

        $result = $resultPage->getLayout()->renderElement('content');
        if ($request->getParam('as_js_varname')) {
            $this->_objectManager->get(Session::class)->setUpdateResult($result);

            return $this->resultRedirectFactory->create()->setPath('sales/order_edit/showUpdateResult');
        }

        return $this->resultRawFactory->create()->setContents($result);
    }
}
