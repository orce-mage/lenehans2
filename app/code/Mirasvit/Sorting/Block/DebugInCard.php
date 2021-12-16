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

namespace Mirasvit\Sorting\Block;

use Magento\Backend\Block\Widget\Context;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;

class DebugInCard extends Template
{
    /**
     * @var bool
     */
    private static $isStylesApplied = false;

    /**
     * @var string
     */
    protected $_template = 'Mirasvit_Sorting::debug_in_card.phtml';

    public function __construct(
        Context $context,
        Product $product,
        array $scores,
        array $values,
        array $weights
    ) {
        parent::__construct($context, [
            'scores'  => $scores,
            'values'  => $values,
            'weights' => $weights,
            'product' => $product,
        ]);
    }

    public function isApplyStyles(): bool
    {
        return !self::$isStylesApplied;
    }

    public function getScores(string $type): array
    {
        return $this->getData('scores')[$type];
    }

    public function getWeights(string $type): array
    {
        return $this->getData('weights')[$type];
    }

    public function getValues(string $type): array
    {
        return $this->getData('values')[$type];
    }

    public function getProduct(): Product
    {
        return $this->getData('product');
    }

    public function getMaxScore(string $type): int
    {
        $max = 0;
        foreach ($this->getScores($type) as $name => $score) {
            $max = max($max, $this->getWeights($type)[$name] * $score);
        }

        return $max;
    }

    public function getScore(string $type): string
    {
        $finalScore = 0;
        $weights    = $this->getWeights($type);

        foreach ($this->getScores($type) as $name => $score) {
            if ($score == '-') {
                return $this->getProduct()->getData($name);
            }

            $finalScore += $score * $weights[$name];
        }

        return number_format($finalScore, 3, '.', ' ');
    }

    protected function _toHtml(): string
    {
        $html = parent::_toHtml();

        self::$isStylesApplied = true;

        return $html;
    }
}
