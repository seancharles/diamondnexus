<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace DiamondNexus\Multipay\Plugin\Model\Method;

use DiamondNexus\Multipay\Model\Constant;

/**
 * Class Available
 * @package DiamondNexus\Multipay\Plugin\Model\Method
 */
class Available
{

    /**
     * Get available payment methods
     *
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterGetAvailableMethods($subject, $result)
    {
        $multipayCode = Constant::MULTIPAY_METHOD;
        foreach ($result as $key=>$_result) {
            if ($_result->getCode() == $multipayCode) {
                $isAllowed =  true;
                if ($isAllowed) {
                   $result[$key] = $multipayCode;
                }
            }
        }
        return $result;
    }
}
