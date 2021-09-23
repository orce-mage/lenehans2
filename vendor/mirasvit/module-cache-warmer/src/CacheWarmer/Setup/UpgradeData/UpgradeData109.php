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
use Mirasvit\CacheWarmer\Api\Repository\WarmRuleRepositoryInterface;

class UpgradeData109 implements UpgradeDataInterface
{
    /**
     * @var WarmRuleRepositoryInterface
     */
    private $warmRuleRepository;

    /**
     * UpgradeData109 constructor.
     * @param WarmRuleRepositoryInterface $warmRuleRepository
     */
    public function __construct(
        WarmRuleRepositoryInterface $warmRuleRepository
    ) {
        $this->warmRuleRepository = $warmRuleRepository;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $rule = $this->warmRuleRepository->create();

        $rule->setName('Default Rule')
            ->setIsActive(true)
            ->setPriority(1);

        $this->warmRuleRepository->save($rule);
    }
}
