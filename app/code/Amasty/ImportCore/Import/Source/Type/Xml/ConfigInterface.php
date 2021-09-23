<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Source\Type\Xml;

interface ConfigInterface
{
    /**
     * @return string
     */
    public function getItemPath();

    /**
     * @param string $itemPath
     *
     * @return void
     */
    public function setItemPath($itemPath);
}
