<?php

namespace ForeverCompanies\CustomSales\Model\User;

use \Magento\User\Model\ResourceModel\User\CollectionFactory;

class Source implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct(
        CollectionFactory $userCollectionFactory
    )
    {
        $this->userCollectionFactory = $userCollectionFactory;
    }
    
    public function toOptionArray()
    {
        $adminUsers = [];

        foreach ($this->userCollectionFactory->create() as $user) {
            $adminUsers[] = [
                'value' => $user->getId(),
                'label' => $user->getName()
            ];
        }

        return $adminUsers;
    }
}