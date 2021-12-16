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

use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Controller\Adminhtml\RankingFactorAbstract;

class Delete extends RankingFactorAbstract
{
    public function execute()
    {
        $id = $this->getRequest()->getParam(RankingFactorInterface::ID);

        if ($id) {
            try {
                $model = $this->rankingFactorRepository->get($id);
                $this->rankingFactorRepository->delete($model);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }

            $this->messageManager->addSuccessMessage((string)__('Ranking Factor was removed'));
        } else {
            $this->messageManager->addErrorMessage((string)__('Please select factor'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
