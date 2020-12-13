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

namespace Mageplaza\EditOrder\Block\Adminhtml\Logs;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Payment\Helper\Data as PaymentData;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\User\Model\UserFactory;
use Mageplaza\EditOrder\Helper\Data as HelperData;
use Mageplaza\EditOrder\Model\Logs;
use Mageplaza\EditOrder\Model\LogsFactory;

/**
 * Class View
 * @package Mageplaza\EditOrder\Block\Adminhtml\Logs
 */
class View extends Template
{
    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var LogsFactory
     */
    protected $logsFactory;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var AddressConfig
     */
    protected $addressConfig;

    /**
     * @var PaymentData
     */
    protected $_paymentData;

    /**
     * @var Data
     */
    protected $priceHelper;

    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * View constructor.
     *
     * @param Context $context
     * @param OrderFactory $orderFactory
     * @param LogsFactory $logsFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param AddressConfig $addressConfig
     * @param PaymentData $paymentData
     * @param QuoteFactory $quoteFactory
     * @param Data $priceHelper
     * @param UserFactory $userFactory
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param Config $eavConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        OrderFactory $orderFactory,
        LogsFactory $logsFactory,
        GroupRepositoryInterface $groupRepository,
        AddressConfig $addressConfig,
        PaymentData $paymentData,
        QuoteFactory $quoteFactory,
        Data $priceHelper,
        UserFactory $userFactory,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        Config $eavConfig,
        array $data = []
    ) {
        $this->orderFactory = $orderFactory;
        $this->logsFactory = $logsFactory;
        $this->groupRepository = $groupRepository;
        $this->addressConfig = $addressConfig;
        $this->_paymentData = $paymentData;
        $this->quoteFactory = $quoteFactory;
        $this->priceHelper = $priceHelper;
        $this->userFactory = $userFactory;
        $this->productRepository = $productRepository;
        $this->priceCurrency = $priceCurrency;
        $this->eavConfig = $eavConfig;

        parent::__construct($context, $data);
    }

    /**
     * @param string $value
     *
     * @return bool|string
     */
    public function getGenderText($value)
    {
        if ($value) {
            try {
                $attribute = $this->eavConfig->getAttribute('customer', 'gender');

                return $attribute->getSource()->getOptionText($value);
            } catch (LocalizedException $e) {
                return '';
            }
        }

        return '';
    }

    /**
     * @return Logs
     */
    public function getLog()
    {
        $logId = $this->getRequest()->getParam('id');

        return $this->logsFactory->create()->load($logId);
    }

    /**
     * @param int $editorId
     *
     * @return string
     */
    public function getAdminUserUrl($editorId)
    {
        $user = $this->userFactory->create()->load($editorId);

        return $this->getUrl(
            'adminhtml/user/edit',
            [
                'user_id' => $user->getId()
            ]
        );
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        $orderId = $this->getLog()->getOrderId();

        return $this->orderFactory->create()->load($orderId);
    }

    /**
     * @return string
     */
    public function getOrderUrl()
    {
        return $this->getUrl(
            'sales/order/view',
            [
                'order_id' => $this->getLog()->getOrderId(),
                'form_key' => $this->getFormKey()
            ]
        );
    }

    /**
     * @return array
     */
    public function getOldOrderData()
    {
        return HelperData::jsonDecode($this->getLog()->getOldData());
    }

    /**
     * @return array
     */
    public function getNewOrderData()
    {
        return HelperData::jsonDecode($this->getLog()->getNewData());
    }

    /**
     * @param array $data
     *
     * @return string|null
     */
    public function getFormattedAddress($data)
    {
        $formatType = $this->addressConfig->getFormatByCode('html');
        if (!$formatType || !$formatType->getRenderer()) {
            return null;
        }

        return $formatType->getRenderer()->renderArray($data);
    }

    /**
     * @param float $price
     *
     * @return float|string
     */
    public function getFormatPrice($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }

    /**
     * @param string key
     *
     * @return bool
     */
    public function checkUpdated($key)
    {
        $oldData = $this->getOldOrderData();
        $newData = $this->getNewOrderData();

        if (isset($newData['order'][$key])) {
            return $newData['order'][$key] !== $oldData['order'][$key];
        }

        return false;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getOldData($key)
    {
        $oldData = $this->getOldOrderData();

        return $oldData['order'][$key];
    }

    /**
     * @return bool
     */
    public function checkUpdateShippingMethod()
    {
        $oldData = $this->getOldOrderData();
        $newData = $this->getNewOrderData();

        if (isset($newData['order']['shipping_method'])) {
            if (!isset($oldData['order']['shipping_method'])) {
                return true;
            }
            if ($newData['order']['shipping_method'] !== $oldData['order']['shipping_method']) {
                return true;
            }
        }

        if (isset($newData['method_detail'])) {
            $method = $newData['order']['shipping_method'];
            foreach ($newData['method_detail'][$method] as $shipKey => $shipVal) {
                if ($newData['method_detail'][$method][$shipKey] !== $oldData['method_detail'][$method][$shipKey]) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param int $proId
     *
     * @return array|ProductInterface
     */
    public function getProductById($proId)
    {
        try {
            return $this->productRepository->getById($proId);
        } catch (NoSuchEntityException $e) {
            return [];
        }
    }

    /**
     * @param float $value
     *
     * @return float
     */
    public function formatPrice($value)
    {
        try {
            return $this->priceCurrency->format(
                $value,
                true,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $this->_storeManager->getStore()
            );
        } catch (NoSuchEntityException $e) {
            return $value;
        }
    }
}
