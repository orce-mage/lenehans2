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
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Ui\Source\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mirasvit\CacheWarmer\Api\Data\SourceInterface;

class ActionColumn extends Column
{
    private $urlBuilder;

    /**
     * ActionColumn constructor.
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
                        'label' => __('Edit'),
                        'href'  => $this->urlBuilder->getUrl('cache_warmer/source/edit', [
                            SourceInterface::ID => $item[SourceInterface::ID],
                        ]),
                    ],
                    'delete' => [
                        'href'    => $this->urlBuilder->getUrl('cache_warmer/source/delete', [
                            SourceInterface::ID => $item[SourceInterface::ID],
                        ]),
                        'label'   => __('Delete'),
                        'confirm' => [
                            'title'   => __('Delete the record with ID "' . $item[SourceInterface::ID] . '"'),
                            'message' => __('Are you sure you want to delete the record with ID "' . $item[SourceInterface::ID] . '"?'),
                        ],
                    ],
                ];
            }
        }

        return $dataSource;
    }
}
