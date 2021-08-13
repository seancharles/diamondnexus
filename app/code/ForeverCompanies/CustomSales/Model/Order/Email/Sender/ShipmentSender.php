<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ForeverCompanies\CustomSales\Model\Order\Email\Sender;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\ShipmentIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\ResourceModel\Order\Shipment as ShipmentResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DataObject;
use ForeverCompanies\CustomSales\Helper\Shipdate;

/**
 * Class ShipmentSender
 */
class ShipmentSender extends \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
{
    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var ShipmentResource
     */
    protected $shipmentResource;

    /**
     * Global configuration storage.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $globalConfig;

    /**
     * @var Renderer
     */
    protected $addressRenderer;

    /**
     * Application Event Dispatcher
     *
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var Shipdate
     */
    protected $shipdateHelper;

    /**
     * @param Template $templateContainer
     * @param ShipmentIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param PaymentHelper $paymentHelper
     * @param ShipmentResource $shipmentResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Template $templateContainer,
        ShipmentIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        ShipmentResource $shipmentResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        ManagerInterface $eventManager,
        Shipdate $shipdateHelper
    ) {
        parent::__construct(
            $templateContainer,
            $identityContainer,
            $senderBuilderFactory,
            $logger,
            $addressRenderer,
            $paymentHelper,
            $shipmentResource,
            $globalConfig,
            $eventManager
        );
        
        $this->shipdateHelper = $shipdateHelper;
    }
    
    /**
     * Sends order shipment email to the customer.
     *
     * Email will be sent immediately in two cases:
     *
     * - if asynchronous email sending is disabled in global settings
     * - if $forceSyncMode parameter is set to TRUE
     *
     * Otherwise, email will be sent later during running of
     * corresponding cron job.
     *
     * @param Shipment $shipment
     * @param bool $forceSyncMode
     * @return bool
     * @throws \Exception
     */
    public function send(Shipment $shipment, $forceSyncMode = false)
    {
        $shipment->setSendEmail($this->identityContainer->isEnabled());

        if (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) {
            $order = $shipment->getOrder();
            $this->identityContainer->setStore($order->getStore());

            $transport = [
                'order' => $order,
                'order_id' => $order->getId(),
                'shipment' => $shipment,
                'shipment_id' => $shipment->getId(),
                'comment' => $shipment->getCustomerNoteNotify() ? $shipment->getCustomerNote() : '',
                'billing' => $order->getBillingAddress(),
                'payment_html' => $this->getPaymentHtml($order),
                'store' => $order->getStore(),
                'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
                'order_data' => [
                    'customer_name' => $order->getCustomerName(),
                    'is_not_virtual' => $order->getIsNotVirtual(),
                    'email_customer_note' => $order->getEmailCustomerNote(),
                    'frontend_status_label' => $order->getFrontendStatusLabel()
                ]
            ];
            
            $transport['dispatch_date'] = null;
            $transport['delivery_date'] = null;
            $transport['tracking_provider'] = null;
            $transport['tracking_number'] = null;
            $transport['tracking_url'] = null;
            
            $deliveryDates = $this->shipdateHelper->getDeliveryDates($order);
            
            if (isset($deliveryDates['dispatch_date']) === true) {
                $transport['dispatch_date'] = $deliveryDates['dispatch_date'];
            }
            
            if (isset($deliveryDates['delivery_date']) === true) {
                $transport['delivery_date'] = $deliveryDates['delivery_date'];
            }
            
            $tracking = $this->shipdateHelper->getTrackingInfo($order);
            
            $transport['tracking_provider'] = $tracking['tracking_provider'];
            $transport['tracking_number'] = $tracking['tracking_number'];
            $transport['tracking_url'] = $tracking['tracking_url'];
            
            $transportObject = new DataObject($transport);

            /**
             * Event argument `transport` is @deprecated. Use `transportObject` instead.
             */
            $this->eventManager->dispatch(
                'email_shipment_set_template_vars_before',
                ['sender' => $this, 'transport' => $transportObject->getData(), 'transportObject' => $transportObject]
            );

            $this->templateContainer->setTemplateVars($transportObject->getData());

            if ($this->checkAndSend($order)) {
                $shipment->setEmailSent(true);
                $this->shipmentResource->saveAttribute($shipment, ['send_email', 'email_sent']);
                return true;
            }
        } else {
            $shipment->setEmailSent(null);
            $this->shipmentResource->saveAttribute($shipment, 'email_sent');
        }

        $this->shipmentResource->saveAttribute($shipment, 'send_email');

        return false;
    }
}
