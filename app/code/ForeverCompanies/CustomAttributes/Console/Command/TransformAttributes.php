<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Console\Command;

use ForeverCompanies\CustomAttributes\Helper\TransformData;
use Magento\Framework\App\State;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TransformAttributes extends Command
{

    /**
     * @var TransformData
     */
    protected $helper;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var string
     */
    protected $name = 'forevercompanies:transform-attributes';

    /**
     * TransformAttributes constructor.
     * @param State $state
     * @param TransformData $helper
     */
    public function __construct(
        State $state,
        TransformData $helper
    ) {
        $this->state = $state;
        $this->helper = $helper;
        parent::__construct($this->name);
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        $output->writeln("Get products for transformation...");
        $productCollection = $this->helper->getProductsForTransformCollection();
        $output->writeln('Products for transformation: ' . $productCollection->count());
        foreach ($productCollection->getItems() as $item) {
            try {
                $output->writeln('In process product ID = '. $item->getData('entity_id'));
                $this->helper->transformProduct((int)$item->getData('entity_id'));
            } catch (InputException $e) {
                $output->writeln($e->getMessage());
            } catch (NoSuchEntityException $e) {
                $output->writeln($e->getMessage());
            } catch (StateException $e) {
                $output->writeln($e->getMessage());
            } catch (LocalizedException $e) {
                $output->writeln($e->getMessage());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Transform attributes after M1 - M2 migration");
        parent::configure();
    }
}