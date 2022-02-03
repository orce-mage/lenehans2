<?php

namespace Amasty\Faq\Model;

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class Voting
{
    const VOTED_POSITIVE_QUESTIONS_KEY = 'amfaq_questions';
    const VOTED_NEGATIVE_QUESTIONS_KEY = 'amfaq_questions_n';

    /**
     * @var array
     */
    private $positiveVotedQuestions = [];

    /**
     * @var array
     */
    private $negativeVotedQuestions = [];

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->setVotedQuestions();
    }

    /**
     * @param int $questionId
     *
     * @return bool|int
     */
    public function isVotedQuestion($questionId)
    {
        if (in_array($questionId, $this->positiveVotedQuestions)
            || in_array($questionId, $this->negativeVotedQuestions)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param int $questionId
     *
     * @return bool
     */
    public function isPositiveVotedQuestion($questionId)
    {
        if (in_array($questionId, $this->positiveVotedQuestions)) {
            return true;
        }

        return false;
    }

    /**
     * @param int $questionId
     * @param bool $isPositive
     */
    public function setVotedQuestion($questionId, $isPositive = true)
    {
        if ($isPositive) {
            $votedQuestions = &$this->positiveVotedQuestions;
        } else {
            $votedQuestions = &$this->negativeVotedQuestions;
        }

        $votedQuestions[] = $questionId;
        array_walk($votedQuestions, function (&$questionId) {
            $questionId = (int)$questionId;
        });
        $votedQuestions = array_unique($votedQuestions);

        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setPath('/')
            ->setDurationOneYear();

        $this->cookieManager->setPublicCookie(
            $isPositive ? self::VOTED_POSITIVE_QUESTIONS_KEY : self::VOTED_NEGATIVE_QUESTIONS_KEY,
            implode(',', $votedQuestions),
            $cookieMetadata
        );
    }

    /**
     * Initialize array of voted questions
     */
    private function setVotedQuestions()
    {
        if ($votedQuestions = $this->cookieManager->getCookie(self::VOTED_POSITIVE_QUESTIONS_KEY)) {
            $votedQuestions = explode(',', $votedQuestions);
            foreach ($votedQuestions as $questionId) {
                $this->positiveVotedQuestions[] = (int)$questionId;
            }
        }
        if ($votedQuestions = $this->cookieManager->getCookie(self::VOTED_NEGATIVE_QUESTIONS_KEY)) {
            $votedQuestions = explode(',', $votedQuestions);
            foreach ($votedQuestions as $questionId) {
                $this->negativeVotedQuestions[] = (int)$questionId;
            }
        }
    }

    /**
     * @return int[]
     */
    public function getPositiveVotedQuestions()
    {
        return $this->positiveVotedQuestions;
    }

    /**
     * @param int[] $positiveVotedQuestions
     */
    public function setPositiveVotedQuestions($positiveVotedQuestions)
    {
        $this->positiveVotedQuestions = $positiveVotedQuestions;
    }

    /**
     * @return array
     */
    public function getNegativeVotedQuestions()
    {
        return $this->negativeVotedQuestions;
    }

    /**
     * @param int[] $negativeVotedQuestions
     */
    public function setNegativeVotedQuestions($negativeVotedQuestions)
    {
        $this->positiveVotedQuestions = $negativeVotedQuestions;
    }
}
