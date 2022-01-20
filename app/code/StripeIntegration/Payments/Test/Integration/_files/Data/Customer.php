<?php

$objectManager = \Magento\TestFramework\ObjectManager::getInstance();

$repository = $objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);

try
{
    $customer = $repository->get('customer@example.com');
    return;
}
catch (\Exception $e)
{
    // Customer was not found
    $customer = $objectManager->create(\Magento\Customer\Api\Data\CustomerInterface::class);
}

$customer->setWebsiteId(1)
    // ->setId(1)
    ->setEmail('customer@example.com')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setPrefix('Mr.')
    ->setFirstname('John')
    ->setMiddlename('A')
    ->setLastname('Smith')
    ->setSuffix('Esq.')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
    ->setTaxvat('12')
    ->setGender(0);

$customer = $repository->save($customer);
