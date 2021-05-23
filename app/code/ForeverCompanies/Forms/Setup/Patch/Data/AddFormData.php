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
                'name' => 'dn inline catalog request form',
                'website_id' => self::DN_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'firstname' => 'First Name',
                    'lastname' => 'Last Name',
                    'email' => 'Email Address',
                    'country' => 'Country',
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
                    'country' => 'required',
                    'address1' => 'required',
                    'city' => 'required',
                    'zip' => 'required'
                ]),
            ],
            [
                'name' => 'dn inline giveaway form',
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
                'name' => 'dn modal get started form',
                'website_id' => self::DN_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'firstname' => 'First Name',
                    'lastname' => 'Last Name',
                    'email' => 'Email Address',
                    'telephone' => 'Telephone',
                ]),
                'fields_validation_json' => json_encode([
                    'firstname' => 'required',
                    'lastname' => 'required',
                    'email' => 'required',
                    'telephone' => 'required'
                ]),
            ],
            [
                'name' => 'dn modal giveaway form',
                'website_id' => self::DN_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'email' => 'Email Address',
                    'gender' => 'Gender',
                ]),
                'fields_validation_json' => json_encode([
                    'email' => 'required'
                ]),
            ],
            [
                'name' => 'dn modal catalog request form',
                'website_id' => self::DN_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'firstname' => 'First Name',
                    'lastname' => 'Last Name',
                    'address1' => 'Address 1',
                    'address2' => 'Apt / Suite / Other',
                    'city' => 'City',
                    'state' => 'State',
                    'zip' => 'Zip'
                ]),
                'fields_validation_json' => json_encode([
                    'firstname' => 'required',
                    'lastname' => 'required',
                    'address1' => 'required',
                    'city' => 'required',
                    'state' => 'required',
                    'zip' => 'required'
                ]),
            ],
            [
                'name' => 'fa inline giveaway form',
                'website_id' => self::FA_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'email' => 'Email'
                ]),
                'fields_validation_json' => json_encode([
                    'email' => 'required'
                ]),
            ],
            [
                'name' => 'fa modal giveaway form',
                'website_id' => self::FA_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'email' => 'Email'
                ]),
                'fields_validation_json' => json_encode([
                    'email' => 'required'
                ]),
            ],
            [
                'name' => 'fa inline get started form',
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
                    'telephone' => 'required',
                    'design' => 'required'
                ]),
            ],
            [
                'name' => 'fa inline long engagement form',
                'website_id' => self::FA_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'designFor' => 'Who are you designing for?',
                    'designInsight' => 'Design Insight',
                    'centerStoneShape' => 'Center Stone Shape',
                    'metalType' => 'Metal Type',
                    'ringType' => 'Ring Type',
                    'stylePreference' => 'Style preference',
                    'needBy' => 'When do you need it by?',
                    'anythingElse' => 'Is there anything else you\'d like us to know?',
                    'howManyImages' => 'How many images would you like to upload?',
                    'imageUploadOne' => 'Image #1', 
                    'imageUploadTwo' => 'Image #2',
                    'imageUploadThree' => 'Image #3',
                    'imageUploadFour' => 'Image #4',
                    'imageUploadFive' => 'Image #5',
                    'imageUploadSix' => 'Image #6',
                    'imageUploadSeven' => 'Image #7',
                    'imageUploadEight' => 'Image #8',
                    'imageUploadNine' => 'Image #9',
                    'imageUploadTen' => 'Image #10'
                ]),
                'fields_validation_json' => json_encode([
                    'designFor' => 'required',
                    'designInsight' => 'required',
                    'centerStoneShape' => 'required',
                    'metalType' => 'required',
                    'ringType' => 'required',
                    'stylePreference' => 'required',
                    'needBy' => 'required',
                    'howManyImages' => 'required'
                ]),
            ],
            [
                'name' => 'fa inline long jewelry form',
                'website_id' => self::FA_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'designFor' => 'Who are you designing for?',
                    'jewelryType' => 'Jewelry Type',
                    'designInsight' => 'Design Insight',
                    'shapePreference' => 'Shape Preference',
                    'metalType' => 'Metal Type',
                    'ringType' => 'Ring Type',
                    'stylePreference' => 'Style preference',
                    'needBy' => 'When do you need it by?',
                    'anythingElse' => 'Is there anything else you\'d like us to know?',
                    'howManyImages' => 'How many images would you like to upload?',
                    'imageUploadOne' => 'Image #1',
                    'imageUploadTwo' => 'Image #2',
                    'imageUploadThree' => 'Image #3',
                    'imageUploadFour' => 'Image #4',
                    'imageUploadFive' => 'Image #5',
                    'imageUploadSix' => 'Image #6',
                    'imageUploadSeven' => 'Image #7',
                    'imageUploadEight' => 'Image #8',
                    'imageUploadNine' => 'Image #9',
                    'imageUploadTen' => 'Image #10'
                ]),
                'fields_validation_json' => json_encode([
                    'designFor' => 'required',
                    'jewelryType' => 'required',
                    'designInsight' => 'required',
                    'shapePreference' => 'required',
                    'metalType' => 'required',
                    'needBy' => 'required',
                    'anythingElse' => 'Is there anything else you\'d like us to know?',
                    'howManyImages' => 'required',
                    'imageUploadOne' => 'Image #1',
                    'imageUploadTwo' => 'Image #2',
                    'imageUploadThree' => 'Image #3',
                    'imageUploadFour' => 'Image #4',
                    'imageUploadFive' => 'Image #5',
                    'imageUploadSix' => 'Image #6',
                    'imageUploadSeven' => 'Image #7',
                    'imageUploadEight' => 'Image #8',
                    'imageUploadNine' => 'Image #9',
                    'imageUploadTen' => 'Image #10'
                ]),
            ],
            [
                'name' => 'fa modal contact form',
                'website_id' => self::FA_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'firstname' => 'First Name',
                    'lastname' => 'Last Name',
                    'email' => 'Email',
                    'telephone' => 'Telephone',
                    'additional_info' => 'Additional Info'
                ]),
                'fields_validation_json' => json_encode([
                    'firstname' => 'required',
                    'lastname' => 'required',
                    'email' => 'required'
                ]),
            ],
            [
                'name' => 'tf inline giveaway form',
                'website_id' => self::TF_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'email' => 'Email Address'
                ]),
                'fields_validation_json' => json_encode([
                    'email' => 'required'
                ]),
            ],
            [
                'name' => 'tf modal giveaway form',
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
    
    public static function getVersion()
    {
        return '2.0.0';
    }
}
