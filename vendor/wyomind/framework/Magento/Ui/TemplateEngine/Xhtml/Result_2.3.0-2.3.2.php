<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */


namespace Wyomind\Framework\Magento\Ui\TemplateEngine\Xhtml;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\View\Layout\Generator\Structure;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\Template;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Result
 */
class Result_2_3_0_2_3_2 extends \Magento\Ui\TemplateEngine\Xhtml\Result
{
    /**
     * @var Template
     */
    public $template;


    /**
     * @var UiComponentInterface
     */
    public $component;

    /**
     * @var Structure
     */
    public $structure;


    /**
     * @var
     */
    public $structureAsArray;

    /**
     * @var ManagerInterface
     */
    public $eventManager;

    /**
     * Result constructor.
     * @param Template $template
     * @param CompilerInterface $compiler
     * @param UiComponentInterface $component
     * @param Structure $structure
     * @param LoggerInterface $logger
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Template $template,
        CompilerInterface $compiler,
        UiComponentInterface $component,
        Structure $structure,
        LoggerInterface $logger,
        ManagerInterface $eventManager
    ) {
    
        $this->eventManager = $eventManager;
        $construct = "__construct";
        parent::$construct($template, $compiler, $component, $structure, $logger);
    }
    /**
     * Result constructor.
     * @param Template $template
     * @param CompilerInterface $compiler
     * @param UiComponentInterface $component
     * @param Structure $structure
     * @param LoggerInterface $logger
     */


    /**
     * Wrap content
     *
     * @param string $content
     * @return string
     */
    public function wrapContent($content)
    {
        return '<script type="text/x-magento-init"><![CDATA['
            . '{"*": {"Magento_Ui/js/core/app": ' . str_replace(['<![CDATA[', ']]>'], '', $content) . '}}'
            . ']]></script>';
    }


    /**
     * @param \Magento\Ui\TemplateEngine\Xhtml\Result $subject
     * @param callable $proceed
     */
    public function appendLayoutConfiguration()
    {


        $this->structureAsArray = $this->structure->generate($this->component);

        $this->eventManager->dispatch("framework_merge_structure", ["structure" => $this]);


        $layoutConfiguration = $this->wrapContent(

            json_encode($this->structureAsArray, JSON_HEX_TAG)
        );
        $this->template->append($layoutConfiguration);
    }
}
