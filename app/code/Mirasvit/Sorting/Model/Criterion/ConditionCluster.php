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

namespace Mirasvit\Sorting\Model\Criterion;

use Magento\Framework\App\ObjectManager;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Model\Config\Source\SortByAttributeSource;
use Mirasvit\Sorting\Model\Config\Source\SortByRankingFactorSource;

/**
 * ConditionCluster
 *  - ConditionFrame
 *      - ConditionNode
 *      - ConditionNode
 *      - ConditionNode
 *  - ConditionFrame
 *      - ConditionNode
 */
class ConditionCluster
{
    /** @var ConditionFrame[]  */
    private $frames = [];

    public function loadArray(array $data): self
    {
        foreach ($data as $frameData) {
            $frame = new ConditionFrame();
            $frame->loadArray($frameData);

            $this->addFrame($frame);
        }

        return $this;
    }

    public function toArray(): array
    {
        $data = [];

        foreach ($this->getFrames() as $frame) {
            $data[] = $frame->toArray();
        }

        return $data;
    }

    /** @return ConditionFrame[] */
    public function getFrames(): array
    {
        return $this->frames;
    }

    public function addFrame(ConditionFrame $frame): self
    {
        $this->frames[] = $frame;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return string
     */
    public function toHtml(): string
    {
        $ob = ObjectManager::getInstance();

        /** @var SortByAttributeSource $attributeSource */
        $attributeSource = $ob->create(SortByAttributeSource::class);

        /** @var SortByRankingFactorSource $factorSource */
        $factorSource = $ob->create(SortByRankingFactorSource::class);

        $lines = [];
        foreach ($this->getFrames() as $fIdx => $frame) {
            foreach ($frame->getNodes() as $idx => $node) {
                if ($node->getSortBy() === CriterionInterface::CONDITION_SORT_BY_ATTRIBUTE) {
                    $label = $node->getAttribute();
                    foreach ($attributeSource->toOptionArray() as $option) {
                        if ($option['value'] == $node->getAttribute()) {
                            $label = $option['label'];
                        }
                    }
                } else {
                    $label = $node->getRankingFactor();
                    foreach ($factorSource->toOptionArray() as $option) {
                        if ($option['value'] == $node->getRankingFactor()) {
                            $label = $option['label'];
                        }
                    }
                }

                $svgBefore = '<svg class="before"><polyline points="12 0 0 0 8 12 0 24 12 24"></polyline><line stroke-width="2" x1="0" x2="12" y1="0" y2="0"></line><line stroke-width="2" x1="0" x2="12" y1="24" y2="24"></line></svg>';
                $svgAfter  = '<svg class="after"><polyline points="0 0 4 0 12 12 4 24 0 24"></polyline><line stroke-width="2" x1="0" x2="4" y1="0" y2="0"></line><line stroke-width="2" x1="0" x2="4" y1="24" y2="24"></line></svg>';

                $lines[] = '<div class="' . $node->getSortBy() . '">' . $svgBefore . '<div>' . __(
                        '<b>%2</b> <small>%3</small>',
                        $fIdx === 0 ? '' : 'Then ',
                        $label,
                        $node->getDirection() === 'desc' ? 'Z-A 9-0' : 'A-Z 0-9'
                    ) . '</div>' . $svgAfter . '</div>';
            }
        }

        return '<div class="mst-sorting-criterion-listing__cluster-html">' . implode('', $lines) . '</div>';
    }
}
