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
 * @package   mirasvit/module-sorting
 * @version   1.1.14
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Controller\Adminhtml\RankingFactor;

use Magento\Rule\Model\Condition\AbstractCondition;
use Mirasvit\Sorting\Controller\Adminhtml\RankingFactorAbstract;
use Mirasvit\Sorting\Factor\ProductRule\Rule;

class NewConditionHtml extends RankingFactorAbstract
{
    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));

        $class     = $typeArr[0];
        $attribute = false;
        if (count($typeArr) == 2) {
            $attribute = $typeArr[1];
        }

        $model = $this->context->getObjectManager()->create($class)
            ->setId($id)
            ->setType($class)
            ->setRule($this->context->getObjectManager()->create(Rule::class))
            ->setPrefix('conditions')
            ->setFormName($this->getRequest()->getParam('form_name', Rule::FORM_NAME));

        $model->setAttribute($attribute);

        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }

        $this->getResponse()->setBody($html);
    }
}
