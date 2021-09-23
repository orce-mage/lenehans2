<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Filesystem\Driver;

/**
 * Origin filesystem driver modified so that we can simply get the result code
 * of an url
 */
class Http extends \Magento\Framework\Filesystem\Driver\Http
{
    public $_status = "";

    /**
     * Rewrites to store the status in a property
     * @param string $path
     * @return boolean
     */
    public function isExists($path)
    {
        $headers = array_change_key_case(get_headers($this->getScheme() . $path, 1), CASE_LOWER);

        $this->_status = $headers[0];

        if (strpos($this->_status, '200 OK') === false) {
            $result = false;
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * Retrieve status of the url
     * Doesn't load the url if not needed
     * @param string $path
     * @return string
     */
    public function getStatus($path = null)
    {
        if ($this->_status == "") {
            if ($path != null) {
                $headers = array_change_key_case(get_headers($this->getScheme() . $path, 1), CASE_LOWER);
                $this->_status = $headers[0];
            }
        }
        return $this->_status;
    }
}
