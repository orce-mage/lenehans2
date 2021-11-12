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

namespace Mirasvit\Finder\Block\Widget;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Mirasvit\Finder\Block\Finder;

class FinderWidget extends Template implements BlockInterface, IdentityInterface
{
    /**
     * @var string
     */
    protected $_template = 'Mirasvit_Finder::widget/finder-widget.phtml';

    public function getFinderBlock(): ?Finder
    {
        /** @var Finder $block */
        $block = $this->_layout->createBlock(Finder::class, null, [
            'data' => $this->getData(),
        ]);

        return $block;
    }

    public function getIdentities()
    {
        return [];
    }
}
