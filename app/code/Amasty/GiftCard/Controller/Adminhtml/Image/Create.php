<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Controller\Adminhtml\Image;

use Amasty\GiftCard\Controller\Adminhtml\AbstractImage;

class Create extends AbstractImage
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
