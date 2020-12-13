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

namespace Mageplaza\EditOrder\Test\Unit\Model\Order;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Customer;
use Magento\Directory\Model\RegionFactory;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Block\Adminhtml\Order\View\Info;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order as ResourceOrder;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Mageplaza\EditOrder\Model\Order\Edit;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class SaveTest
 * @package Mageplaza\EditOrder\Test\Unit\Controller\Adminhtml\Order\Edit
 */

//TODO Fix this
class EditTest extends TestCase
{
    /**
     * @var Info|PHPUnit_Framework_MockObject_MockObject
     */
    protected $info;

    /**
     * @var RegionFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $regionFactory;

    /**
     * @var OrderAddressInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderAddressInterface;

    /**
     * @var ResourceOrder|PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderResourceModel;

    /**
     * @var CustomerRepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customerRepoInterface;

    /**
     * @var AccountManagement|PHPUnit_Framework_MockObject_MockObject
     */
    protected $accountManagement;

    /**
     * @var CollectionFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderCollectionFactory;

    /**
     * @var CustomerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerFactory;

    /**
     * @var Customer|PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerModel;

    /**
     * @var OrderFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactory;

    /**
     * @var Edit|PHPUnit_Framework_MockObject_MockObject
     */
    private $object;

    protected function setUp()
    {
        $this->info = $this->getMockBuilder(Info::class)->disableOriginalConstructor()->getMock();
        $this->regionFactory = $this->getMockBuilder(RegionFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->orderAddressInterface = $this->getMockBuilder(OrderAddressInterface::class)->getMock();
        $this->_orderCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->orderResourceModel = $this->getMockBuilder(ResourceOrder::class)
            ->disableOriginalConstructor()->getMock();
        $this->_customerRepoInterface = $this->getMockBuilder(CustomerRepositoryInterface::class)->getMock();
        $this->accountManagement = $this->getMockBuilder(AccountManagement::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerFactory = $this->getMockBuilder(CustomerInterface::class)->getMock();
        $this->customerModel = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()->getMock();
        $this->orderFactory = $this->getMockBuilder(OrderFactory::class)
            ->disableOriginalConstructor()->getMock();

        $this->object = new Edit(
            $this->info,
            $this->regionFactory,
            $this->orderAddressInterface,
            $this->_orderCollectionFactory,
            $this->orderResourceModel,
            $this->_customerRepoInterface,
            $this->accountManagement,
            $this->customerFactory,
            $this->customerModel,
            $this->orderFactory
        );
    }

    /**
     * @inheritDoc
     */
    public function testAdminInstance()
    {
        $this->assertInstanceOf(Edit::class, $this->object);
    }

    /**
     * unit test SetInfoData function()
     */
    public function testSetInfoData()
    {
        $data = [
            'increment' => '00000001',
            'date'      => '12/09/1994',
            'status'    => 'pending'
        ];

        $order = $this->getMockBuilder(Order::class)->setMethods([
            'getIncrementId',
            'setIncrementId',
            'setCreatedAt',
            'setStatus'
        ])->disableOriginalConstructor()->getMock();

        $orderCollection = $this->getMockBuilder(Collection::class)->disableOriginalConstructor()->getMock();
        $orderCollection->method('addFieldToFilter')->willReturnSelf();
        $orderCollection->method('getSize')->willReturn(4);
        $this->_orderCollectionFactory->method('create')->willReturn($orderCollection);
        $order->method('getIncrementId')->willReturn($data['increment']);
        $order->method('setIncrementId')->with($data['increment'])->willReturnSelf();
        $order->method('setCreatedAt')->with($data['date'])->willReturnSelf();
        $order->method('setStatus')->with($data['status'])->willReturnSelf();

        $this->orderResourceModel->method('save')->with($order)->willReturnSelf();

        $this->assertEquals(['success' => true], $this->object->setInfoData($order, $data));
    }
}
