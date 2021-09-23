<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Model\WarmRule;

use Magento\Rule\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Rule extends AbstractModel
{
    const FORM_NAME = 'cache_warmer_warm_rule_form';

    /**
     * @var Rule\Condition\CombineFactory
     */
    private $conditionCombineFactory;

    /**
     * Rule constructor.
     * @param Rule\Condition\CombineFactory $conditionCombineFactory
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        Rule\Condition\CombineFactory $conditionCombineFactory,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate
    ) {
        $this->conditionCombineFactory = $conditionCombineFactory;

        parent::__construct($context, $registry, $formFactory, $localeDate);
    }

    /**
     * @return \Magento\Rule\Model\Action\Collection|void
     */
    public function getActionsInstance()
    {
    }

    /**
     * @return \Magento\Rule\Model\Condition\Combine|Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->conditionCombineFactory->create();
    }
}
