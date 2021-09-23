<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\SchemaReader;

use Amasty\ImportCore\SchemaReader\Config\Reader;
use Magento\Framework\Config\CacheInterface;

class Config extends \Magento\Framework\Config\Data
{
    const CACHE_ID = 'amasty_import';

    /**
     * Initialize reader and cache.
     *
     * @param Reader $reader
     * @param CacheInterface $cache
     */
    public function __construct(
        Reader $reader,
        CacheInterface $cache
    ) {
        parent::__construct($reader, $cache, self::CACHE_ID);
    }
}
