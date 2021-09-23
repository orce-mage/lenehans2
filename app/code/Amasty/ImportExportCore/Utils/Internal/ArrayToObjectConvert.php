<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportExportCore
 */


namespace Amasty\ImportExportCore\Utils\Internal;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Exception\SerializationException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Reflection\NameFinder;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface;
use Zend\Code\Reflection\ClassReflection;

/**
 * Class rewrites _createFromArray method
 * because if original class couldn't find setter throw exception
 */
class ArrayToObjectConvert extends \Magento\Framework\Webapi\ServiceInputProcessor
{
    /**
     * @var NameFinder
     */
    private $objectNameFinder;

    public function __construct(
        NameFinder $objectNameFinder,
        TypeProcessor $typeProcessor,
        ObjectManagerInterface $objectManager,
        AttributeValueFactory $attributeValueFactory,
        CustomAttributeTypeLocatorInterface $customAttributeTypeLocator,
        MethodsMap $methodsMap
    ) {
        parent::__construct(
            $typeProcessor,
            $objectManager,
            $attributeValueFactory,
            $customAttributeTypeLocator,
            $methodsMap
        );
        $this->objectNameFinder = $objectNameFinder;
    }

    protected function _createFromArray($className, $data)
    {
        $data = is_array($data) ? $data : [];
        $className = (string) $className;
        $class = new ClassReflection($className);
        if (is_subclass_of($className, self::EXTENSION_ATTRIBUTES_TYPE)) {
            $className = substr($className, 0, -strlen('Interface'));
        }
        $object = $this->objectManager->create($className);

        foreach ($data as $propertyName => $value) {
            $camelCaseProperty = SimpleDataObjectConverter::snakeCaseToUpperCamelCase($propertyName);
            try {
                $methodName = $this->objectNameFinder->getGetterMethodName($class, $camelCaseProperty);
            } catch (\Exception $e) {
                continue;
            }
            $methodReflection = $class->getMethod($methodName);
            if ($methodReflection->isPublic()) {
                $returnType = $this->typeProcessor->getGetterReturnType($methodReflection)['type'];
                try {
                    $setterName = $this->objectNameFinder->getSetterMethodName($class, $camelCaseProperty);
                } catch (\Exception $e) {
                    if (empty($value)) {
                        continue;
                    }
                }
                try {
                    if ($camelCaseProperty === 'CustomAttributes') {
                        $setterValue = $this->convertCustomAttributeValue($value, $className);
                    } else {
                        $setterValue = $this->convertValue($value, $returnType);
                    }
                } catch (SerializationException $e) {
                    throw new SerializationException(
                        new Phrase(
                            'Error occurred during "%field_name" processing. %details',
                            ['field_name' => $propertyName, 'details' => $e->getMessage()]
                        )
                    );
                }
                $object->{$setterName}($setterValue);
            }
        }

        return $object;
    }
}
