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
	protected $storeManager;
	
    /**
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
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)  {

		$shipdates = [];

		$days = (int) $args['days'];
		
		$currentStampBeforeCutoff = $this->shipdateHelper->getTimestamp();
		$currentStampAfterCutoff = $this->shipdateHelper->getTimestamp(true);
		
		for($i=1; $i<=$days; $i++) {

			// timestamps midnight and 2:01
			$timestampBeforeCutoff = $this->shipdateHelper->adjustTimestampDays($currentStampBeforeCutoff, $i);
			$timestampAfterCutoff = $this->shipdateHelper->adjustTimestampDays($currentStampAfterCutoff, $i);
			
			// date formatted for array keys
			$date = date('d-m-Y', $timestampBeforeCutoff);
			
			$shipdates[$date]['before_cutoff'] = [];
			$shipdates[$date]['after_cutoff'] = [];

			// push asd for each shipping group before and after cutoff
			for($j=1; $j<=20; $j++) {
				$shipdates[$date]['before_cutoff'][] = $this->shipdateHelper->getShipdate($j, $timestampBeforeCutoff);
				$shipdates[$date]['after_cutoff'][] = $this->shipdateHelper->getShipdate($j, $timestampAfterCutoff);
			}
		}
		
		$data['json'] = json_encode($shipdates);
		
		return $data;
    }
}