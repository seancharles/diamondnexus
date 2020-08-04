<?php

/**
 * Astound
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to codemaster@astoundcommerce.com so we can send you a copy immediately.
 *
 * @category  Affirm
 * @package   Astound_Affirm
 * @copyright Copyright (c) 2016 Astound, Inc. (http://www.astoundcommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Copyright (c) 2016 Astound, Inc. (http://www.astoundcommerce.com).
 * Modified by Prog Leasing, LLC. Copyright (c) 2018, Prog Leasing, LLC.
 */

namespace Progressive\PayWithProgressive\Block;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;

/**
 * Payment Block Info class
 *
 * @package Progressive\PayWithProgressive\Block
 */
class Info extends ConfigurableInfo
{
    const LEASE_ID = 'leaseId';

    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $info = $this->getInfo();
        $leaseID = $info->getAdditionalInformation(self::LEASE_ID);
        $transport = new \Magento\Framework\DataObject([(string)__('Lease ID') => $leaseID]);
        $transport = parent::_prepareSpecificInformation($transport);

        return $transport;
    }

    /**
     * Changed standard template
     *
     * @var string
     */
    protected $_template = 'Progressive_PayWithProgressive::payment/info/edit.phtml';


    /**
     * Retrieve translated label
     *
     * @param string $field
     * @return Phrase|string
     */
    protected function getLabel($field)
    {
        return __($field);
    }

    /**
     * Is admin panel
     *
     * @return bool
     */
    protected function isInAdminPanel()
    {
        return $this->_appState->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;
    }

}
