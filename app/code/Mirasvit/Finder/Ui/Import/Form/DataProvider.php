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
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Finder\Ui\Import\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Finder\Api\Data\FilterOptionInterface;
use Mirasvit\Finder\Repository\FilterOptionRepository;
use Mirasvit\Finder\Service\LayoutService;

class DataProvider extends AbstractDataProvider
{
    private $context;

    private $filterOptionRepository;

    private $layoutService;

    public function __construct(
        FilterOptionRepository $filterOptionRepository,
        LayoutService $layoutService,
        ContextInterface $context,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->context                = $context;
        $this->filterOptionRepository = $filterOptionRepository;
        $this->layoutService          = $layoutService;

        $this->collection = $this->filterOptionRepository->getCollection();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getMeta(): array
    {
        $meta = parent::getMeta();

        return $meta;
    }

    public function getData(): array
    {
        $parentId = (int)$this->context->getRequestParam('finder_id');

        $this->collection->addFieldToFilter(FilterOptionInterface::FINDER_ID, $parentId);
        $this->collection->getSelect()->order('filter_id');

        $result = [];

        foreach ($this->collection as $model) {
            $data = $model->getData();

            $result[$model->getId()] = $data;
        }

        return $result;
    }
}
