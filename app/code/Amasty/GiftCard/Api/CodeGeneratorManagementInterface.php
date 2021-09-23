<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Api;

/**
 * @api
 */
interface CodeGeneratorManagementInterface
{
    /**
     * @param int $codePoolId
     * @param int $qty
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateCodesForCodePool(int $codePoolId, int $qty): bool;

    /**
     * @param int $codePoolId
     * @param string $template
     * @param int $qty
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateCodesByTemplate(int $codePoolId, string $template, int $qty): bool;
}
