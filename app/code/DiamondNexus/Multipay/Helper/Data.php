<?php

declare(strict_types=1);

namespace DiamondNexus\Multipay\Helper;

use Braintree\Result\Error;
use Braintree\Result\Successful;
use DiamondNexus\Multipay\Model\Constant;
use League\ISO3166\ISO3166;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\ValidatorException;
use Magento\Sales\Api\Data\OrderInterface;

class Data extends AbstractHelper
{
    /**
     * @var BraintreeAdapter
     */
    protected $brainTreeAdapter;

    /**
     * @var ISO3166
     */
    protected $iso3166;

    /**
     * Data constructor.
     * @param Context $context
     * @param BraintreeAdapter $braintreeAdapter
     * @param ISO3166 $iso3166
     */
    public function __construct(
        Context $context,
        BraintreeAdapter $braintreeAdapter,
        ISO3166 $iso3166
    ) {
        parent::__construct($context);
        $this->brainTreeAdapter = $braintreeAdapter;
        $this->iso3166 = $iso3166;
    }

    /**
     * @param OrderInterface $order
     * @return Error|Successful
     * @throws ValidatorException
     */
    public function sendToBraintree(OrderInterface $order)
    {
        $shippingAddress = $order->getShippingAddress();
        $additionalInformation = $order->getPayment()->getAdditionalInformation();
        $billingAddress = $order->getBillingAddress();
        $amount = $additionalInformation[Constant::OPTION_PARTIAL_DATA];
        if ((int)$additionalInformation[Constant::OPTION_TOTAL_DATA] == 1) {
            $amount = $additionalInformation[Constant::AMOUNT_DUE_DATA];
        } else {
            if ($additionalInformation[Constant::AMOUNT_DUE_DATA] < $amount) {
                throw new ValidatorException(__('You can\'t pay more than order total price'));
            }
        }
        $items = [];
        foreach ($order->getItems() as $orderItem) {
            $items[] = [
                'name' => substr($orderItem->getName(), 0, 35),
                'kind' => 'debit',
                'quantity' => $orderItem->getQtyOrdered(),
                'unitAmount' => $orderItem->getPrice(),
                'unitOfMeasure' => $orderItem->getProductType(),
                'totalAmount' => $orderItem->getRowTotal(),
                'taxAmount' => $orderItem->getTaxAmount(),
                'discountAmount' => $orderItem->getDiscountAmount(),
                'productCode' => substr($orderItem->getSku(), 0, 12),
                'commodityCode' => substr($orderItem->getSku(), 0, 12)
            ];
        }
        $attributes = [
            'customer' =>
                [
                    'firstName' => $order->getCustomerFirstname(),
                    'lastName' => $order->getCustomerLastname(),
                    'company' => $shippingAddress->getCompany() ?? '',
                    'phone' => $shippingAddress->getTelephone() ?? '',
                    'email' => $order->getCustomerEmail()
                ],
            'amount' => $amount,
            'creditCard' => [
                'cvv' => $additionalInformation[Constant::CVV_NUMBER_DATA],
                'expirationMonth' => $additionalInformation[Constant::EXP_MONTH_DATA],
                'expirationYear' => $additionalInformation[Constant::EXP_YEAR_DATA],
                'number' => $additionalInformation[Constant::CC_NUMBER_DATA]
            ],
            'orderId' => $order->getId(),
            'channel' => 'Magento2GeneBT',
            'options' =>
                [
                    'skipAdvancedFraudChecking' => false,
                    'storeInVaultOnSuccess' => true
                ],
            'transactionSource' => 'moto',
            'customFields' => [],
            'billing' => [
                'firstName' => $billingAddress->getFirstname(),
                'lastName' => $billingAddress->getLastname(),
                'company' => $billingAddress->getCompany(),
                'streetAddress' => $billingAddress->getStreet()[0],
                'extendedAddress' => $billingAddress->getStreet()[1] ?? '',
                'locality' => $billingAddress->getCity(),
                'region' => $billingAddress->getRegionCode(),
                'postalCode' => $billingAddress->getPostcode(),
                'countryCodeAlpha2' => $billingAddress->getCountryId()
            ],
            'shipping' =>
                [
                    'firstName' => $shippingAddress->getFirstname(),
                    'lastName' => $shippingAddress->getLastname(),
                    'company' => $shippingAddress->getCompany(),
                    'streetAddress' => $shippingAddress->getStreet()[0],
                    'extendedAddress' => '',
                    'locality' => $shippingAddress->getCity(),
                    'region' => $shippingAddress->getRegionCode(),
                    'postalCode' => $shippingAddress->getPostcode(),
                    'countryCodeAlpha2' => $shippingAddress->getCountryId(),
                    'countryCodeAlpha3' => $this->iso3166->alpha2($shippingAddress->getCountryId())['alpha3']
                ],
            'purchaseOrderNumber' => $order->getId(),
            'taxAmount' => $order->getTaxAmount(),
            'discountAmount' => $order->getDiscountAmount(),
            'lineItems' => $items,
            'shippingAmount' => $order->getShippingAmount(),
            'shipsFromPostalCode' => null
        ];
        return $this->brainTreeAdapter->sale($attributes);
    }
}
