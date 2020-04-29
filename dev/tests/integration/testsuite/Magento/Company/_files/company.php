<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User;

/** @var User $user */
$user = Bootstrap::getObjectManager()->create(User::class);
$user->loadByUsername(\Magento\TestFramework\Bootstrap::ADMIN_NAME);

/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);

/** @var Customer $customer */
$customer = Bootstrap::getObjectManager()->create(Customer::class);
$customer->setWebsiteId(1)
    ->setEmail('customer@magento.com')
    ->setPassword('password')
    ->setFirstname('John')
    ->setLastname('Doe')
    ->isObjectNew(true);
$customer->save();
$customer = $customerRepository->get('customer@magento.com');

/** @var CompanyRepositoryInterface $companyRepository */
$companyRepository = Bootstrap::getObjectManager()->get(CompanyRepositoryInterface::class);

/** @var CompanyInterface $company */
$company = Bootstrap::getObjectManager()->create(CompanyInterface::class);
$company->setCompanyName('Magento')
    ->setStatus(CompanyInterface::STATUS_APPROVED)
    ->setCompanyEmail('email@magento.com')
    ->setComment('Comment')
    ->setSuperUserId($customer->getId())
    ->setSalesRepresentativeId($user->getId())
    ->setCustomerGroupId(1)
    ->setCountryId('US')
    ->setRegionId(1)
    ->setCity('City')
    ->setStreet('123 Street')
    ->setPostcode('Postcode')
    ->setTelephone('5555555555');
$companyRepository->save($company);
