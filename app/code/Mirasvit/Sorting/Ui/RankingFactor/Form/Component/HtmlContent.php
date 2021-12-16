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
namespace Mirasvit\Sorting\Ui\RankingFactor\Form\Component;

class HtmlContent extends \Magento\Ui\Component\HtmlContent
{
    /**
     * Compatibility with 2.1.x
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    public function getBlock()
    {
        return $this->block;
    }
}
