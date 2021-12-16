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
 * @package   mirasvit/module-sorting
 * @version   1.1.14
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);


namespace Mirasvit\Sorting\Service;


use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Model\ConfigProvider;

class DebugService
{
    /** @var array */
    public static $log = [];

    /** @var CriterionInterface|null */
    public static $currCriterion = null;

    private       $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public static function logCollection(AbstractCollection $collection = null): void
    {
        $class = get_class($collection);

        if (
            strpos($class, 'Configurable') !== false
            || strpos($class, 'Bundle') !== false
            || strpos($class, 'Link') !== false
        ) {
            return; //prevent logging inner collection
        }

        $arr = array_filter(self::$log, function ($item) use ($collection) {
            return $item->getSelect()->assemble() == $collection->getSelect()->assemble();
        });

        if (count($arr)) {
            return; //prevent duplicates
        }

        self::$log[] = $collection;
    }

    public static function setCurrentCriterion(CriterionInterface $criterion = null): void
    {
        self::$currCriterion = $criterion;
    }

    public function getCurrentCriterion(): ?CriterionInterface
    {
        return self::$currCriterion;
    }

    public function isEnabled(): bool
    {
        return $this->configProvider->isDebug();
    }

    public function getLogs(): array
    {
        return self::$log;
    }
}
