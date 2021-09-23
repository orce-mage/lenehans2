<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Filesystem\Driver;

/**
 * Doesn't change anything from \Magento\Framework\Filesystem\Driver\Https
 * but extends \Wyomind\MassStockUpdate\Filesystem\Driver\Http to be able to
 * use the getStatus method
 */
class Https extends \Wyomind\MassStockUpdate\Filesystem\Driver\Http
{
    /**
     * Scheme distinguisher
     *
     * @var string
     */
    public $scheme = 'https';

    /**
     * Parse a https url
     *
     * @param string $path
     * @return array
     */
    protected function parseUrl($path)
    {
        $urlProp = parent::parseUrl($path);

        if (!isset($urlProp['port'])) {
            $urlProp['port'] = 443;
        }

        return $urlProp;
    }

    /**
     * Open a https url
     *
     * @param string $hostname
     * @param int $port
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function open($hostname, $port)
    {
        return parent::open('ssl://' . $hostname, $port);
    }
}
