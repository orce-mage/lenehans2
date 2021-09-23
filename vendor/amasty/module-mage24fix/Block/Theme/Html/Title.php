<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mage24Fix
 */


declare(strict_types=1);

namespace Amasty\Mage24Fix\Block\Theme\Html;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Block\Html\Title as MagentoTitle;

/**
 * Class Title
 *
 * Fix fatal with plugin to title block only for 2.4.0 version - https://github.com/magento/magento2/issues/28981
 */
class Title extends MagentoTitle
{
    /**
     * Config path to 'Translate Title' header settings
     */
    const XML_PATH_HEADER_TRANSLATE_TITLE = 'design/header/translate_title';
}
