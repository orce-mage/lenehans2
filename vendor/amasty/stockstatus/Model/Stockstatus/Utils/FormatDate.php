<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Stockstatus\Utils;

use DateTime;
use IntlDateFormatter;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class FormatDate
{
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    public function __construct(TimezoneInterface $localeDate)
    {
        $this->localeDate = $localeDate;
    }

    /**
     * Format date to IntlDateFormatter::MEDIUM , or to custom pattern if provided.
     * Date processed in config locale & timezone.
     *
     * @param string|null $date
     * @param string|null $pattern
     * @return string
     */
    public function format(?string $date, ?string $pattern = null): string
    {
        return $this->localeDate->formatDateTime(
            $date,
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::NONE,
            null,
            null,
            $pattern
        );
    }

    /**
     * If $date = null - current timestamp returned.
     *
     * @param string|null $date
     * @return int
     */
    public function getTimestamp(?string $date = null): int
    {
        return $this->localeDate->date($date)->getTimestamp();
    }

    /**
     * Return false if given value less than current day.
     *
     * @param string $value
     * @return bool
     */
    public function compareDateWithCurrentDay(string $value): bool
    {
        $dateToCompare = new DateTime($value);
        $currentDate = new DateTime('today');

        return $dateToCompare->getTimestamp() < $currentDate->getTimestamp();
    }
}
