<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Console\Command;

use ForeverCompanies\CustomAttributes\Helper\TransformData;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\State;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteDisabled extends AbstractCommand
{

    /**
     * @var string
     */
    protected $name = 'forevercompanies:delete-disabled';

    /**
     * @var Session
     */
    protected $session;

    public function __construct(
        State $state,
        TransformData $helper,
        Session $catalogSession
    )
    {
        parent::__construct($state, $helper);
        $this->session = $catalogSession;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\SessionException
     * @throws LocalizedException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $output->writeln("Get products for delete...");
        $productCollection = $this->helper->getProductsForDeleteCollection();
        $output->writeln('Products for delete: ' . $productCollection->count());
        foreach ($productCollection->getItems() as $item) {
            try {
                $this->helper->deleteProduct((int)$item->getData('entity_id'));
            } catch (InputException $e) {
                $output->writeln($e->getMessage());
            } catch (NoSuchEntityException $e) {
                $output->writeln($e->getMessage());
            } catch (StateException $e) {
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
        $this->setDescription("Delete products after M1 - M2 migration");
        parent::configure();
    }
}
