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



namespace Mirasvit\CacheWarmer\Api\Repository;

use Mirasvit\CacheWarmer\Api\Data\TraceInterface;

interface TraceRepositoryInterface
{
    /**
     * @return \Mirasvit\CacheWarmer\Model\ResourceModel\Trace\Collection|TraceInterface[]
     */
    public function getCollection();

    /**
     * @return TraceInterface
     */
    public function create();

    /**
     * @param TraceInterface $model
     * @return TraceInterface
     */
    public function save(TraceInterface $model);

    /**
     * @param int $id
     * @return TraceInterface|false
     */
    public function get($id);

    /**
     * @param TraceInterface $model
     * @return bool
     */
    public function delete(TraceInterface $model);
}