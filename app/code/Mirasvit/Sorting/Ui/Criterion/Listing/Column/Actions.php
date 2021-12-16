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

namespace Mirasvit\Sorting\Ui\Criterion\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mirasvit\Sorting\Api\Data\CriterionInterface;

class Actions extends Column
{
    private $urlBuilder;

    private $escaper;

    public function __construct(
        Escaper $escaper,
        UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->escaper    = $escaper;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $title = $this->escaper->escapeHtml($item['name']);

                $item[$this->getData('name')] = [
                    'edit'    => [
                        'href'  => $this->urlBuilder->getUrl('sorting/criterion/edit', [
                            CriterionInterface::ID => $item[CriterionInterface::ID],
                        ]),
                        'label' => __('Edit'),
                    ],
                    'delete'  => [
                        'href'    => $this->urlBuilder->getUrl('sorting/criterion/delete', [
                            CriterionInterface::ID => $item[CriterionInterface::ID],
                        ]),
                        'label'   => __('Delete'),
                        'confirm' => [
                            'title'   => __('Delete %1', $title),
                            'message' => __('Are you sure you want to delete a %1 record?', $title),
                        ],
                    ],
                ];
            }
        }

        return $dataSource;
    }
}
