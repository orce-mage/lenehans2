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



namespace Mirasvit\CacheWarmer\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\CacheWarmer\Api\Data\PageTypeInterface;

class PageType extends AbstractModel implements PageTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(PageTypeInterface::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getPageType()
    {
        return $this->getData(PageTypeInterface::PAGE_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPageType($value)
    {
        return $this->setData(PageTypeInterface::PAGE_TYPE, $value);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Mirasvit\CacheWarmer\Model\ResourceModel\PageType::class);
    }
}