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

namespace Mirasvit\Sorting\Ui\RankingFactor\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;

class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * Actions constructor.
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = [
                    'edit'   => [
                        'href'  => $this->urlBuilder->getUrl('sorting/rankingFactor/edit', [
                            RankingFactorInterface::ID => $item[RankingFactorInterface::ID],
                        ]),
                        'label' => __('Edit'),
                    ],
                    'reindex'   => [
                        'href'  => $this->urlBuilder->getUrl('sorting/rankingFactor/reindex', [
                            RankingFactorInterface::ID => $item[RankingFactorInterface::ID],
                        ]),
                        'label' => __('Reindex'),
                    ],
                    'delete' => [
                        'href'    => $this->urlBuilder->getUrl('sorting/rankingFactor/delete', [
                            RankingFactorInterface::ID => $item[RankingFactorInterface::ID],
                        ]),
                        'label'   => __('Delete'),
                        'confirm' => [
                            'title'   => __('Delete "'. $item['name'] .'"'),
                            'message' => __('Are you sure you want to delete a "'. $item['name'] .'" record?'),
                        ],
                    ],
                ];
            }
        }

        return $dataSource;
    }
}
