<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ForeverCompanies\Rules\Ui\Component\Form;

use ForeverCompanies\Rules\Model\Rule;
use Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{
    /**
     * @var \ForeverCompanies\Rules\Model\Rule
     */
   protected $rule;

    public function __construct(Rule $rule)
    {
        $this->rule = $rule;
    }


    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 100.1.0
     */
    public function toOptionArray()
    {
        return $this->rule->getDatesForShipperHq();
    }
}
