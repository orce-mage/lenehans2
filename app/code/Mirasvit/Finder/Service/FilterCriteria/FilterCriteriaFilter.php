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
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Finder\Service\FilterCriteria;

final class FilterCriteriaFilter
{
    private $filterId;

    private $optionIds;

    private $path;

    public function __construct(
        int $filterId,
        array $optionIds,
        string $path
    ) {
        $this->filterId  = $filterId;
        $this->optionIds = $optionIds;
        $this->path      = $path;
    }

    public function getFilterId(): int
    {
        return $this->filterId;
    }

    public function getOptionIds(): array
    {
        return $this->optionIds;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
