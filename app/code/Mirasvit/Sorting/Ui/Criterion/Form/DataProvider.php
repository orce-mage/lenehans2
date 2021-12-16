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

namespace Mirasvit\Sorting\Ui\Criterion\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Repository\CriterionRepository;
use Mirasvit\Sorting\Model\Config\Source\SortByAttributeSource;
use Mirasvit\Sorting\Model\Config\Source\SortByRankingFactorSource;
use Mirasvit\Sorting\Model\Config\Source\SortBySource;
use Mirasvit\Sorting\Model\Config\Source\SortDirectionSource;

class DataProvider extends AbstractDataProvider
{
    private $repository;

    private $context;

    private $uiComponentFactory;

    private $sortBySource;

    private $sortByAttributeSource;

    private $sortByRankingFactorSource;

    private $sortDirectionSource;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     *
     * @param CriterionRepository $repository
     * @param SortBySource $sortBySource
     * @param SortByAttributeSource $sortByAttributeSource
     * @param SortByRankingFactorSource $sortByRankingFactorSource
     * @param SortDirectionSource $sortDirectionSource
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        CriterionRepository $repository,
        SortBySource $sortBySource,
        SortByAttributeSource $sortByAttributeSource,
        SortByRankingFactorSource $sortByRankingFactorSource,
        SortDirectionSource $sortDirectionSource,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->repository         = $repository;
        $this->collection         = $this->repository->getCollection();
        $this->context            = $context;
        $this->uiComponentFactory = $uiComponentFactory;

        $this->sortBySource              = $sortBySource;
        $this->sortByAttributeSource     = $sortByAttributeSource;
        $this->sortByRankingFactorSource = $sortByRankingFactorSource;
        $this->sortDirectionSource       = $sortDirectionSource;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return mixed
     */
    public function getConfigData()
    {
        $data = parent::getConfigData();

        $data['sortBySource']              = $this->sortBySource->toOptionArray();
        $data['sortByAttributeSource']     = $this->sortByAttributeSource->toOptionArray();
        $data['sortByRankingFactorSource'] = $this->sortByRankingFactorSource->toOptionArray();
        $data['sortDirectionSource']       = $this->sortDirectionSource->toOptionArray();

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $result = [];

        $model = $this->getModel();

        if ($model) {
            $data = $model->getData();

            $data[CriterionInterface::CONDITIONS] = $model->getConditionCluster()->toArray();
            //print_r($data);die();
            $result[$model->getId()] = $data;
        }

        return $result;
    }

    /**
     * @return false|\Mirasvit\Sorting\Api\Data\CriterionInterface
     */
    private function getModel()
    {
        $id = $this->context->getRequestParam($this->getRequestFieldName(), null);

        return $id ? $this->repository->get($id) : false;
    }
}
