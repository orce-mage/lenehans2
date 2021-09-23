<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Api\Data;

interface CodePoolInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const CODE_POOL_ID = 'pool_id';
    const TITLE = 'title';
    const TEMPLATE = 'template';
    const CODE_POOL_RULE = 'code_pool_rule';
    /**#@-*/

    /**
     * @param int $id
     *
     * @return \Amasty\GiftCard\Api\Data\CodePoolInterface
     */
    public function setCodePoolId(int $id): \Amasty\GiftCard\Api\Data\CodePoolInterface;

    /**
     * @return int
     */
    public function getCodePoolId(): int;

    /**
     * @param string $title
     *
     * @return \Amasty\GiftCard\Api\Data\CodePoolInterface
     */
    public function setTitle(string $title): \Amasty\GiftCard\Api\Data\CodePoolInterface;

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @param string $template
     *
     * @return \Amasty\GiftCard\Api\Data\CodePoolInterface
     */
    public function setTemplate(string $template): \Amasty\GiftCard\Api\Data\CodePoolInterface;

    /**
     * @return string
     */
    public function getTemplate(): string;

    /**
     * @param CodePoolRuleInterface $rule
     *
     * @return \Amasty\GiftCard\Api\Data\CodePoolInterface
     */
    public function setCodePoolRule(
        \Amasty\GiftCard\Api\Data\CodePoolRuleInterface $rule
    ): \Amasty\GiftCard\Api\Data\CodePoolInterface;

    /**
     * @return \Amasty\GiftCard\Api\Data\CodePoolRuleInterface|null
     */
    public function getCodePoolRule();
}
