<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Utils;

use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Sequence\SequenceFactory;
use Magento\Framework\ObjectManagerInterface;

class MetadataSearcher extends MetadataPool
{
    const ENTITY_TYPE = 'entity_type';
    const EAV_ENTITY_TYPE = 'eav_entity_type';

    private $metadataLinks;

    public function __construct(
        ObjectManagerInterface $objectManager,
        SequenceFactory $sequenceFactory,
        array $metadata
    ) {
        parent::__construct($objectManager, $sequenceFactory, $metadata);
        $this->initializeMetadataLinks($metadata);
    }

    public function searchMetadata(string $value, string $key = self::ENTITY_TYPE): ?EntityMetadataInterface
    {
        $metadata = null;

        try {
            switch ($key) {
                case self::ENTITY_TYPE:
                    $metadata = $this->getMetadata($value);
                    break;
                case self::EAV_ENTITY_TYPE:
                    $entityType = $this->metadataLinks[$value] ?? '';
                    $metadata = $this->getMetadata($entityType);
                    break;
            }
        } catch (\Exception $e) {
            return null;
        }

        return $metadata;
    }

    private function initializeMetadataLinks(array $metadata)
    {
        $metadataLinks = [];

        /** @var EntityMetadataInterface $entity */
        foreach ($metadata as $key => $entity) {
            if (isset($entity['eavEntityType'])) {
                $metadataLinks[$entity['eavEntityType']] = $key;
            }
        }

        $this->metadataLinks = $metadataLinks;
    }
}
