<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\MbFrame\Model\Config\Structure;

use Magento\Framework\Serialize\SerializerInterface;

class Data extends \Magento\Framework\Config\Data\Scoped
{
    /**
     * Data constructor.
     * @param Reader                                   $reader
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param SerializerInterface|null                 $serializer
     */
    public function __construct(
        Reader $reader,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Magento\Framework\Config\CacheInterface $cache,
        SerializerInterface $serializer = null
    ) {
        $code = 'MageBig_System';
        $uri = $_SERVER['REQUEST_URI'];
        $params = explode('/', $uri);
        for ($i = 0; $i < count($params); ++$i) {
            if ($params[$i] == 'theme_id') {
                $code .= ucwords($params[$i + 1]);
                break;
            }
        }
        $cacheId = $code;
        parent::__construct($reader, $configScope, $cache, $cacheId, $serializer);
    }

    /**
     * Merge additional config.
     *
     * @param array $config
     */
    public function merge(array $config)
    {
        if (isset($config['config']['system'])) {
            $config = $config['config']['system'];
        }
        parent::merge($config);
    }
}
