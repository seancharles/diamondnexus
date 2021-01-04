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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Block\Template;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Sales\Block\Adminhtml\Order\Create\Coupons;
use Magento\Sales\Block\Adminhtml\Order\Create\Items as ItemForm;
use Magento\Sales\Block\Adminhtml\Order\Create\Search;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Sales\Model\OrderFactory;
use Mageplaza\EditOrder\Block\Adminhtml\Order\Edit\Items\Grid;
use Mageplaza\EditOrder\Block\Adminhtml\Order\Edit\Items\Search\Grid as SearchGrid;
use Mageplaza\EditOrder\Block\Adminhtml\Order\Edit\OrderItems;

/**
 * Class Form
 * @package Mageplaza\EditOrder\Controller\Adminhtml\Items
 */
class Form extends Action
{
    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var QuoteSession
     */
    protected $quoteSession;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var Create
     */
    protected $orderCreate;

    /**
     * Form constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param QuoteSession $quoteSession
     * @param OrderFactory $orderFactory
     * @param Create $orderCreate
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        QuoteSession $quoteSession,
        OrderFactory $orderFactory,
        Create $orderCreate
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->quoteSession = $quoteSession;
        $this->orderFactory = $orderFactory;
        $this->orderCreate = $orderCreate;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $resultLayout = $this->resultLayoutFactory->create();

        $this->quoteSession->clearStorage();
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderFactory->create()->load($orderId);
        $this->orderCreate->initFromOrder($order);

        $ignoreKeys = [
            'item_id', 'created_at', 'updated_at', 'parent_item_id'
        ];

        $quote      = $this->orderCreate->getQuote();
        $quoteItems = $quote->getAllVisibleItems();
        $orderItems = $order->getAllVisibleItems();
        foreach ($orderItems as $orderItem) {
            foreach ($quoteItems as $item) {
                if ($item->getProductId() === $orderItem->getProductId()) {
                    foreach ($orderItem->getData() as $key => $data) {
                        if (!in_array($key, $ignoreKeys, true)) {
                            $item->setData($key, $orderItem->getData($key));
                        }
                    }
                    $item->save();
                }
            }
        }

        $coupon = $resultLayout->getLayout()
            ->createBlock(Coupons::class)
            ->setTemplate('Mageplaza_EditOrder::order/edit/items/coupons/form.phtml');

        $grid = $resultLayout->getLayout()
            ->createBlock(Grid::class)
            ->setTemplate('Mageplaza_EditOrder::order/edit/items/grid.phtml')
            ->setChild('coupons', $coupon);

        $items = $resultLayout->getLayout()
            ->createBlock(ItemForm::class)
            ->setTemplate('Mageplaza_EditOrder::order/edit/items/items.phtml')
            ->setChild('items_grid', $grid);

        $searchGrid = $resultLayout->getLayout()
            ->createBlock(SearchGrid::class);

        $search = $resultLayout->getLayout()
            ->createBlock(Search::class)
            ->setTemplate('Magento_Sales::order/create/abstract.phtml')
            ->setChild('search_grid', $searchGrid);

        $formHtml = $resultLayout->getLayout()
            ->createBlock(OrderItems::class)
            ->setTemplate('Mageplaza_EditOrder::order/edit/items/form.phtml')
            ->setChild('order_items', $items)
            ->setChild('search', $search)
            ->toHtml();

        $jsHtml = $resultLayout->getLayout()
            ->createBlock(Template::class)
            ->setTemplate('Magento_Sales::order/create/js.phtml')
            ->toHtml();

        $result->setData(['success' => $formHtml . $jsHtml]);

        return $result;
    }
}
