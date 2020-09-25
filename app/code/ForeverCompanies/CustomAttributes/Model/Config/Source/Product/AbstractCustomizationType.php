<?php

namespace ForeverCompanies\CustomAttributes\Model\Config\Source\Product;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Customization types mode source
 */
class AbstractCustomizationType implements OptionSourceInterface
{
    /**
     * Store manager.
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    const OPTIONS = [];

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this::OPTIONS as $label => $option) {
            $options[] = ['value' => $option, 'label' => __($label)];
        }
        return $options;
    }
}
