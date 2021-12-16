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

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Registry;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Controller\Adminhtml\RankingFactorAbstract;
use Mirasvit\Sorting\Model\ConfigProvider;
use Mirasvit\Sorting\Model\Indexer;
use Mirasvit\Sorting\Repository\RankingFactorRepository;

class Reindex extends RankingFactorAbstract
{
    private $indexer;

    private $configProvider;

    public function __construct(
        Indexer $indexer,
        RankingFactorRepository $rankingFactorRepository,
        ConfigProvider $configProvider,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        Context $context
    ) {
        $this->indexer        = $indexer;
        $this->configProvider = $configProvider;

        parent::__construct($rankingFactorRepository, $registry, $resultForwardFactory, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->getParam(RankingFactorInterface::ID)) {
            $model = $this->initModel();

            if (!$model->getId()) {
                $this->messageManager->addErrorMessage((string)__('This factor no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }

            try {
                $this->reindex($model);
                $this->messageManager->addSuccessMessage((string)__('Reindex has been completed.'));
                $this->esWarning();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', [RankingFactorInterface::ID => $model->getId()]);
            }

            return $resultRedirect->setPath('*/*/');
        } else {
            try {
                foreach ($this->rankingFactorRepository->getCollection() as $rankingFactor) {
                    $this->reindex($rankingFactor);
                }
                $this->messageManager->addSuccessMessage((string)__('Reindex has been completed.'));
                $this->esWarning();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }

            return $resultRedirect->setPath('*/*/');
        }
    }

    private function reindex(RankingFactorInterface $rankingFactor)
    {
        $this->indexer->executeRankingFactor([$rankingFactor->getId()]);
    }

    private function esWarning()
    {
        if ($this->configProvider->isElasticSearch()) {
            $this->messageManager->addWarningMessage('Elasticsearch engine is used. You should also run search reindex to apply sorting. `bin/magento indexer:reindex catalogsearch_fulltext`');
        }
    }
}
