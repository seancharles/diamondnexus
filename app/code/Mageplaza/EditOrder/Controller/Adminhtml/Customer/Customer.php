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

namespace Mageplaza\EditOrder\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;

/**
 * Class Customer
 * @package Mageplaza\EditOrder\Controller\Adminhtml\Customer
 */
class Customer extends Action
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * Customer constructor.
     *
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        LayoutFactory $layoutFactory
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        parent::__construct($context);
    }

    /**
     * Grid Action
     *
     * @return Raw
     */
    public function execute()
    {
        $resultRaw = $this->resultRawFactory->create();

        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                \Mageplaza\EditOrder\Block\Adminhtml\Order\Edit\Tab\Customer\Customer::class,
                'mpeditorder.grid.customer'
            )->toHtml()
        );
    }
}
