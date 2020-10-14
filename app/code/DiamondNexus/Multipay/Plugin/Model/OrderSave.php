<?php

namespace DiamondNexus\Multipay\Plugin\Model;

use DiamondNexus\Multipay\Logger\Logger;
use DiamondNexus\Multipay\Model\Constant;
use DiamondNexus\Multipay\Model\ResourceModel\Transaction;
use DiamondNexus\Multipay\Model\TransactionFactory;
use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class OrderSave
{
    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var Transaction
     */
    protected $resource;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * OrderSave constructor.
     * @param Transaction $resource
     */
    public function __construct(
        Transaction $resource
    )
    {
        $this->resource = $resource;
    }

    /**
     * Save order tax
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws Exception
     */
    public function afterSave(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    )
    {
        $payment = $order->getPayment();
        $methodInstance = $payment->getMethod();
        $information = $payment->getAdditionalInformation();
        $method = $information[Constant::PAYMENT_METHOD_DATA];

        if ($methodInstance === Constant::MULTIPAY_METHOD) {
            switch ($method) {
                case Constant::MULTIPAY_CREDIT_METHOD:
                case Constant::MULTIPAY_CASH_METHOD:
                case Constant::MULTIPAY_AFFIRM_OFFLINE_METHOD:
                    $id = $order->getId();
                    $this->saveMultipayTransaction($id, $information);
                    break;
                case Constant::MULTIPAY_QUOTE_METHOD:

                    break;
            }
        }
        /** TODO: $attributes -- now only test*/
        $attributes = [
            'customer' =>
                [
                    'firstName' => 'Test name',
                    'lastName' => 'Test lastname',
                    'company' => '',
                    'phone' => '88005553535',
                    'email' => 'hellothere@general.knb'
                ],
            'amount' => '9000.00',
            'paymentMethodNonce' => 'tokencc_i_dont_know_for_what',
            'orderId' => '32167',
            'channel' => 'Magento2GeneBT',
            'options' =>
                [
                    'skipAdvancedFraudChecking' => false,
                    'storeInVaultOnSuccess' => true
                ],
            'transactionSource' => 'moto',
            'customFields' => [],
            'billing' => [
                'firstName' => 'Testname',
                'lastName' => 'Testlastname',
                'company' => '',
                'streetAddress' => 'Backer street',
                'extendedAddress' => '',
                'locality' => 'Dark castle',
                'region' => 'CH',
                'postalCode' => '12345',
                'countryCodeAlpha2' => 'US'
            ],
            'shipping' =>
                [
                    'firstName' => 'Test shipping firstname',
                    'lastName' => 'Test shipping lastname',
                    'company' => '',
                    'streetAddress' => '1020 Garrison Avenue',
                    'extendedAddress' => 'Unit 2002',
                    'locality' => 'Fort Smith',
                    'region' => 'AR',
                    'postalCode' => '72901',
                    'countryCodeAlpha2' => 'US',
                    'countryCodeAlpha3' => 'USA'
                ],
            'purchaseOrderNumber' => '32167',
            'taxAmount' => '0',
            'discountAmount' => '0',
            'lineItems' =>
                [
                    0 =>
                        [
                            'name' => 'Round Cut Studs Screw Back Basket',
                            'kind' => 'debit',
                            'quantity' => '1',
                            'unitAmount' => '220',
                            'unitOfMeasure' => 'simple',
                            'totalAmount' => '220',
                            'taxAmount' => '0',
                            'discountAmount' => '0',
                            'productCode' => 'LEXXCL0005XR',
                            'commodityCode' => 'LEXXCL0005XR'
                        ],
                    1 =>
                        [
                            'name' => 'Round Cut Studs Screw Back Basket',
                            'kind' => 'debit',
                            'quantity' => '1',
                            'unitAmount' => '260',
                            'unitOfMeasure' => 'simple',
                            'totalAmount' => '260',
                            'taxAmount' => '0',
                            'discountAmount' => '0',
                            'productCode' => 'LEXXCL0005XR',
                            'commodityCode' => 'LEXXCL0005XR'
                        ],
                    2 =>
                        [
                            'name' => 'Round Cut Studs Screw Back Basket',
                            'kind' => 'debit',
                            'quantity' => '1',
                            'unitAmount' => '260',
                            'unitOfMeasure' => 'simple',
                            'totalAmount' => '260',
                            'taxAmount' => '0',
                            'discountAmount' => '0',
                            'productCode' => 'LEXXCL0005XR',
                            'commodityCode' => 'LEXXCL0005XR'
                        ]],
            'shippingAmount' => '15',
            'shipsFromPostalCode' => null
        ];
        \Braintree\Transaction::sale($attributes);
        return $order;
    }

    /**
     * @param $id
     * @param $additionalInformation
     * @throws LocalizedException
     */
    protected function saveMultipayTransaction($id, $additionalInformation)
    {
        $otherTransactions = $this->resource->getAllTransactionsByOrderId($id);
        if ($otherTransactions !== null && count($otherTransactions) > 0) {
            return;
        }
        $this->resource->createNewTransaction($id, $additionalInformation);
    }
}
