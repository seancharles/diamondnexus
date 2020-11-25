<?php

declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\SpecificSourceInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Framework\Api\CustomAttributesDataInterface;

class BandWidth extends AbstractEav
{
    protected $eq = 'string';
}
