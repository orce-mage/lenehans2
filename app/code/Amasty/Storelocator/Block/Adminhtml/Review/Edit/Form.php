<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Block\Adminhtml\Review\Edit;

use Amasty\Storelocator\Model\Review;
use \Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Form
 */
class Form extends Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Amasty\Storelocator\Model\Repository\ReviewRepository
     */
    private $reviewRepository;

    /**
     * @var \Amasty\Storelocator\Model\ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Amasty\Storelocator\Model\Config\Source\ReviewStatuses
     */
    private $reviewStatuses;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Amasty\Storelocator\Model\Repository\ReviewRepository $reviewRepository,
        \Amasty\Storelocator\Model\ReviewFactory $reviewFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Amasty\Storelocator\Model\Config\Source\ReviewStatuses $reviewStatuses,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->reviewRepository = $reviewRepository;
        $this->reviewFactory = $reviewFactory;
        $this->customerRepository = $customerRepository;
        $this->reviewStatuses = $reviewStatuses;
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('amlocator_reviews_form');
        $this->setTitle(__('Reviews'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        if ($id = (int)$this->getRequest()->getParam('id')) {
            $model = $this->reviewRepository->getById($id);
        } else {
            $model = $this->reviewFactory->create();
        }
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Review Details'), 'class' => 'fieldset-wide']
        );
        $this->addIdField($fieldset, $model);
        $this->addCustomerField($fieldset, $model);
        $this->addRatingField($fieldset);
        $this->addStatusField($fieldset);
        $this->addReviewField($fieldset);

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param Fieldset $fieldset
     * @param Review $model
     */
    private function addIdField($fieldset, $model)
    {
        $fieldset->addField(
            'id',
            'hidden',
            [
                'name' => 'id',
                'value' => $model->getId()
            ]
        );
    }

    /**
     * @param Fieldset $fieldset
     * @param Review $model
     */
    private function addCustomerField($fieldset, $model)
    {
        try {
            $customer = $this->customerRepository->getById($model->getCustomerId());
            $customerText = __(
                '<a href="%1" onclick="this.target=\'blank\'">%2 %3</a>',
                $this->getUrl('customer/index/edit', ['id' => $customer->getId(), 'active_tab' => 'review']),
                $this->escapeHtml($customer->getFirstname()),
                $this->escapeHtml($customer->getLastname())
            );
        } catch (NoSuchEntityException $e) {
            $customerText = "Deleted";
        }
        $fieldset->addField(
            'customer',
            'note',
            [
                'label' => __('Author'),
                'text' => $customerText
            ]
        );
    }

    /**
     * @param Fieldset $fieldset
     */
    private function addRatingField($fieldset)
    {
        $fieldset->addField(
            'detailed-rating',
            'note',
            [
                'label'    => __('Rating'),
                'required' => true,
                'text'     => '<div id="rating_detail">' .
                    $this->getLayout()
                        ->createBlock(\Amasty\Storelocator\Block\Adminhtml\RatingStars::class)->toHtml() . '</div>'
            ]
        );
    }

    /**
     * @param Fieldset $fieldset
     */
    private function addStatusField($fieldset)
    {
        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'required' => true,
                'name' => 'status',
                'values' => $this->reviewStatuses->toOptionArray()
            ]
        );
    }

    /**
     * @param Fieldset $fieldset
     */
    private function addReviewField($fieldset)
    {
        $fieldset->addField(
            'review_text',
            'textarea',
            [
                'label' => __('Review'),
                'required' => true,
                'name' => 'review_text',
                'style' => 'height:24em;'
            ]
        );
    }
}
