<?php
namespace ForeverCompanies\StonesIntermediary\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;
use ForeverCompanies\StonesIntermediary\Model\ResourceModel\StonesSupplier\CollectionFactory;;

class Supplier extends AbstractSource implements OptionSourceInterface, SourceInterface
{
    
    private $supplierCollectionFactory;

    public function __construct(
        CollectionFactory $collectionF
    ) {
        $this->supplierCollectionFactory = $collectionF;
    }

    public function getAllOptions()
    {
        $options = [];
        $collection = $this->supplierCollectionFactory->create();
        
        foreach ($collection as $supplier) {
            $options[] = [
                'value' => $supplier['id'],
                'label' => $supplier['name']
            ];
        } 
        $this->_options = $options;
        return $this->_options;
    }
    
    final public function toOptionArray()
    {
        $options = [];
        $collection = $this->supplierCollectionFactory->create();
        
        foreach ($collection as $supplier) {
            $options[] = [
                'value' => $supplier['id'],
                'label' => $supplier['name']
            ];
        } 
        
        return $options;
    }
}