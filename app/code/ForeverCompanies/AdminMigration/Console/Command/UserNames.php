<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\AdminMigration\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ResourceConnection;

class UserNames extends Command
{
    const NAME = 'forevercompanies:update-status-usernames';
  
    private $resourceConnection;
    
    public function __construct(
        ResourceConnection $resourceConnection
        ) {
            $this->resourceConnection = $resourceConnection;
            
            parent::__construct(self::NAME);
    }
    
    protected function configure()
    {
        $this->setName(self::NAME);
        $this->setDescription('Brings comment user names over from M1 DB.');
     
        parent::configure();
    }
    
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->resourceConnection->getConnection();
        
        if (!$connection->isTableExists("m1_sales_flat_order_status_history")) {
            $output->writeln('<error>DB Table m1_sales_flat_order_status_history does not exist.</error>');
            return;
        }
        
        $output->writeln('<comment>Beginning import...</comment>');
       
        $query  = 'SET `foreign_key_checks` = 0;';
        $connection->query($query);
        
        $query = 'INSERT INTO sales_order_status_history(`entity_id`, `sales_person`)
            SELECT `entity_id`, `username` 
            FROM `m1_sales_flat_order_status_history` `m1`
            WHERE m1.entity_id <= (SELECT MAX(entity_id) FROM `sales_order_status_history`)
            ON DUPLICATE KEY UPDATE `sales_person` = `m1`.`username`';
        
        try {
            $connection->query($query);
        } catch(Exception $e){
            $query = 'SET `foreign_key_checks` = 1;';
            $connection->query($query);
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return;
        }
        
        $query = 'SET `foreign_key_checks` = 1;';
        $connection->query($query);
        
        $output->writeln('<info>Comment Username / Salesperson import complete.</info>');
    }
}