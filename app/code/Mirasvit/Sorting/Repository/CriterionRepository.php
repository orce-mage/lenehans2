<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-sorting
 * @version   1.1.14
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Api\Data\CriterionInterfaceFactory;
use Mirasvit\Sorting\Factor\FactorInterface;
use Mirasvit\Sorting\Model\ResourceModel\Criterion\CollectionFactory;

class CriterionRepository
{
    private $entityManager;

    private $factory;

    private $collectionFactory;

    public function __construct(
        EntityManager $entityManager,
        CriterionInterfaceFactory $factory,
        CollectionFactory $collectionFactory
    ) {
        $this->entityManager     = $entityManager;
        $this->factory           = $factory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return \Mirasvit\Sorting\Model\ResourceModel\Criterion\Collection | CriterionInterface[]
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * @return CriterionInterface
     */
    public function create()
    {
        return $this->factory->create();
    }

    /**
     * @param int $id
     *
     * @return CriterionInterface|false
     */
    public function get($id)
    {
        $model = $this->create();

        $this->entityManager->load($model, $id);

        return $model->getId() ? $model : false;
    }

    /**
     * @param string $code
     *
     * @return CriterionInterface|false
     */
    public function getByCode($code)
    {
        $model = $this->create();

        $model = $model->load($code, CriterionInterface::CODE);

        return $model->getId() ? $model : false;
    }

    /**
     * @param CriterionInterface $model
     *
     * @return CriterionInterface
     */
    public function save(CriterionInterface $model)
    {
        if (!$model->getCode()) {
            $code = preg_replace("/[^a-z0-9]/i", '-', $model->getName());

            $model->setCode(strtolower($code));
        }
        
        if ($model->getCode() == 'relevance') {
            throw new \Exception((string)__(
                'Word "relevance" can\'t be used as code for search criteria because this word is reserved.'
            ));
        }

        $this->entityManager->save($model);


        $collection = $this->getCollection();
        $collection->addFieldToFilter(CriterionInterface::CODE, $model->getCode())
            ->addFieldToFilter(CriterionInterface::ID, ['neq' => $model->getId()]);

        if ($collection->getSize()) {
            $model->setCode($model->getCode() . '-1');
            $this->entityManager->save($model);
        }

        if ($model->isDefault()) {
            $collection = $this->getCollection();
            $collection->addFieldToFilter(CriterionInterface::IS_DEFAULT, 1)
                ->addFieldToFilter(CriterionInterface::ID, ['neq' => $model->getId()]);

            foreach ($collection as $item) {
                $item->setIsDefault(false);
                $this->save($item);
            }
        }

        if ($model->isSearchDefault()) {
            $collection = $this->getCollection();
            $collection->addFieldToFilter(CriterionInterface::IS_SEARCH_DEFAULT, 1)
                ->addFieldToFilter(CriterionInterface::ID, ['neq' => $model->getId()]);

            foreach ($collection as $item) {
                $item->setIsSearchDefault(false);
                $this->save($item);
            }
        }

        return $model;
    }

    /**
     * @param CriterionInterface $model
     *
     * @return $this
     */
    public function delete(CriterionInterface $model)
    {
        $this->entityManager->delete($model);

        return $this;
    }
}
