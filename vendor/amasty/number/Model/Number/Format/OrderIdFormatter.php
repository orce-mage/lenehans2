<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


declare(strict_types=1);

namespace Amasty\Number\Model\Number\Format;

use Amasty\Number\Model\Number\AbstractFormatter;

class OrderIdFormatter extends AbstractFormatter
{
    const PLACEHOLDER = 'order_id';

    /**
     * @param string $template
     * @return string
     */
    public function format(string $template): string
    {
        $replacement = '';

        if ($this->getSequence()->getOrder()) {
            $replacement = (string)$this->getSequence()->getOrder()->getId();
        }

        return $this->replacePlaceholder($template, self::PLACEHOLDER, $replacement);
    }
}
