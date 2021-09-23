<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */




namespace Mirasvit\CacheWarmer\Setup\UpgradeData;


use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Mirasvit\CacheWarmer\Api\Data\SourceInterface;
use Mirasvit\CacheWarmer\Api\Repository\SourceRepositoryInterface;
use Mirasvit\CacheWarmer\Model\Config\Source\CustomerGroups;

class UpgradeData1018 implements UpgradeDataInterface
{
    private $sourceRepository;

    private $customerGroups;

    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        CustomerGroups $customerGroups
    ) {
        $this->sourceRepository = $sourceRepository;
        $this->customerGroups   = $customerGroups;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $source = $this->sourceRepository->create();

        $customerGroupIds = $this->customerGroups->getCustomerGroupIds();

        $source->setSourceName('Default source')
            ->setSourceType(SourceInterface::TYPE_VISITOR)
            ->setCustomerGroups($customerGroupIds)
            ->setIsActive(true)
            ->setLastSyncronizedAt(date("Y-m-d H:i:s"));

        $this->sourceRepository->save($source);
    }
}
