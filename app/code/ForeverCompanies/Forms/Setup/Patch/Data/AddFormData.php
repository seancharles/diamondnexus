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
                // fa short form
                'website_id' => self::FA_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'firstname' => 'First Name',
                    'lastname' => 'Last Name',
                    'email' => 'Email Address',
                    'telephone' => 'Telephone',
                    'design' => 'What do you want to design'
                ]),
                'validation_json' => json_encode([
                    'firstname' => 'required',
                    'lastname' => 'required',
                    'email' => 'required',
                    'telephone' => 'required'
                ]),
            ],
            [
                // long form
                'website_id' => self::FA_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'firstname' => 'First Name',
                    'lastname' => 'Last Name',
                    'email' => 'Email Address'
                ]),
                'validation_json' => json_encode([
                    'firstname' => 'required',
                    'lastname' => 'required',
                    'email' => 'required'
                ]),
            ],
            [
                // dn catalog request form
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
                'validation_json' => json_encode([
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
                // dn modal form
                'website_id' => self::DN_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'email' => 'Email Address'
                ]),
                'validation_json' => json_encode([
                    'email' => 'required'
                ]),
            ],
            [
                // fa modal form
                'website_id' => self::FA_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'firstname' => 'First Name'
                ]),
                'validation_json' => json_encode([
                    'email' => 'required'
                ]),
            ],
            [
                // tf modal form
                'website_id' => self::TF_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'email' => 'Email Address'
                ]),
                'validation_json' => json_encode([
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
