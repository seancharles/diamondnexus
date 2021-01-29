<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Forms\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddFormData implements DataPatchInterface
{
    const DN_WEBSITE_ID = 1;
    const FA_WEBSITE_ID = 2;
    const TF_WEBSITE_ID = 3;
    
    const FORM_STATUS_ACTIVE = 1;
    
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $connResource;
    
    /**
     * Constructor
     *
     * @param Form $form
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Framework\App\ResourceConnection $connResource
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->connResource = $connResource;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        
        $formData = [
            [
                'name' => 'fa short form',
                'website_id' => self::FA_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'firstname' => 'First Name',
                    'lastname' => 'Last Name',
                    'email' => 'Email Address',
                    'telephone' => 'Telephone',
                    'design' => 'What do you want to design'
                ]),
                'fields_validation_json' => json_encode([
                    'firstname' => 'required',
                    'lastname' => 'required',
                    'email' => 'required',
                    'telephone' => 'required'
                ]),
            ],
            [
                'name' => 'fa long form',
                'website_id' => self::FA_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'email' => 'Email Address',
                    'telephone' => 'Telephone',
                    'designfor' => 'Who are you designing for?',
                    'selectJewelryType' => 'Design insight',
                    'selectShapePreference' => 'Shape preference',
                    'selectMetalType' => 'Select your metal type',
                    'selectNeedBy' => 'When do you need it by?',
                    'txtComments' => 'Is there anything else you\'d like us to know?',
                    'imageUploadOne' => 'Image #1',
                    'imageUploadTwo' => 'Image #2',
                    'imageUploadThree' => 'Image #3'
                ]),
                'fields_validation_json' => json_encode([
                ]),
            ],
            [
                'name' => 'dn catalog request form',
                'website_id' => self::DN_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'firstname' => 'First Name',
                    'lastname' => 'Last Name',
                    'email' => 'Email Address',
                    'coutry' => 'Country',
                    'address1' => 'Address 1',
                    'address2' => 'Apt / Suite / Other',
                    'city' => 'City',
                    'state' => 'State',
                    'zip' => 'Zip',
                    'engagement' => 'Are you shopping for an Engagement Ring?',
                    'type_need' => 'Which best applies to you: ',
                    'need_by' => 'When do you need the Engagement Ring by?',
                    'itemsofinterest' => 'Other Jewelry pieces you are interested in'
                ]),
                'fields_validation_json' => json_encode([
                    'firstname' => 'required',
                    'lastname' => 'required',
                    'email' => 'required',
                    'coutry' => 'required',
                    'address1' => 'required',
                    'city' => 'required',
                    'zip' => 'required'
                ]),
            ],
            [
                'name' => 'dn modal form',
                'website_id' => self::DN_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'email' => 'Email Address'
                ]),
                'fields_validation_json' => json_encode([
                    'email' => 'required'
                ]),
            ],
            [
                'name' => 'fa modal form',
                'website_id' => self::FA_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'firstname' => 'First Name'
                ]),
                'fields_validation_json' => json_encode([
                    'email' => 'required'
                ]),
            ],
            [
                'name' => 'tf modal form',
                'website_id' => self::TF_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'email' => 'Email Address'
                ]),
                'fields_validation_json' => json_encode([
                    'email' => 'required'
                ]),
            ]
        ];
        
        $connection = $this->connResource->getConnection();
        $connection->insertMultiple($connection->getTableName('fc_form'), $formData);
        
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }
    
    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}
