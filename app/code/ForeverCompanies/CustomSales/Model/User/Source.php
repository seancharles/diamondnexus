<?php

namespace ForeverCompanies\CustomSales\Model\User;

use Magento\Framework\Data\Collection;
use \Magento\User\Model\ResourceModel\User\CollectionFactory;

class Source implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $userCollectionFactory;

    public function __construct(
        CollectionFactory $userCollectionFactory
    ) {
        $this->userCollectionFactory = $userCollectionFactory;
    }

    public function toOptionArray()
    {
        $adminUsers = [];

        $collection = $this->userCollectionFactory->create();
        $collection->addOrder('firstname', Collection::SORT_ORDER_ASC);
        foreach ($collection as $user) {
            $adminUsers[] = [
                'value' => $user->getId(),
                'label' => $user->getName()
            ];
        }

        return $adminUsers;
    }
}
