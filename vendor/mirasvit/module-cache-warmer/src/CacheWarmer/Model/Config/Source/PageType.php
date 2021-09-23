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



namespace Mirasvit\CacheWarmer\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageTypeRepositoryInterface;

class PageType implements ArrayInterface
{
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;
    /**
     * @var PageTypeRepositoryInterface
     */
    private $pageTypeRepository;

    /**
     * PageType constructor.
     * @param PageRepositoryInterface $pageRepository
     * @param PageTypeRepositoryInterface $pageTypeRepository
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        PageTypeRepositoryInterface $pageTypeRepository
    ) {
        $this->pageRepository     = $pageRepository;
        $this->pageTypeRepository = $pageTypeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->getPageTypes() as $action) {
            $label = explode('_', $action);
            $label = array_map('ucfirst', $label);

            $options[] = [
                'value' => $action,
                'label' => implode(' · ', $label),
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getPageTypes()
    {
        $types          = $this->pageRepository->getPageTypes();
        $typesCollected = $this->pageTypeRepository->getPageTypes();

        $types = array_unique(
            array_merge_recursive(
                $types,
                $typesCollected,
                ['cms_index_index',
                 'cms_page_view',
                 'catalog_category_view',
                 'catalog_product_view',
                ]
            )
        );

        sort($types);

        return $types;
    }
}
