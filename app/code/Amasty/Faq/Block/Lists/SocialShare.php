<?php

namespace Amasty\Faq\Block\Lists;

use Amasty\Faq\Model\Config\ShortAnswerBehavior;
use Amasty\Faq\Model\SocialData;
use Magento\Framework\View\Element\Template;

class SocialShare extends \Amasty\Faq\Block\AbstractBlock
{
    /**
     * @var \Amasty\Faq\Model\SocialDataList
     */
    private $socialDataList;

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Amasty_Faq::lists/social.phtml';

    public function __construct(
        Template\Context $context,
        \Amasty\Faq\Model\SocialDataList $socialDataList,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->socialDataList = $socialDataList;
    }

    /**
     * @return SocialData[]
     */
    public function getButtons()
    {
        return $this->socialDataList->getActiveSocials();
    }

    /**
     * Return current url without query part
     *
     * @return string
     */
    protected function getCurrentUrl()
    {
        $url = $this->_urlBuilder->getCurrentUrl();
        // cut a tail (query) of url
        return preg_replace('/(\?|#).*/', '', $url);
    }

    /**
     * @param SocialData $button
     *
     * @return string
     */
    public function renderButtonHref(SocialData $button)
    {
        $template = $button->getHrefTemplate();
        $template = str_replace('#CURRENT_URL#', $this->getCurrentUrl(), $template);
        if (strpos($template, '#META_TITLE#') !== false) {
            $title = $this->encodeUrl($this->pageConfig->getTitle()->get());
            $template = str_replace('#META_TITLE#', $title, $template);
        }
        if (strpos($template, '#TITLE#') !== false) {
            /** @var \Magento\Theme\Block\Html\Title $headingBlock */
            if ($headingBlock = $this->getLayout()->getBlock('page.main.title')) {
                $title = $headingBlock->getPageTitle();
            } else {
                $title = $this->pageConfig->getTitle()->get();
            }
            $title = $this->encodeUrl($title);
            $template = str_replace('#TITLE#', $title, $template);
        }
        if (strpos($template, '#SHORT_ANSWER#') !== false) {
            $title = '';
            /** @var \Amasty\Faq\Block\View\Question $questionBlock */
            if ($questionBlock = $this->getLayout()->getBlock('question')) {
                $title = $this->encodeUrl(
                    $questionBlock->getCurrentQuestion()->prepareShortAnswer(
                        255,
                        ShortAnswerBehavior::SHOW_CUT_FULL_ANSWER
                    )
                );
            }
            $template = str_replace('#SHORT_ANSWER#', $title, $template);
        }

        return $template;
    }

    /**
     * Encode URL query
     *
     * @param string $query
     *
     * @return string
     */
    private function encodeUrl($query)
    {
        $query = urlencode($query);
        $query = str_replace('+', '%20', $query);

        return $query;
    }

    /**
     * Return additional HTML attributes of a element for social button
     *
     * @param SocialData $button
     *
     * @return string
     */
    public function getAttributesHtml(SocialData $button)
    {
        $attributes = '';
        if ($button->isOpenInNewTab()) {
            $attributes = 'target="_blank"';
        }

        return $attributes;
    }

    /**
     * get url to static image
     *
     * @param SocialData $button
     *
     * @return string
     */
    public function getImage(SocialData $button)
    {
        return $this->getViewFileUrl('Amasty_Faq::image/social/' . $button->getImgName());
    }
}
