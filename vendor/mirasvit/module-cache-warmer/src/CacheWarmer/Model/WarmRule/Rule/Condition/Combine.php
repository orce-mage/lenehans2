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



namespace Mirasvit\CacheWarmer\Model\WarmRule\Rule\Condition;

use Magento\Rule\Model\Condition\Context;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var PageCondition
     */
    private $pageCondition;

    /**
     * @var string
     */
    private $ruleType;

    /**
     * Combine constructor.
     * @param PageCondition $pageCondition
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        PageCondition $pageCondition,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->pageCondition     = $pageCondition;

        $this->setData('type', self::class);
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setRuleType($type)
    {
        $this->ruleType = $type;

        return $this;
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $pageAttributes  = $this->pageCondition->loadAttributeOptions()->getData('attribute_option');

        $attributes = [];

        foreach ($pageAttributes as $code => $label) {
            $attributes['page'][] = [
                'value' => PageCondition::class . '|' . $code,
                'label' => $label,
            ];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, [
            [
                'value' => self::class,
                'label' => __('Conditions Combination'),
            ],
        ]);


        $conditions = array_merge_recursive($conditions, [
            [
                'label' => __('Page Attributes'),
                'value' => $attributes['page'],
            ],
        ]);

        return $conditions;
    }
}
