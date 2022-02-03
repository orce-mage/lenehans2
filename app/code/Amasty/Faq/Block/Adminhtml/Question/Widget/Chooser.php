<?php

declare(strict_types=1);

namespace Amasty\Faq\Block\Adminhtml\Question\Widget;

use Amasty\Base\Model\Serializer;
use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Model\OptionSource\Question\Status;
use Amasty\Faq\Model\OptionSource\Question\Visibility;
use Amasty\Faq\Model\ResourceModel\Question\CollectionFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

class Chooser extends Extended
{
    const HEADER_TEMPLATE = 'Amasty_Faq::widget/questions/gridHeader.phtml';

    const FAQ_QUESTION_WIDGET_CHOOSER_PATH = 'amastyfaq/question_widget/chooser';

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        CollectionFactory $collectionFactory,
        Context $context,
        BackendHelper $backendHelper,
        Serializer $serializer,
        array $data = []
    ) {
        $this->_collection = $collectionFactory->create();
        $this->serializer = $serializer;

        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(true);
    }

    /**
     * @param AbstractElement $element
     *
     * @return AbstractElement
     * @throws LocalizedException
     */
    public function prepareElementHtml(AbstractElement $element)
    {
        $uniqId = $this->mathRandom->getUniqueHash($element->getId());
        /** @var QuestionList $questionsRowsBlock **/
        $questionsRowsBlock = $this->getLayout()->createBlock(QuestionList::class)->setUniqId($uniqId);
        $questionsRowsBlock
            ->setQuestions($element->getValue())
            ->setFieldsetId($this->getFieldsetId());
        $sourceUrl = $this->getUrl(
            self::FAQ_QUESTION_WIDGET_CHOOSER_PATH,
            ['uniq_id' => $uniqId]
        );

        $chooser = $this->getLayout()->createBlock(
            \Magento\Widget\Block\Adminhtml\Widget\Chooser::class
        )->setElement(
            $element
        )->setConfig(
            $this->getConfig()
        )->setFieldsetId(
            $this->getFieldsetId()
        )->setSourceUrl(
            $sourceUrl
        )->setUniqId(
            $uniqId
        )->setLabel(' ');

        $element->setData('after_element_html', $questionsRowsBlock->toHtml() . $chooser->toHtml());

        return $element;
    }

    /**
     * @return Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'question_ids',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'question_ids',
                'inline_css' => 'checkbox entities',
                'field_name' => 'in_questions',
                'align' => 'center',
                'index' => QuestionInterface::QUESTION_ID,
                'use_index' => true
            ]
        )->addColumn(
            QuestionInterface::QUESTION_ID,
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => QuestionInterface::QUESTION_ID,
                'filter_index' => 'main_table.' . QuestionInterface::QUESTION_ID,
                'header_css_class' => 'col-' . QuestionInterface::QUESTION_ID,
                'column_css_class' => 'col-' . QuestionInterface::QUESTION_ID
            ]
        )->addColumn(
            QuestionInterface::TITLE,
            [
                'header' => __('Question'),
                'sortable' => true,
                'index' => QuestionInterface::TITLE,
                'filter_index' => 'main_table.' . QuestionInterface::TITLE,
                'header_css_class' => 'col-' . QuestionInterface::TITLE,
                'column_css_class' => 'col-' . QuestionInterface::TITLE
            ]
        )->addColumn(
            QuestionInterface::URL_KEY,
            [
                'header' => __('Url Key'),
                'sortable' => true,
                'index' => QuestionInterface::URL_KEY,
                'filter_index' => 'main_table.' . QuestionInterface::URL_KEY,
                'header_css_class' => 'col-' . QuestionInterface::URL_KEY,
                'column_css_class' => 'col-' . QuestionInterface::URL_KEY
            ]
        )->addColumn(
            QuestionInterface::STATUS,
            [
                'header' => __('Status'),
                'sortable' => true,
                'index' => QuestionInterface::STATUS,
                'filter_index' => 'main_table.' . QuestionInterface::STATUS,
                'header_css_class' => 'col-' . QuestionInterface::STATUS,
                'column_css_class' => 'col-' . QuestionInterface::STATUS,
                'options' => Status::class
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function toHtml()
    {
        /** @var Template $gridHeader * */
        $gridHeader = $this->getLayout()
            ->createBlock(Template::class)
            ->setTemplate(self::HEADER_TEMPLATE)
            ->setChooserId($this->getId());

        return $gridHeader->toHtml() . parent::toHtml();
    }

    /**
     * @return $this|Extended
     */
    protected function _prepareCollection()
    {
        $this->_collection->addFieldToFilter(
            QuestionInterface::VISIBILITY,
            ['neq' => Visibility::VISIBILITY_NONE]
        );

        return $this;
    }

    public function getGridUrl(): string
    {
        return $this->getUrl(self::FAQ_QUESTION_WIDGET_CHOOSER_PATH, ['_current' => true]);
    }
}
