<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Helper;

use Magento\Framework\App\Helper\Context;
use Wyomind\Framework\Model\ResourceModel\ScheduleFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Heartbeat
 * @package Wyomind\Framework\Helper
 */
class Heartbeat extends \Wyomind\Framework\Helper\License
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;
    /**
     * @var \Wyomind\Framework\Model\ResourceModel\ScheduleFactory
     */
    protected $scheduleFactory;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Heartbeat constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Wyomind\Framework\Model\ResourceModel\ScheduleFactory $scheduleFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param ManagerInterface $messageManager
     * @param \Magento\Framework\App\Helper\Context $context
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ScheduleFactory $scheduleFactory,
        DateTime $dateTime,
        ManagerInterface $messageManager,
        Context $context
    ) {
    
        parent::__construct($objectManager, $context);
        $this->dateTime = $dateTime;

        $this->scheduleFactory = $scheduleFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * Check the cron heartbeat :
     * - displays an error if no cron task detected
     * - displays a simple notification if cron task detected
     */
    public function checkHeartbeat()
    {
        $lastHeartbeat = $this->getLastHeartbeat();
        if ($lastHeartbeat === false) {
// no cron task found
            $this->messageManager->addError(__('No cron task found. <a href="https://www.wyomind.com/magento2/faq.html?section=faq#_scheduled-tasks-don-t-work-in-magento-2" target="_blank">Check if cron is configured correctly.</a>'));
        } else {
            $timespan = $this->dateDiff($lastHeartbeat);
            if ($timespan <= 5 * 60) {
                $this->messageManager->addSuccess(__('Scheduler is working. (Last cron task: %1 minute(s) ago)', round($timespan / 60)));
            } elseif ($timespan > 5 * 60 && $timespan <= 60 * 60) {
// cron task wasn't executed in the last 5 minutes. Heartbeat schedule could have been modified to not run every five minutes!
                $this->messageManager->addNotice(__('Last cron task is older than %1 minutes.', round($timespan / 60)));
            } else {
// everything ok
                $this->messageManager->addError(__('Last cron task is older than one hour. Please check your settings and your configuration!'));
            }
        }
    }

    /**
     * Get the last cron task execution time
     * @return string
     */
    public function getLastHeartbeat()
    {
        if (version_compare($this->getMagentoVersion(), "2.2.0") >= 0) {
            $datetime = $this->scheduleFactory->create()->getLastHeartbeat();
            if (null === $datetime) {
                return false;
            }
            return $this->dateTime->date("Y-m-d H:i:s", strtotime($datetime) + $this->dateTime->getGmtOffSet('seconds'));
        } else {
            return $this->scheduleFactory->create()->getLastHeartbeat();
        }
    }

    /**
     * Get the difference between two dates
     * @param string $timeFirst
     * @param string $timeSecond
     * @return double
     */
    public function dateDiff($timeFirst, $timeSecond = null)
    {
        if ($timeSecond == null) {
            $timeSecond = $this->dateTime->date('Y-m-d H:i:s', $this->dateTime->timestamp() + $this->dateTime->getGmtOffset('seconds'));
        }
        $timeFirst = strtotime($timeFirst);
        $timeSecond = strtotime($timeSecond);

        return $timeSecond - $timeFirst;
    }

    /**
     * Transform a integer time to displayable string
     * @param double $time
     * @return string
     */
    public function getDuration($time)
    {
        if ($time < 60) {
            $time = ceil($time) . ' sec. ';
        } else {
            $time = floor($time / 60) . ' min. ' . ($time % 60) . ' sec.';
        }
        return $time;
    }
}
