<?php

declare(strict_types=1);

namespace MidoriWeb\Custom\Api\Data;

interface DeliveryMessageContentInterface
{
    const FROM_DAY = 'from_day';
    const TO_DAY = 'to_day';
    const TO_DATE = 'to_date';

    /**
     * Gets from day.
     *
     * @return string.
     */
    public function getFromDay();

    /**
     * Set from day.
     *
     * @param string $fromDay
     * @return $this
     */
    public function setFromDay(string $fromDay);

    /**
     * Gets to day.
     *
     * @return string.
     */
    public function getToDay();

    /**
     * Set to day.
     *
     * @param string $toDay
     * @return $this
     */
    public function setToDay(string $toDay);

    /**
     * Gets to date.
     *
     * @return string.
     */
    public function getToDate();

    /**
     * Set to date.
     *
     * @param string $toDate
     * @return $this
     */
    public function setToDate(string $toDate);
}
