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
use Magento\Framework\Registry;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Repository\RankingFactorRepository;

abstract class RankingFactorAbstract extends Action
{
    protected $context;

    private $registry;

    protected $rankingFactorRepository;

    /**
     * @var \Magento\Backend\Model\Session
     */
    private $session;

    protected $resultForwardFactory;

    public function __construct(
        RankingFactorRepository $rankingFactorRepository,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        Context $context
    ) {
        $this->rankingFactorRepository = $rankingFactorRepository;
        $this->registry                = $registry;
        $this->resultForwardFactory    = $resultForwardFactory;

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
        $resultPage->getConfig()->getTitle()->prepend((string)__('Ranking Factors'));

        return $resultPage;
    }

    /**
     * @return RankingFactorInterface|false
     */
    protected function initModel()
    {
        $model = $this->rankingFactorRepository->create();

        if ($this->getRequest()->getParam(RankingFactorInterface::ID)) {
            $model = $this->rankingFactorRepository->get($this->getRequest()->getParam(RankingFactorInterface::ID));
        }

        $this->registry->register(RankingFactorInterface::class, $model);

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
