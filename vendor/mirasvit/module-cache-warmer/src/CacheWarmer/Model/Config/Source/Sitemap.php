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
use Magento\Sitemap\Model\ResourceModel\Sitemap\Collection as SitemapCollection;

class Sitemap implements ArrayInterface
{
    private $sitemapCollection;

    public function __construct(SitemapCollection $sitemapCollection)
    {
        $this->sitemapCollection = $sitemapCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->sitemapCollection as $sitemap) {
            $options[] = [
                'value' => $sitemap->getData('sitemap_id'),
                'label' => $sitemap->getData('sitemap_filename')
            ];
        }

        return $options;
    }
}
