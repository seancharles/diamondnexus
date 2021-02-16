<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Forms\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddTfGetStartedData implements DataPatchInterface
{
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
                'name' => 'tf short form',
                'website_id' => self::TF_WEBSITE_ID,
                'active' => self::FORM_STATUS_ACTIVE,
                'fields_json' => json_encode([
                    'firstname' => 'First Name',
                    'lastname' => 'Last Name',
                    'email' => 'Email Address',
                    'telephone' => 'Telephone'
                ]),
                'fields_validation_json' => json_encode([
                    'firstname' => 'required',
                    'lastname' => 'required',
                    'email' => 'required',
                    'telephone' => 'required'
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
