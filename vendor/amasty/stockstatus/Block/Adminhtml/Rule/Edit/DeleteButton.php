<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Block\Adminhtml\Rule\Edit;

class DeleteButton extends GenericButton
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];

        if ($ruleId = $this->getRuleId()) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => sprintf(
                    'deleteConfirm("%s", "%s")',
                    __('Are you sure you want to delete this rule?'),
                    $this->getUrl('*/*/delete', ['id' => $ruleId])
                ),
                'sort_order' => 20,
            ];
        }

        return $data;
    }
}
