<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */


namespace Wyomind\Framework\Magento\Ui\TemplateEngine\Xhtml;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Serialize\Serializer\JsonHexTag;
use Magento\Framework\View\Layout\Generator\Structure;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\Template;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Result
 */
class Result_2_3_3_2_3_5 extends \Magento\Ui\TemplateEngine\Xhtml\Result
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
     * @var JsonHexTag
     */
    public $jsonSerializer;

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
     * @param JsonHexTag|null $jsonSerializer
     */
    public function __construct(
        Template $template,
        CompilerInterface $compiler,
        UiComponentInterface $component,
        Structure $structure,
        LoggerInterface $logger,
        ManagerInterface $eventManager,
        JsonHexTag $jsonSerializer = null
    ) {
    
        $this->eventManager = $eventManager;
        $this->jsonSerializer = $jsonSerializer ?? ObjectManager::getInstance()->get(JsonHexTag::class);
        $construct = "__construct";
        parent::$construct($template, $compiler, $component, $structure, $logger, $jsonSerializer);
    }
    /**
     * Result constructor.
     * @param Template $template
     * @param CompilerInterface $compiler
     * @param UiComponentInterface $component
     * @param Structure $structure
     * @param LoggerInterface $logger
     * @param JsonHexTag|null $jsonSerializer
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
            $this->jsonSerializer->serialize($this->structureAsArray)
        );
        $this->template->append($layoutConfiguration);
    }
}
