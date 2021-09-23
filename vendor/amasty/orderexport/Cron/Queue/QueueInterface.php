<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Cron\Queue;

interface QueueInterface
{
    const SECONDS_IN_MINUTE = 60;

    const GENERATE_QUEUE_AHEAD_FOR = 3600;

    const RUN_BY_CRON_FIELD = 'run_by_cron';

    const PROFILE_ENABLED_FIELD = 'enabled';

    /**
     * @return void
     */
    public function execute();
}
