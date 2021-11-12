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

namespace Mirasvit\Finder\Ui\Finder\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Finder\Repository\FinderRepository;
use Mirasvit\Finder\Service\LayoutService;

class DataProvider extends AbstractDataProvider
{
    private $finderRepository;

    private $filtersModifier;

    private $layoutService;

    public function __construct(
        FinderRepository $finderRepository,
        Modifier\FiltersModifier $filtersModifier,
        LayoutService $layoutService,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->finderRepository = $finderRepository;
        $this->filtersModifier  = $filtersModifier;
        $this->layoutService    = $layoutService;

        $this->collection = $this->finderRepository->getCollection();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getMeta(): array
    {
        $meta = parent::getMeta();

        $meta = $this->filtersModifier->modifyMeta($meta);

        return $meta;
    }

    public function getData(): array
    {
        $result = [];
        foreach ($this->collection as $model) {
            $data = $model->getData();

            $data['display_xml']    = $this->layoutService->getXmlMarkup($model);
            $data['display_widget'] = $this->layoutService->getWidgetMarkup($model);
            $data['display_phtml']  = $this->layoutService->getPhpMarkup($model);

            $result[$model->getId()] = $data;
        }
        $result = $this->filtersModifier->modifyData($result);


        return $result;
    }
}
