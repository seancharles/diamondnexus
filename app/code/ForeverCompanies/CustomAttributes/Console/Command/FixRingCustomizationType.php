<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Console\Command;

use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Option;

class FixRingCustomizationType extends Command
{
    /**
     * @var string
     */
    protected $name = 'forevercompanies:fix-ring-customization-type';

    /**
     * @var State
     */
    protected $state;
    
    protected $productColl;
    protected $productOptionModel;

    /**
     * RefreshPatchList constructor.
     * @param State $state
     * @param string|null $name
     */
    public function __construct(
        State $state,
        CollectionFactory $collectionFac,
        Option $opt,
        string $name = null
    ) {
        parent::__construct($name);
        $this->state = $state;
        
        $this->productColl = $collectionFac->create();
        $this->productOptionModel = $opt;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $output->writeln('Starting..');
        
        $this->productColl->addFieldToFilter('attribute_set_id', 18);
        $this->productColl->addAttributeToSelect("name")->load();
        
        foreach ($this->productColl as $product) {
            $options = $this->productOptionModel->getProductOptionCollection($product);
            foreach ($options as $o) {
                $o->setCustomizationType('ring_size');
                try {
                    $o->save();
                } catch(\Magento\Framework\Validator\Exception $e) {
                    continue;
                } catch(\Exception $e) {
                    continue;
                }
            }
        }
        $output->writeln('Complete');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Sets Customizable Options -> Customization Type to 'Ring Size' for Products in Rings Attribute Set");
        parent::configure();
    }
}
