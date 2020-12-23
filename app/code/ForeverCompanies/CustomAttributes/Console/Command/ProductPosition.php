<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Console\Command;

use Magento\Catalog\Model\ResourceModel\CategoryProduct;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProductPosition extends Command
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var CategoryProduct
     */
    protected $categoryProductResource;

    /**
     * ProductPosition constructor.
     * @param State $state
     * @param CategoryProduct $categoryProductResource
     * @param string|null $name
     */
    public function __construct(
        State $state,
        CategoryProduct $categoryProductResource,
        string $name = null
    ) {
        $this->state = $state;
        $this->categoryProductResource = $categoryProductResource;
        parent::__construct($name);
    }

    /**
     * @var string
     */
    protected $name = 'forevercompanies:product-position';

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->state->setAreaCode(Area::AREA_GLOBAL);
        $output->writeln("Get categories for change product's position...");
        $categories = $this->getCategories();
        $output->writeln('Categories for migration: ' . count($categories));
        foreach ($categories as $category) {
            $output->writeln('In process category ID = ' . $category['category_id']);
            $position = 1;
            foreach ($this->getProducts($category['category_id']) as $product) {
                $this->categoryProductResource->getConnection()->update(
                    $this->categoryProductResource->getMainTable(),
                    ['position' => $position],
                    ['entity_id = ' . $product['entity_id']]
                );
                $position++;
            }
        }
        $output->writeln('Products position in category are updated! Execute bin/magento indexer:reindex');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Update Category Products - Migrate Category Product Positions");
        parent::configure();
    }

    private function getCategories()
    {
        $connection = $this->categoryProductResource->getConnection();
        try {
            $select = $connection->select()->from($this->categoryProductResource->getMainTable(), 'category_id')
                ->group('category_id');
            return $connection->fetchAll($select);
        } catch (LocalizedException $e) {
            return [];
        }
    }

    private function getProducts($categoryId)
    {
        $connection = $this->categoryProductResource->getConnection();
        $select = $connection->select()->from($this->categoryProductResource->getMainTable())
            ->where('category_id = ?', $categoryId)
            ->order('position asc');
        return $connection->fetchAll($select);
    }
}
