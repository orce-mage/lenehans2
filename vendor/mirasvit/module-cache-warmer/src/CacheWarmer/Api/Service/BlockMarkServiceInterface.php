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
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Api\Service;


interface BlockMarkServiceInterface
{
    const START_REPLACER_TAG_BEGIN = '<!--[m_unique_tag_start_begin';
    const START_REPLACER_TAG_END   = 'm_unique_tag_start_end]-->';
    const END_REPLACER_TAG_BEGIN   = '<!--[m_unique_tag_end_begin';
    const END_REPLACER_TAG_END     = 'm_unique_tag_end_end]-->';
    const HTML_NAME_PATTERN        = '/<!--\[m_unique_tag_start_begin(.*?)m_unique_tag_start_end\]-->/i';
    const SEPARATOR                = '___';

    const BLOCK_CLASS              = 'block_class';
    const MODULE_NAME              = 'module_name';
    const TEMPLATE_FILE            = 'template_file';
    const TEMPLATE_BLOCK_CLASS     = 'template_block_class';
    const TEMPLATE_ADMIN_PATH      = 'template_admin_path';
    const CMS_BLOCK_ID_WIDGET_CODE = 'cms_block_id';
    const WIDGET_TYPE              = 'widget type';

    /**
     * @param array  $markParams
     * @param string $result
     * @return string
     */
    public function markBlocks($markParams, $result);
}