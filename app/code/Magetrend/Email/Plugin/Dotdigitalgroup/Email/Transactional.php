<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Plugin\Dotdigitalgroup\Email;

class Transactional
{
    public function aroundIsDotmailerTemplate($subject, $parent, $templateCode)
    {
        if (strpos($templateCode, 'tmp_template_') !== false) {
            return false;
        }

        return $parent($templateCode);
    }
}
