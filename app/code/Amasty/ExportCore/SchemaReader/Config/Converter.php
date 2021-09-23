<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\SchemaReader\Config;

use Magento\Framework\Config\ConverterInterface;
use Magento\Framework\ObjectManager\Config\Mapper\ArgumentParser;
use Magento\Framework\Stdlib\BooleanUtils;

class Converter implements ConverterInterface
{
    /**
     * @var ArgumentParser
     */
    private $argumentParser;

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    public function __construct(
        ArgumentParser $argumentParser,
        BooleanUtils $booleanUtils
    ) {
        $this->argumentParser = $argumentParser;
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * @param \DOMDocument $source
     *
     * @return array
     */
    public function convert($source)
    {
        $output = [];
        if (!$source instanceof \DOMDocument) {
            return $output;
        }

        /** @var \DOMNodeList $entities */
        $entities = $source->getElementsByTagName('entity');
        /** @var \DOMElement $entity */
        foreach ($entities as $entity) {
            $entityCode = $entity->getAttribute('code');
            $output[$entityCode] = [];
            foreach ($entity->childNodes as $entityNode) {
                if ($entityNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                switch ($entityNode->tagName) {
                    case 'name':
                    case 'description':
                    case 'group':
                        $output[$entityCode][$entityNode->tagName] = $entityNode->nodeValue;
                        break;
                    case 'enabledChecker':
                    case 'collectionFactory':
                    case 'collectionModifier':
                        $output[$entityCode][$entityNode->tagName] = $this->readClass($entityNode);
                        break;
                    case 'isHidden':
                        $output[$entityCode][$entityNode->tagName] = $this->booleanUtils->toBoolean(
                            $entityNode->nodeValue
                        );
                        break;
                    case 'fieldsConfig':
                        $output[$entityCode][$entityNode->tagName] = $this->readFieldsConfig($entityNode);
                        break;
                }
            }
        }

        foreach ($this->convertRelations($source) as $entity => $relationConfig) {
            if (isset($output[$entity])) { // Just in case do not merge relations if entity does not exist
                $output[$entity]['relations'] = $relationConfig;
            }
        }

        return $output;
    }

    protected function convertRelations(\DOMDocument $source): array
    {
        $output = [];

        /** @var \DOMNodeList $relations */
        $relations = $source->getElementsByTagName('relation');
        foreach ($relations as $relation) {
            if ($relation->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $relationConfig = $this->convertRelation($relation);
            $parentEntity = $relationConfig['parent_entity'];
            if (!isset($output[$parentEntity])) {
                $output[$parentEntity] = [];
            }
            $output[$parentEntity] [] = $relationConfig;
        }

        return $output;
    }

    protected function convertRelation(\DOMElement $relation): array
    {
        $relationConfig = [];
        foreach ($relation->childNodes as $relationNode) {
            if ($relationNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            switch ($relationNode->tagName) {
                case 'arguments':
                    $relationConfig[$relationNode->tagName] = $this->getClassArguments($relation);
                    break;
                default:
                    $relationConfig[$relationNode->tagName] = $relationNode->nodeValue;
            }
        }

        return $relationConfig;
    }

    protected function readClass(\DOMElement $node): array
    {
        $class = ['class' => $node->getAttribute('class')];
        foreach ($node->childNodes as $classNode) {
            if ($classNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            switch ($classNode->tagName) {
                case 'arguments':
                    $class[$classNode->tagName] = $this->getClassArguments($node);
                    break;
                default:
                    $class[$classNode->tagName] = $classNode->nodeValue;
            }
        }

        return $class;
    }

    public function readFieldsConfig(\DOMNode $node) : array
    {
        $result = [];

        if ($node->hasChildNodes()) {
            $result = [];
            /**
             * @var \DomNode $fieldConfigNode
             */
            foreach ($node->childNodes as $fieldConfigNode) {
                if ($fieldConfigNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                switch ($fieldConfigNode->tagName) {
                    case 'fields':
                        if ($fieldConfigNode->hasAttribute('rowActionClass')) {
                            $result['rowActionClass'] = $fieldConfigNode->getAttribute(
                                'rowActionClass'
                            );
                        }

                        $result['fields'] = $this->readFields($fieldConfigNode);
                        break;
                    case 'virtualFields':
                        $result['virtualFields'] = $this->readVirtualFields($fieldConfigNode);
                        break;
                    case 'fieldsClass':
                        $result['fieldsClass'] = [
                            'class' => $fieldConfigNode->getAttribute('class'),
                            'arguments' => []
                        ];
                        if ($arguments = $this->getClassArguments($fieldConfigNode)) {
                            $result['fieldsClass']['arguments'] = $arguments;
                        }
                        break;
                }
            }
        }

        return $result;
    }

    public function readFields(\DOMNode $node): array
    {
        $result = [];

        if ($node->hasChildNodes()) {
            $result = [];
            /**
             * @var \DomNode $field
             */
            foreach ($node->childNodes as $field) {
                if ($field->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                $result[$field->getAttribute('name')] = $this->readField($field);
            }
        }

        return $result;
    }

    public function readField(\DOMNode $node): array
    {
        if (!$node->hasChildNodes()) {
            return [];
        }

        $result = [];
        foreach ($node->childNodes as $fieldNode) {
            if ($fieldNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            switch ($fieldNode->tagName) {
                case 'map':
                    $result[$fieldNode->tagName] = $fieldNode->nodeValue;
                    break;
                case 'filterClass':
                    $result[$fieldNode->tagName] = $this->readFieldFilterClass($fieldNode);
                    break;
                case 'filter':
                    $result['filter'] = $this->readFieldFilter($fieldNode);
                    break;
                case 'actions':
                    $result['actions'] = $this->readActions($fieldNode);
                    break;
                case 'remove':
                    $result['remove'] = $this->booleanUtils->toBoolean(
                        $fieldNode->nodeValue
                    );
                    break;
            }
        }

        return $result;
    }

    public function readVirtualFields(\DOMNode $node): array
    {
        $result = [];

        if ($node->hasChildNodes()) {
            $result = [];
            /**
             * @var \DomNode $field
             */
            foreach ($node->childNodes as $field) {
                if ($field->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                $result[$field->getAttribute('name')] = $this->readVirtualField($field);
            }
        }

        return $result;
    }

    public function readFieldFilterClass(\DOMNode $node): array
    {
        $result = [];

        if ($node->hasChildNodes()) {
            $result = [
                'type' => $node->getAttribute('type')
            ];
            /**
             * @var \DomNode $filterClassNode
             */
            foreach ($node->childNodes as $filterClassNode) {
                if ($filterClassNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                switch ($filterClassNode->tagName) {
                    case 'class':
                    case 'metaClass':
                        $result[$filterClassNode->tagName] = [
                            'class' => $filterClassNode->getAttribute('name'),
                            'arguments' => []
                        ];
                        if ($arguments = $this->getClassArguments($filterClassNode)) {
                            $result[$filterClassNode->tagName]['arguments'] = $arguments;
                        }
                        break;
                }
            }
        }

        return $result;
    }

    public function readVirtualField(\DOMNode $node): array
    {
        if (!$node->hasChildNodes()) {
            return [];
        }

        $result = [];
        foreach ($node->childNodes as $fieldNode) {
            if ($fieldNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            switch ($fieldNode->tagName) {
                case 'label':
                    $result['label'] = $fieldNode->nodeValue;
                    break;
                case 'generator':
                case 'collectionModifier':
                    $result[$fieldNode->tagName] = [
                        'class'     => $fieldNode->getAttribute('class'),
                        'arguments' => $arguments = $this->getClassArguments($fieldNode)
                    ];
                    break;
            }
        }

        return $result;
    }

    public function readFieldFilter(\DOMNode $node): array
    {
        if (!$node->hasChildNodes()) {
            return [];
        }

        $result = [];
        /**
         * @var \DomNode $action
         */
        foreach ($node->childNodes as $filter) {
            if ($filter->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            switch ($filter->tagName) {
                case 'options':
                    $result['options'] = $this->readFilterOptions($filter);
                    break;
                default:
                    $result[$filter->tagName] = $filter->nodeValue;
                    break;
            }
        }

        return $result;
    }

    public function readFilterOptions(\DOMNode $node): array
    {
        if (!$node->hasChildNodes()) {
            return [];
        }

        $result = [];
        $optionCounter = 0;
        /**
         * @var \DomNode $action
         */
        foreach ($node->childNodes as $filterOptions) {
            if ($filterOptions->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            if ($filterOptions->tagName == 'class') {
                $result['class'] = [
                    'name' => 'class',
                    'xsi:type' => 'object',
                    'value' => $filterOptions->nodeValue
                ];

                return $result;
            }
            $option = [];
            foreach ($filterOptions->childNodes as $filterOption) {
                if ($filterOption->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                $option[] = [
                    'name' => $filterOption->tagName,
                    'xsi:type' => 'string',
                    'value' => $filterOption->nodeValue
                ];
            }
            if (empty($result)) {
                $result = [
                    'name' => 'options',
                    'xsi:type' => 'array',
                    'item' => []
                ];
            }
            $result['item'][] = [
                'name' => 'option' . ($optionCounter++),
                'xsi:type' => 'array',
                'item' => $option
            ];
        }

        return $result;
    }

    public function readActions(\DOMNode $node): array
    {
        if (!$node->hasChildNodes()) {
            return [];
        }

        $result = [];
        /**
         * @var \DomNode $action
         */
        foreach ($node->childNodes as $action) {
            if ($action->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            $result[] = [
                'class' => $action->getAttribute('class'),
                'name' => $action->getAttribute('name'),
                'arguments' => []
            ];

            $index = count($result) - 1;
            foreach ($action->attributes as $attribute) {
                if (!in_array($attribute->nodeName, ['class', 'name', 'preselected'])) {
                    $result[$index]['arguments'][$attribute->nodeName] = $attribute->nodeValue;
                } elseif ($attribute->nodeName == 'preselected') {
                    $result[$index]['arguments'][$attribute->nodeName] = [
                        'name' => $attribute->nodeName,
                        'xsi:type' => 'boolean',
                        'value' => $this->booleanUtils->toBoolean($attribute->nodeValue)
                    ];
                }
            }

            if ($arguments = $this->getClassArguments($action)) {
                $result[$index]['arguments'] = $arguments;
            }
        }

        return $result;
    }

    public function getClassArguments(\DOMNode $node): array
    {
        if (!$node->hasChildNodes()) {
            return [];
        }

        $result = [];
        foreach ($node->childNodes as $argumentsNode) {
            if ($argumentsNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            if ($argumentsNode->tagName === 'arguments' && $argumentsNode->hasChildNodes()) {
                $result = $this->parseArguments($argumentsNode->childNodes);
                break;
            }
        }

        return $result;
    }

    public function parseArguments(\DOMNodeList $node): array
    {
        $result = [];
        foreach ($node as $argument) {
            if ($argument->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $argumentName = $argument->attributes->getNamedItem('name')->nodeValue;
            $result[$argumentName] = $this->argumentParser->parse($argument);
        }

        return $result;
    }
}
