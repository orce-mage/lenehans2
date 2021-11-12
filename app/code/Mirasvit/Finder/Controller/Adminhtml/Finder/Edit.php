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

use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Finder\Api\Data\FinderInterface;

class Edit extends AbstractFinder implements ActionInterface
{
    public function execute()
    {
        $id    = $this->getRequest()->getParam(FinderInterface::ID);
        $model = $this->initModel();

        if ($id && !$model->getId()) {
            $this->messageManager->addErrorMessage((string)__('This finder no longer exists.'));

            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($page, $model->getName() ? $model->getName() : 'New Finder');

        return $page;
    }
}
