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



namespace Mirasvit\CacheWarmer\Api\Service;

use Mirasvit\CacheWarmer\Api\Data\JobInterface;

interface JobServiceInterface
{
    /**
     * @param JobInterface $job
     * @param string $flag
     * @param array $pageIds
     * @return bool
     */
    public function run(JobInterface $job, $flag, $pageIds = []);
}
