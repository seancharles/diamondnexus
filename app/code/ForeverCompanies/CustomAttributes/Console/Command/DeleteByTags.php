<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteByTags extends AbstractCommand
{

    /**
     * @var string
     */
    protected $name = 'forevercompanies:delete-by-tags';

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->state->setAreaCode(Area::AREA_GLOBAL);
        $output->writeln("Delete products by tags...");
        $products = $this->helper->getProductsForDeleteCollection();
        $output->writeln("Count of products - " . $products->count());
        foreach ($products as $product) {
                $output->writeln("Deleting " . $product->getId());
                $this->helper->deleteProduct((int)$product->getId());

        }
        $output->writeln('Delete is complete! Please execute bin/magento indexer:reindex');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Delete old products by tags after M1 - M2 media migration");
        parent::configure();
    }
}
