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

namespace Mirasvit\Finder\Block\Filter;

use Magento\Framework\View\LayoutInterface;
use Mirasvit\Finder\Api\Data\FilterInterface;

class FilterBlockFactory
{
    private $layout;

    public function __construct(
        LayoutInterface $layout
    ) {
        $this->layout = $layout;
    }

    public function create(FilterInterface $filter): AbstractFilter
    {
        switch ($filter->getDisplayMode()) {
            case FilterInterface::DISPLAY_MODE_LABEL:
                $filterClass = LabelFilter::class;
                break;

            default:
            case FilterInterface::DISPLAY_MODE_DROPDOWN:
                $filterClass = DropdownFilter::class;
        }

        /** @var AbstractFilter $block */
        $block = $this->layout->createBlock($filterClass);
        $block->setFilter($filter);

        return $block;
    }
}
