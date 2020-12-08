<?php

namespace ForeverCompanies\Salesforce\Block\Adminhtml\Map;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

/**
 * Class Edit
 *
 * @package ForeverCompanies\Salesforce\Block\Adminhtml\Map
 */
class Edit extends Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;


    /**
     * @param Context  $context
     * @param Registry $registry
     * @param array    $data
     * @return void
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize blog post edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'ForeverCompanies_Salesforce';
        $this->_controller = 'adminhtml_map';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Mapping'));
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label'          => __('Save and Continue Edit'),
                'class'          => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event'  => 'saveAndContinueEdit',
                            'target' => '#edit_form',
                        ],
                    ],
                ],
            ],
            -100
        );
        $this->buttonList->add(
            'updateallfields',
            [
                'label' => __('Refresh Fields'),
            ],
            -90
        );
        $this->buttonList->update('delete', 'label', __('Delete'));
    }
}
