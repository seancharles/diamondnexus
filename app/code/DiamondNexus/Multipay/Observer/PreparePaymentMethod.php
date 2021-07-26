<?php

namespace DiamondNexus\Multipay\Observer;

use DiamondNexus\Multipay\Model\Constant;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

/**
 * Class PreparePaymentMethod
 * @package DiamondNexus\Multipay\Observer
 */
class PreparePaymentMethod implements ObserverInterface
{
    protected $logger;

    public function __construct(LoggerInterface $loggerInterface)
    {
        $this->logger = $loggerInterface;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $code = $observer->getData('method_instance')->getCode();
        $result = $observer->getData('result');
        $result->setData('is_available', $code === Constant::MULTIPAY_METHOD);

        return $this;
    }
}
