<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Model\EntityManager;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Sequence\SequenceManager;
use Magento\Framework\EntityManager\Sequence\SequenceRegistry;

class SequenceHandler
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SequenceManager
     */
    private $sequenceManager;

    /**
     * @var SequenceRegistry
     */
    private $sequenceRegistry;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    public function __construct(
        ResourceConnection $resourceConnection,
        SequenceManager $sequenceManager,
        SequenceRegistry $sequenceRegistry,
        MetadataPool $metadataPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->sequenceManager = $sequenceManager;
        $this->sequenceRegistry = $sequenceRegistry;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Adds/generates sequence values for new entities creation
     *
     * @param array $data
     * @param string $entityType
     * @throws \Exception
     */
    public function handleNew(array &$data, string $entityType)
    {
        if (!$this->getSequenceInfo($entityType)) {
            return;
        }
        $metadata = $this->metadataPool->getMetadata($entityType);
        $identifierField = $metadata->getIdentifierField();

        $existingSeqValues = $this->getSequenceValues($data, $entityType);
        foreach ($data as &$row) {
            if (isset($row[$identifierField])
                && !in_array($row[$identifierField], $existingSeqValues)
            ) {
                $this->sequenceManager->force(
                    $entityType,
                    $row[$identifierField]
                );
            } else {
                $id = $metadata->generateIdentifier();
                if ($id) {
                    $row[$identifierField] = $id;
                }
            }
        }
    }

    /**
     * Adds/generates sequence values for new entities creation or exiting entities update
     *
     * @param array $data
     * @param string $entityType
     * @throws \Exception
     */
    public function handleNewOrUpdate(array &$data, string $entityType)
    {
        if (!$this->getSequenceInfo($entityType)) {
            return;
        }
        $metadata = $this->metadataPool->getMetadata($entityType);
        $identifierField = $metadata->getIdentifierField();
        $linkField = $metadata->getLinkField();

        $existingEntries = $this->getEntityEntries($data, $entityType);
        $existingSeqValues = $this->getSequenceValues($data, $entityType);

        foreach ($data as &$row) {
            if (isset($row[$linkField]) && in_array($row[$linkField], $existingEntries)) { // update
                if (isset($row[$identifierField])
                    && !in_array($row[$identifierField], $existingSeqValues)
                ) {
                    $this->sequenceManager->force(
                        $entityType,
                        $row[$identifierField]
                    );
                }
            } else { // new
                if (isset($row[$linkField])
                    && isset($row[$identifierField])
                    && !in_array($row[$identifierField], $existingSeqValues)
                ) {
                    $this->sequenceManager->force(
                        $entityType,
                        $row[$identifierField]
                    );
                } else {
                    $id = $metadata->generateIdentifier();
                    if ($id) {
                        $row[$identifierField] = $id;
                    }
                }
            }
        }
    }

    /**
     * Adds sequence values for exiting entities update
     *
     * @param array $data
     * @param string $entityType
     * @throws \Exception
     */
    public function handleUpdate(array &$data, string $entityType)
    {
        if (!$this->getSequenceInfo($entityType)) {
            return;
        }
        $metadata = $this->metadataPool->getMetadata($entityType);
        $identifierField = $metadata->getIdentifierField();

        $existingSeqValues = $this->getSequenceValues($data, $entityType);
        foreach ($data as $row) {
            if (isset($row[$identifierField])
                && !in_array($row[$identifierField], $existingSeqValues)
            ) {
                $this->sequenceManager->force(
                    $entityType,
                    $row[$identifierField]
                );
            }
        }
    }

    /**
     * Get existing entity entries
     *
     * @param array $data
     * @param string $entityType
     * @return array
     * @throws \Exception
     */
    private function getEntityEntries(array $data, string $entityType): array
    {
        $connection = $this->resourceConnection->getConnection();
        $metadata = $this->metadataPool->getMetadata($entityType);
        $identifier = $metadata->getLinkField();

        $select = $connection->select()
            ->from($metadata->getEntityTable(), [$identifier])
            ->where($identifier . ' IN (?)', array_column($data, $identifier));

        return $connection->fetchCol($select);
    }

    /**
     * Get existing sequence values
     *
     * @param array $data
     * @param string $entityType
     * @return array
     * @throws \Exception
     */
    private function getSequenceValues(array $data, string $entityType): array
    {
        $connection = $this->resourceConnection->getConnection();
        $sequenceInfo = $this->sequenceRegistry->retrieve($entityType);
        $metadata = $this->metadataPool->getMetadata($entityType);

        $select = $connection->select()
            ->from(
                $this->resourceConnection->getTableName($sequenceInfo['sequenceTable']),
                ['sequence_value']
            )->where(
                'sequence_value' . ' IN (?)',
                array_column($data, $metadata->getIdentifierField())
            );

        return $connection->fetchCol($select);
    }

    private function getSequenceInfo(string $entityType)
    {
        $this->metadataPool->getMetadata($entityType);
        return $this->sequenceRegistry->retrieve($entityType) ?
            $this->sequenceRegistry->retrieve($entityType)['sequence'] : null;
    }
}
