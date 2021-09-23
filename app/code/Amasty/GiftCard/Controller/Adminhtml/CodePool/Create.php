<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Controller\Adminhtml\CodePool;

use Amasty\GiftCard\Controller\Adminhtml\AbstractCodePool;

class Create extends AbstractCodePool
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
