<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Config\CustomEntity;

use Magento\Framework\ObjectManagerInterface;

class CollectionFactory
{
    const TABLE_NAME = 'tableName';
    const ID_FILED = 'idField';

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $idField;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        array $config
    ) {
        $this->tableName = $config[self::TABLE_NAME];
        $this->idField = $config[self::ID_FILED];
        $this->objectManager = $objectManager;
    }

    public function create()
    {
        $resourceModel = $this->objectManager->create(
            CustomResourceModel::class,
            [self::TABLE_NAME => $this->tableName, self::ID_FILED => $this->idField]
        );

        return $this->objectManager->create(CustomCollection::class, ['resource' => $resourceModel]);
    }
}
