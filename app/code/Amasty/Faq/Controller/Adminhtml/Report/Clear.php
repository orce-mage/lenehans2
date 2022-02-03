<?php

namespace Amasty\Faq\Controller\Adminhtml\Report;

use Amasty\Faq\Controller\Adminhtml\AbstractReports;
use Amasty\Faq\Model\VisitStatRepository;
use Magento\Backend\App\Action;

class Clear extends AbstractReports
{
    /**
     * @var VisitStatRepository
     */
    private $visitStatRepository;

    public function __construct(
        Action\Context $context,
        VisitStatRepository $visitStatRepository
    ) {
        parent::__construct($context);

        $this->visitStatRepository = $visitStatRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $result = $this->visitStatRepository->deleteAll();

        if ($result) {
            $this->messageManager->addSuccessMessage("Grid has been cleared.");
        } else {
            $this->messageManager->addErrorMessage("An error has occured.");
        }

        $this->_redirect->redirect(
            $this->getResponse(),
            'amastyfaq/report/index/'
        );
    }
}
