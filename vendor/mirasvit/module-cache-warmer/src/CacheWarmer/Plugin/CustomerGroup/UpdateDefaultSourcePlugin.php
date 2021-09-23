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




namespace Mirasvit\CacheWarmer\Plugin\CustomerGroup;


use Magento\Customer\Api\Data\GroupInterface;
use Mirasvit\CacheWarmer\Model\Config\Source\CustomerGroups;
use Mirasvit\CacheWarmer\Repository\SourceRepository;

class UpdateDefaultSourcePlugin
{
    private $sourceRepository;

    private $customerGroups;

    public function __construct(
        SourceRepository $sourceRepository,
        CustomerGroups $customerGroups
    ) {
        $this->sourceRepository = $sourceRepository;
        $this->customerGroups   = $customerGroups;
    }

    /**
     * @param GroupInterface $subject
     * @param GroupInterface $customerGroup
     *
     * @return GroupInterface
     */
    public function afterSave($subject, $customerGroup)
    {
        $this->updateDefaultSource();

        return $customerGroup;
    }

    /**
     * @param GroupInterface $subject
     * @param bool $result
     *
     * @return bool
     */
    public function afterDelete($subject, $result)
    {
        $this->updateDefaultSource();

        return $result;
    }

    private function updateDefaultSource()
    {
        $defaultSource = $this->sourceRepository->get(1);

        $defaultSource->setCustomerGroups($this->customerGroups->getCustomerGroupIds());

        $this->sourceRepository->save($defaultSource);
    }
}
