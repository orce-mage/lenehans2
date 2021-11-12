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

namespace Mirasvit\Finder\Ui\Import\Listing;

use Magento\Backend\Model\UrlInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider;
use Magento\Framework\App\RequestInterface;
use Mirasvit\Finder\Api\Data\FilterInterface;
use Mirasvit\Finder\Api\Data\FinderInterface;
use Mirasvit\Finder\Api\Data\IndexInterface;
use Mirasvit\Finder\Repository\FilterOptionRepository;
use Mirasvit\Finder\Repository\FilterRepository;

class DataProvider extends ProductDataProvider
{
    private $filterOptionRepository;

    private $filterRepository;

    private $request;

    private $url;

    /**
     * DataProvider constructor.
     *
     * @param ProductCollectionFactory $productCollectionFactory
     * @param string                   $name
     * @param string                   $primaryFieldName
     * @param string                   $requestFieldName
     * @param UrlInterface             $url
     * @param FilterOptionRepository   $filterOptionRepository
     * @param FilterRepository         $filterRepository
     * @param RequestInterface         $request
     * @param array                    $meta
     * @param array                    $data
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        UrlInterface $url,
        FilterOptionRepository $filterOptionRepository,
        FilterRepository $filterRepository,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $productCollectionFactory, [], [], $meta, $data);

        $this->filterOptionRepository = $filterOptionRepository;
        $this->filterRepository       = $filterRepository;
        $this->request                = $request;
        $this->url                    = $url;

        $finderId = (int)$this->request->getParam('finder_id');

        $indexTable  = $this->collection->getTable(IndexInterface::TABLE_NAME);
        $filterTable = $this->collection->getTable(FilterInterface::TABLE_NAME);

        $select = $this->collection->getSelect();

        $select
            ->joinInner(
                [
                    'fi' => new \Zend_Db_Expr('(
                        SELECT index_id, product_id, GROUP_CONCAT(DISTINCT option_id SEPARATOR \'|\') as option_id, filter_id, finder_id 
                        FROM `' . $indexTable . '`
                        GROUP BY finder_id, product_id, filter_id
                    )
                    '),
                ],
                'e.entity_id = fi.product_id and fi.finder_id = ' . $finderId,
                [
                    new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT fi.option_id ORDER BY ff.position) as option_ids'),
                    new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT fi.filter_id ORDER BY ff.position) as filter_ids'),
                ]
            )
            ->joinInner(['ff' => $filterTable],
                'fi.filter_id = ff.filter_id',
                [
                    FilterInterface::POSITION,
                ]
            )
            ->group('entity_id');
    }

    public function getMeta(): array
    {
        $meta = parent::getMeta();

        $finderId = (int)$this->request->getParam('finder_id');

        if ($finderId > 0) {
            $filters = $this->filterRepository->getCollection()
                ->addFieldToFilter(FilterInterface::FINDER_ID, $finderId);

            $sortOrder = 1000;
            foreach ($filters as $filter) {
                $meta['mst_finder_finder_import_product_columns']['children']['filter__' . $filter->getId()] = [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataType'      => 'text',
                                'component'     => 'Magento_Ui/js/grid/columns/column',
                                'componentType' => 'column',
                                'label'         => $filter->getName(),
                                'sortOrder'     => $sortOrder,
                                'sortable'      => false,
                            ],
                        ],
                    ],
                ];

                $sortOrder += 5;
            }

            $meta['listing_top']['children']['exportButton'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'options' => [
                                'csv' => [
                                    'url' => $this->url->getUrl('mst_finder/export/export', [
                                        'type'              => 'csv',
                                        FinderInterface::ID => $finderId,
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            $meta['listing_top']['children']['listing_massaction'] = [
                'children' => [
                    'delete' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'url' => $this->url->getUrl('mst_finder/index/massDelete', [
                                        FinderInterface::ID => $finderId,
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        }

        return $meta;
    }

    public function getData(): array
    {
        $finderId = (int)$this->request->getParam('finder_id');

        $data['items'] = [];

        if ($finderId > 0) {
            foreach ($this->getCollection() as $v) {
                $filterIds = explode(',', $v['filter_ids']);
                $optionIds = explode(',', $v['option_ids']);

                foreach ($filterIds as $k => $filterId) {
                    $value = '';

                    if (strpos($optionIds[$k], '|') != false) {
                        $productOptionIds = explode('|', $optionIds[$k]);
                        foreach ($productOptionIds as $productOptionId) {
                            $option = $this->filterOptionRepository->get((int)$productOptionId);
                            if ($option) {
                                $value .= $option->getName() . ', ';
                            }
                        }
                    } else {
                        $option = $this->filterOptionRepository->get((int)$optionIds[$k]);
                        if ($option) {
                            $value .= $option->getName() . ', ';
                        }
                    }

                    $v['filter__' . $filterId] = trim($value, ' ,');
                }
            }

            $data = parent::getData();
        }

        return $data;
    }
}
