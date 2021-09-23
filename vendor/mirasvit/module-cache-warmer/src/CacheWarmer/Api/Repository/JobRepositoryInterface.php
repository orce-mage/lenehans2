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

use Mirasvit\CacheWarmer\Api\Data\JobInterface;

interface JobRepositoryInterface
{
    /**
     * @return \Mirasvit\CacheWarmer\Model\ResourceModel\Job\Collection|JobInterface[]
     */
    public function getCollection();

    /**
     * @return JobInterface
     */
    public function create();

    /**
     * @param JobInterface $job
     * @return JobInterface
     */
    public function save(JobInterface $job);

    /**
     * @param int $id
     * @return JobInterface|false
     */
    public function get($id);

    /**
     * @param JobInterface $job
     * @return bool
     */
    public function delete(JobInterface $job);
}