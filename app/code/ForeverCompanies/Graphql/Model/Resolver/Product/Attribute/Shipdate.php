<?php

declare(strict_types=1);

namespace ForeverCompanies\Graphql\Model\Resolver\Product\Attribute;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Customers field resolver, used for GraphQL request processing.
 */

class Shipdate implements ResolverInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \ForeverCompanies\CustomSales\Helper\Shipdate
     */
    private $shipdateHelper;

    /**
     * @param \ForeverCompanies\CustomSales\Helper\Shipdate $shipdateHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \ForeverCompanies\CustomSales\Helper\Shipdate $shipdateHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->shipdateHelper = $shipdateHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {

        $shipdates = [];

        $days = (int) $args['days'];

        $currentStampBeforeCutoff = $this->shipdateHelper->getTimestamp();
        $currentStampAfterCutoff = $this->shipdateHelper->getTimestamp(true);

        for ($i=0; $i<=$days; $i++) {
            // timestamps midnight and 2:01
            $timestampBeforeCutoff = $this->shipdateHelper->adjustTimestampDays($currentStampBeforeCutoff, $i);
            $timestampAfterCutoff = $this->shipdateHelper->adjustTimestampDays($currentStampAfterCutoff, $i);

            // date formatted for array keys
            $date = date('Y-m-d', $timestampBeforeCutoff);

            $shipdates[$date]['before_cutoff'] = [];
            $shipdates[$date]['after_cutoff'] = [];

            // push asd for each shipping group before and after cutoff
            for ($j=0; $j<=20; $j++) {
                $beforeCutOff = $this->shipdateHelper->getShipdate($j, $timestampBeforeCutoff);
                $shipdates[$date]['before_cutoff'][$j . ' Day'] = $beforeCutOff;
                $afterCutOff = $this->shipdateHelper->getShipdate($j, $timestampAfterCutoff);
                $shipdates[$date]['after_cutoff'][$j . ' Day'] = $afterCutOff;
            }
        }
        /** TODO: change to magento's serializer */
        $data['json'] = json_encode($shipdates);

        return $data;
    }
}
