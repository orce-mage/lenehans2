<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

namespace Magefan\WysiwygAdvanced\Block\Adminhtml\System\Config\Form;

use Magento\Store\Model\ScopeInterface;

/**
 * Admin blog configurations information block
 */
class Info extends \Magefan\Community\Block\Adminhtml\System\Config\Form\Info
{
    /**
     * Return extension url
     * @return string
     */
    protected function getModuleUrl()
    {
        return 'https://mage' . 'fan.com?aff=8d5e957f297893487bd98fa830fa6413';
    }

    /**
     * Return extension title
     * @return string
     */
    protected function getModuleTitle()
    {
        return 'WYSIWYG Advanced Extension';
    }
}
