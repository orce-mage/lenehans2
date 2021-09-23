<?php

namespace Swissup\SoldTogether\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Helper\AbstractHelper;

class Suggestpage extends AbstractHelper
{
    /**
     * Get slides per view for "Customers Also Bought..." in Ajaxpro popup
     * depending of its dialog type.
     *
     * @return int
     */
    public function getSlidesPerView()
    {
        if ($this->_moduleManager->isEnabled('Swissup_Ajaxpro')) {
            $ajaxproConfigHelper = ObjectManager::getInstance()->get(
                \Swissup\Ajaxpro\Helper\Config::class
            );
            if ($ajaxproConfigHelper->getCartDialogType() == 'slide') {
                return 2;
            }
        }

        return 3;
    }
}
