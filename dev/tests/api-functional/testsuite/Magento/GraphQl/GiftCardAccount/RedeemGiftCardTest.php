<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\GiftCardAccount;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test redeemGiftCardBalanceAsStoreCredit mutation
 */
class RedeemGiftCardTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GiftCardAccount/_files/giftcardaccount.php
     */
    public function testRedeemGiftCard()
    {
        $giftCardCode = "giftcardaccount_fixture";
        $customerToken = $this->customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        $query = $this->getRedeemGiftCardQuery($giftCardCode);
        $customerHeader = ['Authorization' => 'Bearer ' . $customerToken];

        $result = $this->graphQlMutation($query, [], '', $customerHeader);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertArrayHasKey('redeemGiftCardBalanceAsStoreCredit', $result);
        $resultData = $result['redeemGiftCardBalanceAsStoreCredit'];
        $this->assertEquals($giftCardCode, $resultData['code']);
        $this->assertEquals(0, $resultData['balance']['value']);
    }

    /**
     * @magentoApiDataFixture Magento/GiftCardAccount/_files/giftcardaccount.php
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot find the customer to update balance
     */
    public function testRedeemAsGuestIsNotAllowed()
    {
        $giftCardCode = "giftcardaccount_fixture";
        $query = $this->getRedeemGiftCardQuery($giftCardCode);

        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GiftCardAccount/_files/giftcardaccounts_for_search.php
     * @expectedException \Exception
     * @expectedExceptionMessage Gift card account is not redeemable.
     */
    public function testRedeemNonRedeemableCardIsNotAllowed()
    {
        $giftCardCode = "gift_card_account_5";
        $customerToken = $this->customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        $query = $this->getRedeemGiftCardQuery($giftCardCode);
        $customerHeader = ['Authorization' => 'Bearer ' . $customerToken];

        $this->graphQlMutation($query, [], '', $customerHeader);
    }

    /**
     * @magentoApiDataFixture Magento/CustomerBalance/_files/disable_customer_balance.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GiftCardAccount/_files/giftcardaccount.php
     * @expectedException \Exception
     * @expectedExceptionMessage You can't redeem a gift card now.
     */
    public function testRedeemIsNotAllowedWhenStoreCreditIsDisabled()
    {
        $giftCardCode = "giftcardaccount_fixture";
        $customerToken = $this->customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        $query = $this->getRedeemGiftCardQuery($giftCardCode);
        $customerHeader = ['Authorization' => 'Bearer ' . $customerToken];

        $this->graphQlMutation($query, [], '', $customerHeader);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Exception
     * @expectedExceptionMessage Gift card not found
     */
    public function testRedeemNonExistentGiftCard()
    {
        $giftCardCode = 'non-existent-giftcardaccount';
        $customerToken = $this->customerTokenService->createCustomerAccessToken('customer@example.com', 'password');
        $query = $this->getRedeemGiftCardQuery($giftCardCode);
        $customerHeader = ['Authorization' => 'Bearer ' . $customerToken];

        $this->graphQlMutation($query, [], '', $customerHeader);
    }

    /**
     * Get redeemGiftCardBalanceAsStoreCredit query string
     *
     * @param string $giftCardCode
     * @return string
     */
    private function getRedeemGiftCardQuery(string $giftCardCode): string
    {
        return <<<QUERY
mutation{
  redeemGiftCardBalanceAsStoreCredit(input: {gift_card_code: "{$giftCardCode}"})
  {
    code
    balance{
      value
      currency
    }
  }
}
QUERY;
    }
}
