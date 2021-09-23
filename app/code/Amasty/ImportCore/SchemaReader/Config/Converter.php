<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\SchemaReader\Config;

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
                    case 'group':
                    case 'description':
                        $output[$entityCode][$entityNode->tagName] = $entityNode->nodeValue;
                        break;
                    case 'isHidden':
                        $output[$entityCode][$entityNode->tagName] = $this->booleanUtils->toBoolean(
                            $entityNode->nodeValue
                        );
                        break;
                    case 'indexer':
                        $output[$entityCode][$entityNode->tagName] = [
                            'class' => $entityNode->getAttribute('class'),
                            'apply_type' => $entityNode->getAttribute('apply')
                        ];
                        break;
                    case 'fileUploader':
                        $output[$entityCode][$entityNode->tagName] = [
                            'class' => $entityNode->getAttribute('class'),
                            'storage_path' => $entityNode->getAttribute('storagePath')
                        ];
                        break;
                    case 'behaviors':
                        $output[$entityCode][$entityNode->tagName] = $this->readBehaviors($entityNode);
                        break;
                    case 'fieldsConfig':
                        $output[$entityCode][$entityNode->tagName] = $this->readFieldsConfig($entityNode);
                        break;
                    case 'importEvents':
                        $output[$entityCode][$entityNode->tagName] = $this->readImportEvents($entityNode);
                        break;
                    case 'enabledChecker':
                        $output[$entityCode][$entityNode->tagName] = $this->readClass($entityNode);
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

    public function readBehaviors(\DOMNode $node): array
    {
        if (!$node->hasChildNodes()) {
            return [];
        }

        $result = [];
        /**
         * @var \DomNode $behavior
         */
        foreach ($node->childNodes as $behavior) {
            if ($behavior->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            $behaviorCode = $behavior->getAttribute('code');
            $result[$behaviorCode] = [
                'class' => $behavior->getAttribute('class'),
                'name' => $behavior->getAttribute('name'),
                'arguments' => []
            ];
            if ($behavior->getAttribute('indexerMethod')) {
                $result[$behaviorCode]['indexerMethod'] = $behavior->getAttribute('indexerMethod');
            }
            if ($arguments = $this->getClassArguments($behavior)) {
                $result[$behaviorCode]['arguments'] = $arguments;
            }
            foreach ($this->readBehaviorData($behavior) as $key => $behaviorData) {
                if ($key == 'executeOnCodes') {
                    $result[$behaviorCode][$key] = $behaviorData;
                } else {
                    $result[$behaviorCode]['arguments'][$key] = $behaviorData;
                }
            }
        }

        return $result;
    }

    public function readBehaviorData(\DOMNode $behavior): array
    {
        $result = [];

        if ($behavior->hasChildNodes()) {
            foreach ($behavior->childNodes as $behaviorData) {
                if ($behaviorData->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                switch ($behaviorData->tagName) {
                    case 'modelFactory':
                    case 'scopeType':
                    case 'scopeIdentifier':
                    case 'entityDataInterface':
                    case 'insertResourceModel':
                    case 'eavEntityType':
                    case 'entityTable':
                    case 'idField':
                    case 'entityType':
                    case 'tableName':
                        $result[$behaviorData->tagName] = [
                            'name' => $behaviorData->tagName,
                            'xsi:type' => 'string',
                            'value' => $behaviorData->nodeValue
                        ];
                        break;
                    case 'repository':
                        $result['repository'] = $this->readBehaviorRepository($behaviorData);
                        break;
                    case 'executeOnParent':
                        $result['executeOnCodes'] = $this->readBehaviorExecuteOnCodes($behaviorData);
                        break;
                    case 'events':
                        $result['events'] = $this->readBehaviorEvents($behaviorData);
                        break;
                }
            }
        }

        return $result;
    }

    public function readBehaviorRepository(\DomNode $behaviorData): array
    {
        $result = [
            'name' => 'repository',
            'xsi:type' => 'array',
            'item' => []
        ];

        foreach ($behaviorData->childNodes as $repositoryData) {
            if ($repositoryData->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            switch ($repositoryData->tagName) {
                case 'class':
                case 'saveMethod':
                case 'loadMethod':
                case 'deleteMethod':
                    $result['item'][$repositoryData->tagName] = [
                        'name' => $repositoryData->tagName,
                        'xsi:type' => 'string',
                        'value' => $repositoryData->nodeValue
                    ];
                    break;
            }
        }

        return $result;
    }

    protected function readBehaviorExecuteOnCodes(\DomNode $behaviorData): array
    {
        if (!$behaviorData->hasChildNodes()) {
            return [];
        }

        $result = [];
        foreach ($behaviorData->childNodes as $executeOnBehaviorData) {
            if ($executeOnBehaviorData->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $result[] = $executeOnBehaviorData->getAttribute('code');
        }

        return $result;
    }

    protected function readBehaviorEvents(\DomNode $behaviorData): array
    {
        $result = [
            'name' => 'events',
            'xsi:type' => 'array',
            'item' => []
        ];

        foreach ($behaviorData->childNodes as $eventTypeData) {
            if ($eventTypeData->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            switch ($eventTypeData->tagName) {
                case 'beforeApply':
                case 'afterApply':
                    $result['item'][$eventTypeData->tagName] = [
                        'name' => $eventTypeData->tagName,
                        'xsi:type' => 'array',
                        'item' => $this->readBehaviorEventObservers($eventTypeData)
                    ];
                    break;
            }
        }

        return $result;
    }

    protected function readBehaviorEventObservers(\DomNode $behaviorData): array
    {
        if (!$behaviorData->hasChildNodes()) {
            return [];
        }

        $result = [];
        $index = 0;
        foreach ($behaviorData->childNodes as $eventObserverData) {
            if ($eventObserverData->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            if ($eventObserverData->tagName == 'observer') {
                $result[] = [
                    'name' => $index++,
                    'xsi:type' => 'array',
                    'item' => [
                        [
                            'name' => 'class',
                            'xsi:type' => 'string',
                            'value' => $eventObserverData->getAttribute('class')
                        ]
                    ]
                ];
            }
        }

        return $result;
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
                    case 'sampleData':
                        $result['sampleData'] = $this->readSampleData($fieldConfigNode);
                        break;
                    case 'rowValidation':
                        $result['rowValidation'] = $this->readBehaviorParams($fieldConfigNode);
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
        $result = ['isIdentity' => $this->booleanUtils->toBoolean($node->getAttribute('isIdentity'))];

        if (!$node->hasChildNodes()) {
            return $result;
        }

        foreach ($node->childNodes as $fieldNode) {
            if ($fieldNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            switch ($fieldNode->tagName) {
                case 'map':
                    $result['map'] = $fieldNode->nodeValue;
                    break;
                case 'isFile':
                    $result['isFile'] = $this->booleanUtils->toBoolean(
                        $fieldNode->nodeValue
                    );
                    break;
                case 'remove':
                    $result['remove'] = $this->booleanUtils->toBoolean(
                        $fieldNode->nodeValue
                    );
                    break;
                case 'actions':
                    $result['actions'] = $this->readActions($fieldNode);
                    break;
                case 'validation':
                    $result['validation'] = $this->readValidation($fieldNode);
                    break;
                case 'required':
                    $result['preselected'] = $this->readPreselected($fieldNode);
                    break;
                case 'synchronization':
                    $result['synchronization'] = $this->readSynchronization($fieldNode);
                    break;
                case 'identifier':
                    $result['identification'] = $this->readIdentification($fieldNode);
                    break;
            }
        }

        return $result;
    }

    private function readSynchronization(\DOMNode $node): array
    {
        if (!$node->hasChildNodes()) {
            return [];
        }

        $result = [];

        foreach ($node->childNodes as $itemNode) {
            if ($itemNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            $result[] = $this->readSynchronizationParams($itemNode);
        }

        return $result;
    }

    private function readSynchronizationParams(\DOMNode $node): array
    {
        if (!$node->hasChildNodes()) {
            return [];
        }

        $result = [];

        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            switch ($childNode->tagName) {
                case 'entityName':
                    $result['entityName'] = $childNode->nodeValue;
                    break;
                case 'fieldName':
                    $result['fieldName'] = $childNode->nodeValue;
                    break;
            }
        }

        return $result;
    }

    private function readIdentification(\DOMNode $node): array
    {
        if (!$node->hasChildNodes()) {
            return [];
        }

        $result = ['isIdentifier' => true];

        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            switch ($childNode->tagName) {
                case 'label':
                    $result['label'] = $childNode->nodeValue;
                    break;
            }
        }

        return $result;
    }

    public function readValidation(\DOMNode $node): array
    {
        if (!$node->hasChildNodes()) {
            return [];
        }

        $result = [];
        /**
         * @var \DomNode $validationNode
         */
        foreach ($node->childNodes as $validationNode) {
            if ($validationNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            $result[] = $this->readValidationParams($validationNode);
        }

        return $result;
    }

    public function readPreselected(\DOMNode $node): array
    {
        if (!$node->hasChildNodes()) {
            return [
                'isRequired' => true,
                'behaviors' => [
                    'includeBehaviors' => [
                        'add_direct',
                        'addUpdate_direct',
                        'add',
                        'addUpdate'
                    ]
                ]
            ];
        }
        $result = ['isRequired' => true];

        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            switch ($childNode->tagName) {
                case 'excludeBehaviors':
                    if ($excludeBehaviors = $this->getValidationBehaviors($childNode)) {
                        $result['behaviors']['excludeBehaviors'] = $excludeBehaviors;
                    }
                    break;
                case 'includeBehaviors':
                    if ($includeBehaviors = $this->getValidationBehaviors($childNode)) {
                        $result['behaviors']['includeBehaviors'] = $includeBehaviors;
                    }
                    break;
            }
        }

        return $result;
    }

    public function readBehaviorParams(\DOMNode $node): array
    {
        $result = [
            'class' => $node->getAttribute('class'),
            'arguments' => []
        ];
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $validationNode) {
                if ($validationNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                switch ($validationNode->tagName) {
                    case 'excludeBehaviors':
                        if ($excludeBehaviors = $this->getValidationBehaviors($validationNode)) {
                            $result['excludeBehaviors'] = $excludeBehaviors;
                        }
                        break;
                    case 'includeBehaviors':
                        if ($includeBehaviors = $this->getValidationBehaviors($validationNode)) {
                            $result['includeBehaviors'] = $includeBehaviors;
                        }
                        break;
                }
            }
        }

        return $result;
    }

    public function readValidationParams(\DOMNode $node)
    {
        $result = [
            'class' => $node->getAttribute('class'),
            'error' => $node->getAttribute('error'),
            'arguments' => []
        ];

        foreach ($node->attributes as $attribute) {
            if ($attribute->nodeName == 'rootOnly') {
                $result['rootOnly'] = $this->booleanUtils->toBoolean(
                    $attribute->nodeValue
                );
            } elseif ($attribute->nodeName == 'isZeroValueAllowed') {
                $result['arguments'][$attribute->nodeName] = [
                    'name' => 'isZeroValueAllowed',
                    'xsi:type' => 'boolean',
                    'value' => $this->booleanUtils->toBoolean(
                        $attribute->nodeValue
                    )
                ];
            } elseif (!in_array($attribute->nodeName, ['class', 'error'])) {
                $result['arguments'][$attribute->nodeName] = [
                    'name' => $attribute->nodeName,
                    'xsi:type' => 'string',
                    'value' => $attribute->nodeValue
                ];
            }
        }

        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $validationArguments) {
                if ($validationArguments->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                switch ($validationArguments->tagName) {
                    case 'arguments':
                        if ($arguments = $this->parseArguments($validationArguments->childNodes)) {
                            $result['arguments'] = $arguments;
                        }
                        break;
                    case 'excludeBehaviors':
                        if ($excludeBehaviors = $this->getValidationBehaviors($validationArguments)) {
                            $result['excludeBehaviors'] = $excludeBehaviors;
                        }
                        break;
                    case 'includeBehaviors':
                        if ($includeBehaviors = $this->getValidationBehaviors($validationArguments)) {
                            $result['includeBehaviors'] = $includeBehaviors;
                        }
                        break;
                }
            }
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

            $result[] = ['arguments' => []];
            $index = count($result) - 1;

            foreach ($action->attributes as $attribute) {
                if (in_array($attribute->nodeName, ['class', 'name', 'apply'])) {
                    $result[$index][$attribute->nodeName] = $attribute->nodeValue;
                } elseif ($attribute->nodeName === 'force') {
                    $result[$index]['arguments'][$attribute->nodeName] = [
                        'name' => $attribute->nodeName,
                        'xsi:type' => 'boolean',
                        'value' => $this->booleanUtils->toBoolean($attribute->nodeValue)
                    ];
                } else {
                    $result[$index]['arguments'][$attribute->nodeName] = [
                        'name' => $attribute->nodeName,
                        'xsi:type' => 'string',
                        'value' => $attribute->nodeValue
                    ];
                }
            }

            if ($arguments = $this->getClassArguments($action)) {
                $result[count($result) - 1]['arguments'] = $arguments;
            }
        }

        return $result;
    }

    public function readImportEvents(\DOMNode $node): array
    {
        if (!$node->hasChildNodes()) {
            return [];
        }

        $result = [];
        /**
         * @var \DomNode $eventTypeNode
         */
        foreach ($node->childNodes as $eventTypeNode) {
            if ($eventTypeNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            if ($eventTypeNode->hasChildNodes()) {
                /**
                 * @var \DomNode $event
                 */
                foreach ($eventTypeNode->childNodes as $event) {
                    if ($event->nodeType != XML_ELEMENT_NODE) {
                        continue;
                    }
                    if (!isset($result[$eventTypeNode->nodeName])) {
                        $result[$eventTypeNode->nodeName] = [];
                    }

                    $result[$eventTypeNode->nodeName][] = [
                        'class' => $event->getAttribute('class'),
                        'arguments' => $this->getClassArguments($event)
                    ];
                }
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

    public function getValidationBehaviors(\DOMNode $node): array
    {
        if (!$node->hasChildNodes()) {
            return [];
        }

        $result = [];
        foreach ($node->childNodes as $validationBehavior) {
            if ($validationBehavior->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $result[] = $validationBehavior->getAttribute('code');
        }

        return $result;
    }

    public function readSampleData(\DOMNode $node): array
    {
        $result = [];

        if ($node->hasChildNodes()) {
            $result = [];
            /**
             * @var \DomNode $row
             */
            foreach ($node->childNodes as $row) {
                if ($row->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                $result[] = $this->readSampleDataRow($row);
            }
        }

        return $result;
    }

    public function readSampleDataRow(\DOMNode $node): array
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

                if ($field->tagName === 'field') {
                    $result[$field->getAttribute('name')] = $field->nodeValue;
                } elseif ($field->tagName === 'subentity') {
                    foreach ($field->childNodes as $childNode) {
                        if ($childNode->nodeType != XML_ELEMENT_NODE) {
                            continue;
                        }
                        $row = $this->readSampleDataRow($childNode);
                        $result[$field->getAttribute('name')][] = $row;
                    }
                }
            }
        }

        return $result;
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
                case 'action':
                    $relationConfig[$relationNode->tagName] = $this->readRelationAction($relationNode);
                    break;
                case 'validation':
                    $relationConfig[$relationNode->tagName] = $this->readRelationValidation($relationNode);
                    break;
                case 'arguments':
                    $relationConfig[$relationNode->tagName] = $this->getClassArguments($relation);
                    break;
                case 'required':
                    $relationConfig[$relationNode->tagName] = $this->readPreselected($relationNode);
                    break;
                default:
                    $relationConfig[$relationNode->tagName] = $relationNode->nodeValue;
            }
        }

        return $relationConfig;
    }

    protected function readRelationValidation(\DOMNode $relationNode): array
    {
        $result = ['class' => $relationNode->getAttribute('class')];

        if ($relationNode->hasChildNodes()) {
            foreach ($relationNode->childNodes as $validationNode) {
                if ($validationNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                switch ($validationNode->tagName) {
                    case 'excludeBehaviors':
                        if ($excludeBehaviors = $this->getValidationBehaviors($validationNode)) {
                            $result['excludeBehaviors'] = $excludeBehaviors;
                        }
                        break;
                    case 'includeBehaviors':
                        if ($includeBehaviors = $this->getValidationBehaviors($validationNode)) {
                            $result['includeBehaviors'] = $includeBehaviors;
                        }
                        break;
                }
            }
        }

        return $result;
    }

    protected function readRelationAction(\DOMNode $relationNode): array
    {
        $result = ['class' => $relationNode->getAttribute('class')];

        if ($relationNode->hasChildNodes()) {
            foreach ($relationNode->childNodes as $actionNode) {
                if ($actionNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                if ($actionNode->tagName == 'arguments') {
                    $result['arguments'] = $this->getClassArguments($relationNode);
                }
            }
        }

        return $result;
    }
}
