<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


declare(strict_types=1);

namespace Amasty\Number\Model\Number\Format;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Amasty\Number\Model\Number\AbstractFormatter;

class RandomFormatter extends AbstractFormatter
{
    const PLACEHOLDER = 'rand';

    /**
     * @param string $template
     * @return string
     * @throws LocalizedException
     */
    public function format(string $template): string
    {
        return $this->replacePlaceholder(
            $template,
            self::PLACEHOLDER,
            (string)Random::getRandomNumber(0, 9999)
        );
    }
}
