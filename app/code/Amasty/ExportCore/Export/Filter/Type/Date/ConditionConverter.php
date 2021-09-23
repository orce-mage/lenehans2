<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Filter\Type\Date;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class ConditionConverter
{
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    public function __construct(
        TimezoneInterface $localeDate
    ) {
        $this->localeDate = $localeDate;
    }

    /**
     * Convert a date condition to a date and time condition, if needed
     *
     * @param $filterCondition
     * @param $filterValue
     * @param bool $includeTime
     * @return array
     */
    public function convert($filterCondition, $filterValue, $includeTime = true): array
    {
        $condition = [];
        if (!$includeTime) {
            $condition[$filterCondition] = $this->convertDate($filterValue, 0, 0, 0, false);

            return $condition;
        }

        switch ($filterCondition) {
            case 'eq':
                $condition['from'] = $this->getFromDateTime($filterValue);
                $condition['to'] = $this->getToDateTime($filterValue);
                break;
            case 'neq':
                $condition[] = ['lt' => $this->getFromDateTime($filterValue)];
                $condition[] = ['gt' => $this->getToDateTime($filterValue)];
                break;
            case 'lteq':
            case 'gt':
                $condition[$filterCondition] = $this->getToDateTime($filterValue);
                break;
            case 'gteq':
            case 'lt':
            default:
                $condition[$filterCondition] = $this->getFromDateTime($filterValue);
        }

        return $condition;
    }

    /**
     * Get date including time as `00:00:00` in UTC timezone
     *
     * @param $filterValue
     * @return \DateTime|null
     */
    private function getFromDateTime($filterValue)
    {
        return $this->convertDate($filterValue, 0, 0, 0);
    }

    /**
     * Get date including time as `23:59:59` in UTC timezone
     *
     * @param $filterValue
     * @return \DateTime|null
     */
    private function getToDateTime($filterValue)
    {
        return $this->convertDate($filterValue, 23, 59, 59);
    }

    /**
     * Convert given date to default (UTC) timezone
     *
     * @param mixed $date
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @param boolean $includeTime
     * @return \DateTime|null
     */
    private function convertDate($date, $hour = 0, $minute = 0, $second = 0, $includeTime = true)
    {
        try {
            $dateObj = $this->localeDate->date($date, null, false);
            if ($includeTime) {
                $dateObj->setTime($hour, $minute, $second);
            }
            $dateObj->setTimezone(new \DateTimeZone('UTC'));
            return $dateObj;
        } catch (\Exception $e) {
            return null;
        }
    }
}
