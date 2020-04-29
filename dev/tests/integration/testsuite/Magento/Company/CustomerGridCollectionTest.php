<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Company;

use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Grid\Collection as CustomerGridCollection;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDataFixture Magento/Customer/_files/two_customers.php
 * @magentoDataFixture Magento/SharedCatalog/_files/assigned_company.php
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
 */
class CustomerGridCollectionTest extends TestCase
{
    /**
     * @var CustomerGridCollection
     */
    private $customerGridCollection;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $indexerRegistry = Bootstrap::getObjectManager()->create(IndexerRegistry::class);
        $indexer = $indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
        $indexer->reindexAll();

        $this->customerGridCollection = Bootstrap::getObjectManager()->create(CustomerGridCollection::class);
    }

    /**
     * @param int $customerType
     * @param int $expectedCount
     * @dataProvider getTotalCountDataProvider
     */
    public function testGetTotalCount(int $customerType, int $expectedCount)
    {
        $this->customerGridCollection->addFieldToFilter('customer_type', $customerType);
        $count = $this->customerGridCollection->getTotalCount();
        $this->assertEquals($expectedCount, $count);
    }

    /**
     * @return array
     */
    public function getTotalCountDataProvider(): array
    {
        return [
            [
                CompanyCustomerInterface::TYPE_COMPANY_ADMIN,
                1,
            ],
            [
                CompanyCustomerInterface::TYPE_COMPANY_USER,
                0,
            ],
            [
                CompanyCustomerInterface::TYPE_INDIVIDUAL_USER,
                2,
            ],
        ];
    }

    /**
     * @param int $customerType
     * @param array $expectedEmails
     * @dataProvider getItemsDataProvider
     */
    public function testGetItems(int $customerType, array $expectedEmails)
    {
        $this->customerGridCollection->addFieldToFilter('customer_type', $customerType);
        $items = $this->customerGridCollection->getItems();
        $emails = [];
        foreach ($items as $item) {
            $emails[] = $item->getCustomAttribute('email')->getValue();
        }
        $this->assertSame($expectedEmails, $emails);
    }

    /**
     * @return array
     */
    public function getItemsDataProvider(): array
    {
        return [
            [
                CompanyCustomerInterface::TYPE_COMPANY_ADMIN,
                ['email1@companyquote.com'],
            ],
            [
                CompanyCustomerInterface::TYPE_COMPANY_USER,
                [],
            ],
            [
                CompanyCustomerInterface::TYPE_INDIVIDUAL_USER,
                ['customer@example.com', 'customer_two@example.com'],
            ],
        ];
    }
}
