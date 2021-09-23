<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\CodePool;

use Amasty\GiftCard\Api\Data\CodePoolRuleInterface;
use Magento\Rule\Model\AbstractModel;

class CodePoolRule extends AbstractModel implements CodePoolRuleInterface
{
    /**#@+
     * Constants
     */
    const CURRENT_RULE = 'current_amgcard_codepoolrule';
    const FORM_NAMESPACE = 'amgcard_codepool_form';
    /**#@-*/

    protected $_eventPrefix = 'codepool_rule';

    protected $_eventObject = 'rule';

    /**
     * @var Condition\CombineFactory
     */
    private $combineFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Amasty\GiftCard\Model\CodePool\Condition\CombineFactory $combineFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
        $this->combineFactory = $combineFactory;
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\CodePoolRule::class);
        $this->setIdFieldName(CodePoolRuleInterface::RULE_ID);
    }

    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    public function getActionsInstance()
    {
        return $this->combineFactory->create();
    }

    public function setRuleId(int $id): CodePoolRuleInterface
    {
        return $this->setData(CodePoolRuleInterface::RULE_ID, $id);
    }

    public function getRuleId(): int
    {
        return (int)$this->_getData(CodePoolRuleInterface::RULE_ID);
    }

    public function setCodePoolId(int $id): CodePoolRuleInterface
    {
        return $this->setData(CodePoolRuleInterface::CODE_POOL_ID, $id);
    }

    public function getCodePoolId(): int
    {
        return (int)$this->_getData(CodePoolRuleInterface::CODE_POOL_ID);
    }

    public function setConditionsSerialized(string $conditions): CodePoolRuleInterface
    {
        return $this->setData(CodePoolRuleInterface::CONDITIONS_SERIALIZED, $conditions);
    }

    public function getConditionsSerialized()
    {
        return $this->_getData(CodePoolRuleInterface::CONDITIONS_SERIALIZED);
    }
}
