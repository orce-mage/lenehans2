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

namespace Mirasvit\Sorting\Controller\Adminhtml\Criterion;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Controller\Adminhtml\CriterionAbstract;
use Mirasvit\Sorting\Model\ConfigProvider;
use Mirasvit\Sorting\Repository\CriterionRepository;

class InlineEdit extends CriterionAbstract
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    public function __construct(
        JsonFactory $jsonFactory,
        CriterionRepository $criterionRepository,
        ConfigProvider $configProvider,
        ForwardFactory $resultForwardFactory,
        Context $context
    ) {
        $this->jsonFactory = $jsonFactory;

        parent::__construct($criterionRepository, $configProvider, $resultForwardFactory, $context);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $messages   = [];

        $postItems = $this->getRequest()->getParam('items', []);

        foreach ($postItems as $criterionId => $data) {
            $model = $this->criterionRepository->get($criterionId);

            if (!$model) {
                $messages[] = __('This criteria no longer exists.');
            }

            if (isset($data[CriterionInterface::NAME])) {
                $model->setName($data[CriterionInterface::NAME]);
            }

            if (isset($data[CriterionInterface::IS_ACTIVE])) {
                $model->setIsActive($data[CriterionInterface::IS_ACTIVE]);
            }

            if (isset($data[CriterionInterface::IS_DEFAULT])) {
                $model->setIsDefault($data[CriterionInterface::IS_DEFAULT]);
            }

            if (isset($data[CriterionInterface::IS_SEARCH_DEFAULT])) {
                $model->setIsSearchDefault($data[CriterionInterface::IS_SEARCH_DEFAULT]);
            }

            if (isset($data[CriterionInterface::CODE])) {
                $model->setCode($data[CriterionInterface::CODE]);
            }

            if (isset($data[CriterionInterface::POSITION])) {
                $model->setPosition($data[CriterionInterface::POSITION]);
            }

            try {
                $this->criterionRepository->save($model);
            } catch (\Exception $e) {
                $messages[] = $e->getMessage();
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error'    => count($messages) ? true : false,
        ]);
    }
}
