<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Filter;

use Amasty\ExportCore\Api\Filter\FilterInterface;
use Magento\Framework\ObjectManagerInterface;

class FilterProvider
{
    protected $filters = [];

    /**
     * @var FilterConfig
     */
    private $filterConfig;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        FilterConfig $filterConfig,
        ObjectManagerInterface $objectManager
    ) {
        $this->filterConfig = $filterConfig;
        $this->objectManager = $objectManager;
    }

    public function getFilter(string $type): FilterInterface
    {
        if (isset($this->filters[$type])) {
            return $this->filters[$type];
        }

        $filterClass = $this->filterConfig->get($type)['filterClass'];

        if (!is_subclass_of($filterClass, FilterInterface::class)) {
            throw new \RuntimeException('Wrong filter class: "' . $filterClass);
        }

        return $this->objectManager->create($filterClass);
    }
}
