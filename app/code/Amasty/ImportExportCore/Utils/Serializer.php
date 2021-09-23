<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportExportCore
 */


declare(strict_types=1);

namespace Amasty\ImportExportCore\Utils;

use Amasty\ImportExportCore\Utils\Internal\ArrayToObjectConvert;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Webapi\ServiceOutputProcessor;

class Serializer
{
    /**
     * @var ServiceOutputProcessor
     */
    private $serviceOutputProcessor;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var ArrayToObjectConvert
     */
    private $arrayToObjectConvert;

    public function __construct(
        ServiceOutputProcessor $serviceOutputProcessor,
        ArrayToObjectConvert $arrayToObjectConvert,
        DataObjectHelper $dataObjectHelper,
        Json $jsonSerializer
    ) {
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->jsonSerializer = $jsonSerializer;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->arrayToObjectConvert = $arrayToObjectConvert;
    }

    public function serialize($object, string $type): string
    {
        return $this->jsonSerializer->serialize(
            $this->convertObjectToArray($object, $type)
        );
    }

    public function convertObjectToArray($object, string $type)
    {
        return $this->serviceOutputProcessor->convertValue($object, $type);
    }

    public function convertSerializedToArray(string $serialized)
    {
        return $this->jsonSerializer->unserialize($serialized);
    }

    public function unserialize(string $serialized, string $type)
    {
        return $this->arrayToObjectConvert->convertValue(
            $this->convertSerializedToArray($serialized),
            $type
        );
    }
}
