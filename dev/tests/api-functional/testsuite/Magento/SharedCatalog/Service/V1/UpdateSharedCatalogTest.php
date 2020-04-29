<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Service\V1;

/**
 * Test for update shared catalog.
 */
class UpdateSharedCatalogTest extends AbstractSharedCatalogTest
{
    const SERVICE_READ_NAME = 'sharedCatalogSharedCatalogRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/sharedCatalog/%d';

    /**
     * Test for update shared catalog.
     *
     * @return void
     * @magentoApiDataFixture Magento/SharedCatalog/_files/shared_catalog_two_store.php
     */
    public function testInvoke()
    {
        /** @var \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog */
        $sharedCatalog = $this->getSharedCatalog();
        $sharedCatalogId = $sharedCatalog->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf(self::RESOURCE_PATH, $sharedCatalogId),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'save',
            ],
        ];
        /** @var \Magento\Store\Model\Store  $store */
        $store = $this->objectManager->get(\Magento\Store\Model\Store::class);
        $store->load('shared_catalog', 'code');
        /** @var \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\Collection $taxClassCollection */
        $taxClassCollection = $this->objectManager
            ->create(\Magento\Tax\Model\ResourceModel\TaxClass\Collection::class);
        /** @var \Magento\Tax\Model\ClassModel $customerTaxClass */
        $customerTaxClass = $taxClassCollection->getLastItem();
        $updateData = [
            'id' => $sharedCatalogId,
            'name' => 'Name_' . time(),
            'description' => 'Description_' . time(),
            'store_id' => $store->getId(),
            'customer_group_id' => $sharedCatalog->getCustomerGroupId(),
            'created_at' => $sharedCatalog->getCreatedAt(),
            'created_by' => $sharedCatalog->getCreatedBy(),
            'type' => $sharedCatalog->getType(),
            'tax_class_id' => $customerTaxClass->getId()
        ];
        $respSharedCatalogData = $this->_webApiCall($serviceInfo, ['sharedCatalog' => $updateData]);
        $this->assertEquals($respSharedCatalogData, $sharedCatalogId, 'Could not update shared catalog.');
        $updatedSharedCatalog = $this->sharedCatalogRepository->get($sharedCatalogId);
        $this->assertEquals(
            $updateData['name'],
            $updatedSharedCatalog->getName(),
            'Could not update shared catalog name.'
        );
        $this->assertEquals(
            $updateData['description'],
            $updatedSharedCatalog->getDescription(),
            'Could not update shared catalog description.'
        );
        $this->assertEquals(
            $updateData['store_id'],
            $updatedSharedCatalog->getStoreId(),
            'Could not update shared catalog store id.'
        );
        $this->assertEquals(
            $updateData['tax_class_id'],
            $updatedSharedCatalog->getTaxClassId(),
            'Could not update shared catalog tax class id.'
        );
        $store->load('default', 'code');
    }
}
