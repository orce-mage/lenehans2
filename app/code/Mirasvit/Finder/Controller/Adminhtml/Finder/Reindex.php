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
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Finder\Controller\Adminhtml\Finder;

use Magento\Backend\App\Action\Context;
use Mirasvit\Finder\Api\Data\FinderInterface;
use Mirasvit\Finder\Model\Index\Indexer;
use Mirasvit\Finder\Repository\FinderRepository;

class Reindex extends AbstractFinder
{
    private $indexer;

    public function __construct(
        Indexer $indexer,
        FinderRepository $finderRepository,
        Context $context
    ) {
        $this->indexer = $indexer;

        parent::__construct($finderRepository, $context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $model = $this->initModel();
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage((string)__('This Finder no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }

            $this->indexer->reindex($model);

            $this->messageManager->addSuccessMessage((string)__('"%1" - reindex has been completed.', $model->getName()));

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/edit', [
            FinderInterface::ID => $this->getRequest()->getParam(FinderInterface::ID),
        ]);
    }
}
