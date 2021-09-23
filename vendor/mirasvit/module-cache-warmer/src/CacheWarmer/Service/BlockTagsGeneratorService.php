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



namespace Mirasvit\CacheWarmer\Service;

use Mirasvit\CacheWarmer\Api\Service\BlockMarkServiceInterface;
use Mirasvit\CacheWarmer\Api\Service\BlockTagsGeneratorServiceInterface;

class BlockTagsGeneratorService implements BlockTagsGeneratorServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getStartReplacerTag($params)
    {
        return BlockMarkServiceInterface::START_REPLACER_TAG_BEGIN
            . $this->getDefinitionHash($params)
            . BlockMarkServiceInterface::START_REPLACER_TAG_END;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinitionHash($params)
    {
        $preparedTemplateFile = explode('templates/', $params[BlockMarkServiceInterface::TEMPLATE_FILE]);
        $trimmedTemplateFile  = (isset($preparedTemplateFile[1]))
            ? $preparedTemplateFile[1] : $params[BlockMarkServiceInterface::TEMPLATE_FILE];
        $hash                 = $params[BlockMarkServiceInterface::BLOCK_CLASS] . BlockMarkServiceInterface::SEPARATOR
            . $trimmedTemplateFile . BlockMarkServiceInterface::SEPARATOR
            . $params[BlockMarkServiceInterface::MODULE_NAME] . BlockMarkServiceInterface::SEPARATOR
            . $params[BlockMarkServiceInterface::TEMPLATE_ADMIN_PATH] . BlockMarkServiceInterface::SEPARATOR
            . $params[BlockMarkServiceInterface::CMS_BLOCK_ID_WIDGET_CODE] . BlockMarkServiceInterface::SEPARATOR
            . $params[BlockMarkServiceInterface::TEMPLATE_BLOCK_CLASS];

        $hash = $this->getHash(true, $hash);

        return $hash;
    }

    /**
     * {@inheritdoc}
     */
    public function getHash($isHash, $value)
    {
        //comment these lines to see original tags
        if ($isHash) {
            $value = bin2hex($value);
        } else {
            $value = hex2bin($value);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getEndReplacerTag($params)
    {
        return BlockMarkServiceInterface::END_REPLACER_TAG_BEGIN
            . $this->getDefinitionHash($params)
            . BlockMarkServiceInterface::END_REPLACER_TAG_END;
    }
}

