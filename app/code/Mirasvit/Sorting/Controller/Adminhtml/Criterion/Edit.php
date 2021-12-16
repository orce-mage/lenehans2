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
use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Controller\Adminhtml\CriterionAbstract;
use Mirasvit\Sorting\Model\ConfigProvider;
use Mirasvit\Sorting\Repository\CriterionRepository;
use Mirasvit\Sorting\Service\SampleService;

class Edit extends CriterionAbstract
{
    private $sampleService;

    public function __construct(
        SampleService $sampleService,
        CriterionRepository $criterionRepository,
        ConfigProvider $configProvider,
        ForwardFactory $resultForwardFactory,
        Context $context
    ) {
        $this->sampleService = $sampleService;

        parent::__construct($criterionRepository, $configProvider, $resultForwardFactory, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $model = $this->initModel();
        $id    = $this->getRequest()->getParam(CriterionInterface::ID);
        $code  = $this->getRequest()->getParam('code');

        if ($code) {
            $id = $this->sampleService->addNewCriterion($code);
            if ($id) {
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/edit', ['criterion_id' => $id]);
            }
        }

        if ($id && !$model) {
            $this->messageManager->addErrorMessage((string)__('This criteria no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($resultPage)
            ->getConfig()->getTitle()->prepend(
                $model->getId()
                    ? (string)__('Criteria "%1"', $model->getName())
                    : (string)__('New Criteria')
            );

        return $resultPage;
    }
}
