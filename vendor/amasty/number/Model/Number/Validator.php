<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


declare(strict_types=1);

namespace Amasty\Number\Model\Number;

use Amasty\Number\Exceptions\InvalidNumberFormat;
use Amasty\Number\Model\ConfigProvider;
use Amasty\Number\Model\Number\Format\CounterFormatter;
use Amasty\Number\Model\Number\Format\DateFormatter;
use Amasty\Number\Model\Number\Format\RandomFormatter;

class Validator
{
    const EXAMPLE_FORMATS = [
        ConfigProvider::ORDER_TYPE => 'ORD-{yy}-{mm}-{dd}-{counter}',
        ConfigProvider::INVOICE_TYPE => 'INV-{yy}-{mm}-{dd}-{counter}',
        ConfigProvider::SHIPMENT_TYPE => 'SHI-{yy}-{mm}-{dd}-{counter}',
        ConfigProvider::CREDITMEMO_TYPE => 'MEMO-{yy}-{mm}-{dd}-{counter}',
    ];
    const REQUIRED_FIELDS = [
        CounterFormatter::PLACEHOLDER,
        RandomFormatter::PLACEHOLDER
    ];

    /**
     * @param string $entityType
     * @param string $pattern
     * @return bool
     * @throws InvalidNumberFormat
     */
    public function validatePattern(string $entityType, string $pattern): bool
    {
        if (preg_match_all('|{(.*)}|Uis', $pattern, $parts)) {
            $result = false;

            foreach ($parts[1] as $placeholder) {
                if (in_array($placeholder, self::REQUIRED_FIELDS)) {
                    $result = true;
                }
            }

            if (!$result) {
                throw new InvalidNumberFormat(__(
                    'Invalid %1 number pattern, it must include one of the following parts: %2',
                    $entityType,
                    implode(' or ', $this->wrapPlaceholder(self::REQUIRED_FIELDS))
                ));
            }
        } else {
            throw new InvalidNumberFormat(__(
                'Invalid %s number pattern. Please change the pattern. Example number format: %s',
                $entityType,
                Validator::EXAMPLE_FORMATS[$entityType]
            ));
        }

        return $result;
    }

    /**
     * @param $placeholder
     * @return array|string
     */
    private function wrapPlaceholder($placeholder)
    {
        if (is_array($placeholder)) {
            return array_map(
                function ($item) {
                    return '{' . $item . '}';
                },
                $placeholder
            );
        } elseif (is_string($placeholder)) {
            return '{' . $placeholder . '}';
        } else {
            return '';
        }
    }
}
