<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Test\Integration\TestModule\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

class TestEntity1Repository
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ResourceModel\TestEntity1
     */
    private $resource;

    public function __construct(
        ResourceModel\TestEntity1 $resource
    ) {
        $this->resource = $resource;
        $this->objectManager = Bootstrap::getObjectManager();
    }

    public function save(TestEntity1 $entity)
    {
        $this->resource->save($entity);
    }

    public function getById(int $id): TestEntity1
    {
        $entity = $this->objectManager->create(TestEntity1::class);
        $this->resource->load($entity, $id);
        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('Entity with specified ID "%1" not found.', $id));
        }

        return $entity;
    }

    public function delete(TestEntity1 $entity)
    {
        $this->resource->delete($entity);
    }
}
