<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace DiamondNexus\Multipay\Observer;

use DiamondNexus\Multipay\Model\Constant;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class PreparePaymentMethod
 * @package DiamondNexus\Multipay\Observer
 */
class PreparePaymentMethod implements ObserverInterface
{
    protected $logger;

    public function __construct(\Psr\Log\LoggerInterface $loggerInterface)
    {
        $this->logger = $loggerInterface;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $paymentCode = $observer->getMethodInstance()->getCode();
        $result = $observer->getResult();
        $result->setData('is_available', $paymentCode === Constant::MULTIPAY_METHOD);

        return $this;
    }
}
