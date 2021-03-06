<?php

namespace ForeverCompanies\Forms\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\App\ResourceConnection;

class UpgradeSchema implements UpgradeSchemaInterface
{
    protected $resourceConnection;
    
    public function __construct(
        ResourceConnection $resourceConn
    ) {
        $this->resourceConnection = $resourceConn;
    }
    
    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
            $installer = $setup;
            
            $installer->startSetup();
            
            $query = "insert ignore into fc_form(`name`, `website_id`, `active`, `fields_json`, `fields_validation_json`) 

            values('dn inline catalog request form', 1, 1, '{\"firstname\":\"First Name\",\"lastname\":\"Last Name\",\"email\":\"Email Address\",\"telephone\":\"Telephone\",\"address1\":\"Address 1\",\"address2\":\"Apt / Suite / Other\",\"city\":\"City\",\"state\":\"State\",\"zip\":\"Zip\",\"country\":\"Country\"}', '{\"firstname\":\"required\",\"lastname\":\"required\",\"email\":\"required\",\"telephone\":\"required\"}'),
            
            ('dn inline giveaway form', 1, 1, '{\"email\":\"Email Address\"}', '{\"email\":\"required\"}'),
            
            ('dn modal get started form', 1, 1, '{\"firstname\":\"First Name\",\"lastname\":\"Last Name\",\"email\":\"Email Address\",\"telephone\":\"Telephone\"}', '{\"firstname\":\"required\",\"lastname\":\"required\",\"email\":\"required\",\"telephone\":\"required\"}'),
            
            ('dn modal giveaway form', 1, 1, '{\"email\":\"Email Address\",\"gender\":\"Gender\"}', '{\"email\":\"required\"}'),
            
            ('dn modal catalog request form', 1, 1, '{\"firstname\":\"First Name\",\"lastname\":\"Last Name\",\"telephone\":\"Telephone\",\"address1\":\"Address 1\",\"address2\":\"Apt / Suite / Other\",\"city\":\"City\",\"state\":\"State\",\"zip\":\"Zip\"}', '{\"firstname\":\"required\",\"lastname\":\"required\",\"address1\":\"required\",\"city\":\"required\",\"state\":\"required\",\"zip\":\"required\"}'),
            
            ('fa inline giveaway form', 2, 1, '{\"email\":\"Email Address\"}', '{\"email\":\"required\"}'),
            
            ('fa modal giveaway form', 2, 1, '{\"email\":\"Email Address\"}', '{\"email\":\"required\"}'),
            
            ('fa inline get started form', 2, 1, '{\"firstname\":\"First Name\",\"lastname\":\"Last Name\",\"email\":\"Email Address\",\"telephone\":\"Telephone\",\"design\":\"What do you want to design\"}','{\"firstname\":\"required\",\"lastname\":\"required\",\"email\":\"required\",\"telephone\":\"required\",\"design\":\"required\"}'),
            
            ('fa inline long engagement form', 2, 1, '{\"designFor-eng\":\"Who are you designing for?\",\"designInsight-eng\":\"Design Insight\",\"centerStoneShape-eng\":\"Center Stone Shape\",\"selectMetalType-eng\":\"Metal Type\",\"selectRingType-eng\":\"Ring Type\",\"selectStylePreference-eng\":\"Style preference\",\"selectNeedBy-eng\":\"When do you need it by?\",\"anythingElse-eng\":\"Is there anything else you\'d like us to know?\",\"imageUpload-eng\":\"Upload Images\"}','{\"designFor-eng\":\"required\",\"designInsight-eng\":\"required\",\"centerStoneShape-eng\":\"required\",\"selectMetalType-eng\":\"required\",\"selectRingType-eng\":\"required\",\"selectStylePreference-eng\":\"required\",\"selectNeedBy-eng\":\"required\"}'),

            ('fa inline long jewelry form', 2, 1, '{\"designFor-jewelry\":\"Who are you designing for?\",\"designInsight\":\"Design Insight\",\"selectShapePreference-jewelry\":\"Shape Preference\",\"selectMetalType-jewelry\":\"Metal Type\",\"selectStylePreference-jewelry\":\"Style preference\",\"selectNeedBy-jewelry\":\"When do you need it by?\",\"anythingElse-jewelry\":\"Is there anything else you\'d like us to know?\",\"imageUpload-jewelry\":\"Upload Images\"}','{\"designFor-jewelry\":\"required\",\"selectJewelryType\":\"required\",\"designInsight\":\"required\",\"selectShapePreference-jewelry\":\"required\",\"selectMetalType-jewelry\":\"required\",\"selectStylePreference-jewelry\":\"required\",\"selectNeedBy-jewelry\":\"required\"}'),

            ('fa modal contact form', 2, 1, '{\"firstname\":\"First Name\",\"lastname\":\"Last Name\",\"email\":\"Email Address\",\"telephone\":\"Telephone\",\"additional_info\":\"Additional Info\"}', '{\"firstname\":\"required\",\"lastname\":\"required\",\"email\":\"required\"}'),
            
            ('tf inline giveaway form', 3, 1, '{\"email\":\"Email Address\"}', '{\"email\":\"required\"}'),
            
            ('tf modal giveaway form', 3, 1, '{\"email\":\"Email Address\"}', '{\"email\":\"required\"}'),
            
            ('tf get started form', 3, 1, '{\"firstname\":\"First Name\",\"lastname\":\"Last Name\",\"email\":\"Email Address\",\"telephone\":\"Telephone\"}', '{\"firstname\":\"required\",\"lastname\":\"required\",\"email\":\"required\",\"telephone\":\"required\"}'),

            ('tf drop a hint form', 3, 1, '{\"friendemail\":\"Friend\'s Email Address\",\"message\":\"Your Email Message\",\"name\":\"Your Name\",\"email\":\"Your Email Address\",\"copy\":\"Send me a copy of this email\",\"updates\":\"Send me 12Fifteen email updates\"}', '{\"friendemail\":\"required\",\"message\":\"required\",\"name\":\"required\",\"email\":\"required\"}')

            ;";
                 
            $this->resourceConnection->getConnection()->query($query);
            
            $installer->endSetup();
    }
}
