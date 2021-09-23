<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Model\Template;

use Magetrend\Email\Model\Template;

class TransportBuilderDp extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * Template data
     *
     * @var array
     */
    protected $templateData = [];

    /**
     * Set template data
     *
     * @param array $data
     * @return $this
     */
    public function setTemplateData($data)
    {
        $this->templateData = $data;
        return $this;
    }

    /**
     * @param $template
     */
    protected function setTemplateFilter($template)
    {
        if (isset($this->templateData['template_filter'])) {
            $template->setTemplateFilter($this->templateData['template_filter']);
        }
    }

    /**
     * @inheritdoc
     */
    protected function prepareMessage()
    {
        /** @var AbstractTemplate $template */
        $template = $this->getTemplate()->setData($this->templateData);
        $this->setTemplateFilter($template);

        $this->message
            ->setMessageType(\Magento\Framework\Mail\MessageInterface::TYPE_HTML)
            ->setBodyHtml($this->templateData['template_text'])
            ->setSubject($this->templateData['template_subject']);

        return $this;
    }
}
