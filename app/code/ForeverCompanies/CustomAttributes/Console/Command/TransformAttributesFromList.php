<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Console\Command;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TransformAttributesFromList extends AbstractCommand
{

    /**
     * @var string
     */
    protected $name = 'forevercompanies:transform-attributes-from-list';

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        try {
            $this->state->getAreaCode();
        } catch (LocalizedException $e) {
            $this->state->setAreaCode(Area::AREA_GLOBAL);
        }
        $output->writeln("Get products list for transformation...");
        $productCollection = $this->helper->getProductListFromAdmin();
        if ($productCollection == '') {
            $output->writeln("Your list of ids is empty. Please fill it in admin panel");
            return;
        }
        $list = explode(',', $productCollection);
        $output->writeln('Products for transformation: ' . count($list));
        foreach ($list as $product) {
            $output->writeln('In process product ID = ' . $product);
            $this->helper->transformProduct((int)$product);
        }
        $output->writeln('Transformation is complete! Please execute bin/magento cache:flush');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Transform attributes for list of products (ids sets on adminhtml)");
        parent::configure();
    }
}
