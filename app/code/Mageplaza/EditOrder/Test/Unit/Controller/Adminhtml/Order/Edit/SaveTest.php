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

namespace Mageplaza\EditOrder\Test\Unit\Controller\Adminhtml\Order\Edit;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\PaymentFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order as ResourceOrder;
use Magento\Sales\Model\ResourceModel\Order\Payment as PaymentResource;
use Mageplaza\EditOrder\Controller\Adminhtml\Order\Edit\Save;
use Mageplaza\EditOrder\Helper\Data as HelperData;
use Mageplaza\EditOrder\Model\LogsFactory;
use Mageplaza\EditOrder\Model\Order\Edit as EditModel;
use Mageplaza\EditOrder\Model\Order\Total as OrderTotal;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class SaveTest
 * @package Mageplaza\EditOrder\Test\Unit\Controller\Adminhtml\Order\Edit
 */

//TODO Fix this
class SaveTest extends TestCase
{
    /**
     * @var Context|PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var RequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var ResourceOrder|PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderResourceModel;

    /**
     * @var JsonFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJsonFactory;

    /**
     * @var PaymentFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentFactory;

    /**
     * @var PaymentResource|PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentResource;

    /**
     * @var LayoutFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultLayoutFactory;

    /**
     * @var OrderFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactory;

    /**
     * @var LogsFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $logsFactory;

    /**
     * @var Session|PHPUnit_Framework_MockObject_MockObject
     */
    protected $authSession;

    /**
     * @var RemoteAddress|PHPUnit_Framework_MockObject_MockObject
     */
    protected $remoteAddress;

    /**
     * @var QuoteFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteFactory;

    /**
     * @var HelperData|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperData;

    /**
     * @var OrderTotal|PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderTotal;

    /**
     * @var EditModel|PHPUnit_Framework_MockObject_MockObject
     */
    protected $editModel;

    /**
     * @var QuoteSession|PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteSession;

    /**
     * @var Save|PHPUnit_Framework_MockObject_MockObject
     */
    private $object;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $this->orderResourceModel = $this->getMockBuilder(ResourceOrder::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultJsonFactory = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->paymentResource = $this->getMockBuilder(PaymentResource::class)
            ->disableOriginalConstructor()->getMock();
        $this->paymentFactory = $this->getMockBuilder(PaymentFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultLayoutFactory = $this->getMockBuilder(LayoutFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->orderFactory = $this->getMockBuilder(OrderFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->logsFactory = $this->getMockBuilder(LogsFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->authSession = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();
        $this->remoteAddress = $this->getMockBuilder(RemoteAddress::class)
            ->disableOriginalConstructor()->getMock();
        $this->quoteFactory = $this->getMockBuilder(QuoteFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->_helperData = $this->getMockBuilder(HelperData::class)
            ->disableOriginalConstructor()->getMock();
        $this->orderTotal = $this->getMockBuilder(OrderTotal::class)->disableOriginalConstructor()->getMock();
        $this->editModel = $this->getMockBuilder(EditModel::class)->disableOriginalConstructor()->getMock();
        $this->quoteSession = $this->getMockBuilder(QuoteSession::class)
            ->disableOriginalConstructor()->getMock();
        $this->_request = $this->getMockBuilder(RequestInterface::class)->getMock();
        $this->context->method('getRequest')->willReturn($this->_request);

        $this->object = new Save(
            $this->context,
            $this->resultJsonFactory,
            $this->paymentResource,
            $this->paymentFactory,
            $this->resultLayoutFactory,
            $this->orderFactory,
            $this->logsFactory,
            $this->authSession,
            $this->remoteAddress,
            $this->quoteFactory,
            $this->_helperData,
            $this->orderTotal,
            $this->editModel,
            $this->quoteSession
        );
    }

    /**
     * @inheritDoc
     */
    public function testAdminInstance()
    {
        $this->assertInstanceOf(Save::class, $this->object);
    }

    /**
     * unit test ApplyCoupon function()
     */
    public function testApplyCoupon()
    {
        $data = [
            'mp_coupon_code' => 'discount'
        ];

        $order = $this->getMockBuilder(Order::class)->setMethods([
            'save',
            'setCouponCode'
        ])->disableOriginalConstructor()->getMock();

        $order->method('save');

        $this->assertEquals(['success' => true], $this->object->applyCoupon($data, $order));
    }
}
