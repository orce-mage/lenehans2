<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Model\Config\Source;

class Design implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magetrend\Email\Model\TemplateManager
     */
    public $templateManager;

    /**
     * Design constructor.
     * @param \Magetrend\Email\Model\TemplateManager $templateManager
     */
    public function __construct(
        \Magetrend\Email\Model\TemplateManager $templateManager
    ) {
        $this->templateManager = $templateManager;
    }
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $key => $value) {
            $options[] = ['value' => $key, 'label'=> $value];
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $options = [];
        $designList = $this->templateManager->getDesignList();

        foreach ($designList as $design) {
            $options[$design['value']] = $design['label'];
        }

        return $options;
    }
}
