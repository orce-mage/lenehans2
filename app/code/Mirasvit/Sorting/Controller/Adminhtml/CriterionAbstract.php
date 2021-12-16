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

namespace Mirasvit\Sorting\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Model\ConfigProvider;
use Mirasvit\Sorting\Repository\CriterionRepository;

abstract class CriterionAbstract extends Action
{
    protected $criterionRepository;

    protected $configProvider;

    protected $resultForwardFactory;

    private   $context;

    /**
     * @var \Magento\Backend\Model\Session
     */
    private $session;

    public function __construct(
        CriterionRepository $criterionRepository,
        ConfigProvider $configProvider,
        ForwardFactory $resultForwardFactory,
        Context $context
    ) {
        $this->criterionRepository  = $criterionRepository;
        $this->configProvider       = $configProvider;
        $this->resultForwardFactory = $resultForwardFactory;

        $this->context = $context;
        $this->session = $context->getSession();

        parent::__construct($context);
    }

    /**
     * Initialize page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Mirasvit_Sorting::sorting');

        $resultPage->getConfig()->getTitle()->prepend((string)__('Improved Sorting'));
        $resultPage->getConfig()->getTitle()->prepend((string)__('Sorting Criteria'));

        return $resultPage;
    }

    /**
     * @return CriterionInterface|false
     */
    protected function initModel()
    {
        $model = $this->criterionRepository->create();

        if ($this->getRequest()->getParam(CriterionInterface::ID)) {
            $model = $this->criterionRepository->get($this->getRequest()->getParam(CriterionInterface::ID));
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Sorting::sorting');
    }
}
