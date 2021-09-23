<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\GiftCard\Attribute\Backend\GiftCard;

use Amasty\GiftCard\Model\GiftCard\Attributes;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;

class Price extends \Magento\Catalog\Model\Product\Attribute\Backend\Price
{
    public function validate($object)
    {
        $rows = (array)$object->getData($this->getAttribute()->getName());
        $this->checkEmptyValues($rows);

        if (empty($rows)) {
            if (!$object->getData(Attributes::ALLOW_OPEN_AMOUNT)) {
                throw new LocalizedException(__('Amount should be specified or Open Amount should be allowed'));
            } else {
                $this->validateOpenAmount($object);
            }

            return $this;
        }
        $duplicates = [0 => []]; //initialize default website

        foreach ($rows as $row) {
            $websiteId = $row['website_id'];
            $row['value'] = str_replace(',', '', $row['value']);
            $value = (float)$row['value'];

            if (!isset($duplicates[$websiteId])) {
                $duplicates[$websiteId] = [];
            }

            if (in_array($value, $duplicates[$websiteId]) || in_array($value, $duplicates[0])) {
                throw new LocalizedException(__('Duplicate amount found.'));
            } else {
                $duplicates[$websiteId][] = $value;
            }
        }

        if ($object->getData(Attributes::ALLOW_OPEN_AMOUNT)) {
            $this->validateOpenAmount($object);
        }

        return $this;
    }

    /**
     * @param array $rows
     */
    private function checkEmptyValues(array &$rows)
    {
        foreach ($rows as $key => $row) {
            if (!$row['value']) {
                unset($rows[$key]);
            }
        }
    }

    /**
     * @param Product $object
     *
     * @throws LocalizedException
     */
    private function validateOpenAmount(Product $object)
    {
        $min = str_replace(',', '', $object->getData(Attributes::OPEN_AMOUNT_MIN));
        $max = str_replace(',', '', $object->getData(Attributes::OPEN_AMOUNT_MAX));

        if ($min && $min == $max) {
            throw new LocalizedException(__('Min and Max values of open amount can\'t be equal.'));
        }

        if ($min && $min > $max) {
            throw new LocalizedException(__('Min value of open amount must be lower then Max value.'));
        }
    }
}
