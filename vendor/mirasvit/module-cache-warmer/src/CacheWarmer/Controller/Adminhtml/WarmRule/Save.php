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



namespace Mirasvit\CacheWarmer\Controller\Adminhtml\WarmRule;

use Mirasvit\CacheWarmer\Api\Data\WarmRuleInterface;
use Mirasvit\CacheWarmer\Controller\Adminhtml\AbstractWarmRule;
use Mirasvit\Core\Service\CompatibilityService;

class Save extends AbstractWarmRule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id             = $this->getRequest()->getParam(WarmRuleInterface::ID);
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getParams();

        if ($data) {
            $model          = $this->initModel();
            $resultRedirect = $this->resultRedirectFactory->create();

            if ($id && !$model) {
                $this->messageManager->addErrorMessage(__('This Job Rule no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }

            $data = $this->filter($data, $model);

            $model->setName($data[WarmRuleInterface::NAME])
                ->setIsActive($data[WarmRuleInterface::IS_ACTIVE])
                ->setPriority($data[WarmRuleInterface::PRIORITY])
                ->setConditionsSerialized($data[WarmRuleInterface::CONDITIONS_SERIALIZED])
                ->setHeaders($data['headers'])
                ->setVaryData($data['vary_data']);

            try {
                $this->WarmRuleRepository->save($model);

                $this->messageManager->addSuccessMessage(__('Job Rule was saved.'));

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', [WarmRuleInterface::ID => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [WarmRuleInterface::ID => $id]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect;
        }
    }

    /**
     * @param array            $data
     * @param WarmRuleInterface $model
     * @return array
     */
    private function filter(array $data, WarmRuleInterface $model)
    {
        $rule = $model->getRule();

        if (isset($data['rule']) && isset($data['rule']['conditions'])) {
            $rule->loadPost(['conditions' => $data['rule']['conditions']]);

            $conditions = $rule->getConditions()->asArray();

            $conditions = $this->serializer->serialize($conditions);

            $data[WarmRuleInterface::CONDITIONS_SERIALIZED] = $conditions;
        } else {
            $data[WarmRuleInterface::CONDITIONS_SERIALIZED] = \Zend_Json::encode([]);
        }

        if (isset($data['headers'])) {
            $data['headers'] = $this->textareaToArray($data['headers']);
        } else {
            $data['headers'] = $model->getHeaders();
        }

        if (isset($data['vary_data'])) {
            $data['vary_data'] = $this->textareaToArray($data['vary_data']);
        } else {
            $data['vary_data'] = $model->getHeaders();
        }

        return $data;
    }

    /**
     * @param string $str
     * @return array
     */
    private function textareaToArray($str)
    {
        $lines = array_filter(explode(PHP_EOL, $str));

        $data = [];

        foreach ($lines as $line) {
            $chunks = array_filter(explode(':', $line));

            if (count($chunks) != 2) {
                continue;
            }

            $data[trim($chunks[0])] = trim($chunks[1]);
        }

        return $data;
    }

}
