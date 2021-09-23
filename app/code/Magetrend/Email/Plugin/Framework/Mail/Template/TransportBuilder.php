<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Plugin\Framework\Mail\Template;

class TransportBuilder
{
    public $templateVarManager;

    public function __construct(
        \Magetrend\Email\Model\TemplateVarManager $templateVarManager
    ) {
        $this->templateVarManager = $templateVarManager;
    }

    public function beforeSetTemplateVars($subject, $vars)
    {
        if (!isset($vars['mtVar'])) {
            $this->templateVarManager->reset();
            $this->templateVarManager->setVariables($vars);
            $vars['mtVar'] = $this->templateVarManager;
        }

        return [$vars];
    }
}
