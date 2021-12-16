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

namespace Mirasvit\Sorting\Ui\RankingFactor\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Repository\RankingFactorRepository;

class DataProvider extends AbstractDataProvider
{
    private $repository;

    private $context;

    private $uiComponentFactory;

    /**
     * @var ModifierInterface[]
     */
    private $modifierPool;

    /**
     * DataProvider constructor.
     * @param RankingFactorRepository $repository
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     * @param array $modifier
     */
    public function __construct(
        RankingFactorRepository $repository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = [],
        array $modifier = []
    ) {
        $this->repository         = $repository;
        $this->collection         = $this->repository->getCollection();
        $this->context            = $context;
        $this->uiComponentFactory = $uiComponentFactory;

        $this->modifierPool = $modifier;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return mixed
     */
    public function getConfigData()
    {
        $data = parent::getConfigData();

        foreach ($this->repository->getFactors() as $code => $factor) {
            $data['notes'][$code] = $factor->getDescription();
        }

        return $data;
    }

    /**
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        $model = $this->getModel();
        if (!$model) {
            return $meta;
        }

        $factor = $this->repository->getFactor($model->getType());
        if (!$factor) {
            return $meta;
        }

        $uiComponent = $factor->getUiComponent();
        if (!$uiComponent) {
            return $meta;
        }

        $component = $this->uiComponentFactory->create($uiComponent);

        $meta = $this->prepareComponent($component)['children'];

        return $meta;
    }

    /**
     * @param UiComponentInterface $component
     *
     * @return array
     */
    protected function prepareComponent(UiComponentInterface $component)
    {
        $data = [];
        foreach ($component->getChildComponents() as $name => $child) {
            $data['children'][$name] = $this->prepareComponent($child);
        }

        $data['arguments']['data']  = $component->getData();
        $data['arguments']['block'] = $component->getBlock();

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

            $data[RankingFactorInterface::CONFIG]      = $model->getConfig();
            $data[RankingFactorInterface::CONFIG]['_'] = 1;

            foreach ($this->modifierPool as $type => $modifier) {
                if ($type === $model->getType()) {
                    $data = $modifier->modifyData($data);
                }
            }
            $result[$model->getId()] = $data;
        }

        return $result;
    }

    /**
     * @return false|\Mirasvit\Sorting\Api\Data\RankingFactorInterface
     */
    private function getModel()
    {
        $id = $this->context->getRequestParam($this->getRequestFieldName(), null);

        return $id ? $this->repository->get($id) : false;
    }
}
