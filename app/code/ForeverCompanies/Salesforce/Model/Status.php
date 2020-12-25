<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Status
 *
 * @package ForeverCompanies\Salesforce\Model
 */
class Status extends AbstractSource
{

    /**
     * #@+
     * Status values
     */
    const STATUS_ENABLED  = 1;
    const STATUS_DISABLED = 2;

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        return [
            self::STATUS_ENABLED  => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled'),
        ];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];

        foreach ($this->getOptionArray() as $index => $value) {
            $result[] = [
                'value' => $index,
                'label' => $value
            ];
        }

        return $result;
    }

    /**
     * Retrieve option text by option value
     *
     * @param  string $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}
