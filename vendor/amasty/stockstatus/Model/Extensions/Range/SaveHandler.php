<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Extensions\Range;

use Amasty\Stockstatus\Api\RangeRepositoryInterface;
use Amasty\Stockstatus\Model\Rule;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\LocalizedException;

class SaveHandler implements ExtensionInterface
{
    /**
     * @var RangeRepositoryInterface
     */
    private $rangeRepository;

    public function __construct(RangeRepositoryInterface $rangeRepository)
    {
        $this->rangeRepository = $rangeRepository;
    }

    /**
     * @param Rule|object $entity
     * @param array $arguments
     * @return Rule|bool|object|void
     * @throws LocalizedException
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->isActivateQtyRanges()) {
            $extensionAttributes = $entity->getExtensionAttributes();
            $ranges = $extensionAttributes->getRanges() ?: [];
            foreach ($ranges as $range) {
                $range->setRuleId((int) $entity->getId());
                $this->rangeRepository->save($range);
            }
        } else {
            $this->rangeRepository->removeByRuleId((int) $entity->getId());
        }

        return $entity;
    }
}
