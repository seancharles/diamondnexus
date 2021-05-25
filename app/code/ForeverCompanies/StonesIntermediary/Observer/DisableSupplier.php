<?php

namespace ForeverCompanies\StonesIntermediary\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class DisableSupplier implements ObserverInterface
{
    protected $supplierMap;
    protected $productColl;
    protected $statusDisabled;
    
    public function __construct(
        CollectionFactory $collectionFac
    ) {   
        $this->productColl = $collectionFac->create();
        
        $this->statusDisabled = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;
        $this->supplierMap = array(
            "blumoon" => "3533",
            "classic" => "3534",
            "greenrocks" => "3535",
            "internal" => "3536",
            "labrilliante" => "3537",
            "paradiam" => "3538",
            "pdc" => "3539",
            "stuller" => "3540",
            "washington" => "3541",
            "foundry" => "3542",
            "diamondfoundry" => "3542",
            "meylor" => "3543",
            "ethereal" => "3544",
            "smilingrocks" => "3545",
            "unique" => "3546",
            "qualitygold" => "3547",
            "flawlessallure" => "3548",
            "labs" => "3549",
            "labsdiamond" => "3549",
            "Fenix" => "3550",
            "fenix" => "3550",
            "brilliantdiamonds" => "3551",
            "growndiamondcorpusa" => "3552",
            "internationaldiamondjewelry" => "3553",
            "ecogrown" => "3554",
            "purestones" => "3555",
            "proudest" => "3556",
            "proudestlegendlimited" => "3556",
            "dvjcorp" => "3357",
            "dvjewelrycorporation" => "3557",
            "indiandiamonds" => "3558",
            "growndiamondcorp" => "3559",
            "lush" => "3560",
            "lushdiamonds" => "3560",
            "altr" => "3561",
            "Forever Grown" => "3562",
            "internalaltr" => "3563",
            // TODO: Create these attribute options and place their values here.
            "bhakti" => ""
        );
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->productColl->addAttributeToFilter('supplier', $this->supplierMap[$observer->getEvent()->getPost()['code']])->load();
        
        foreach ($this->productColl as $_product) {
            $_product->setStatus($this->statusDisabled);
            $_product->save();
            unset($_product);
        }
        
        return $observer;
    }
}