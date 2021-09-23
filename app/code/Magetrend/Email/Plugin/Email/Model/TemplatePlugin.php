<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Plugin\Email\Model;

class TemplatePlugin
{
    public $registry;

    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    public function afterGetSubject(
        \Magento\Email\Model\Template $subject,
        $result
    ) {
        return htmlspecialchars_decode((string)$result, ENT_QUOTES);
    }

    public function afterGetProcessedTemplateSubject(
        \Magento\Email\Model\Template $subject,
        $result
    ) {
        return htmlspecialchars_decode((string)$result, ENT_QUOTES);
    }

    public function afterSave($results)
    {
        $this->registry->unregister('dotmailer_saving_data');
        return $results;
    }
}
