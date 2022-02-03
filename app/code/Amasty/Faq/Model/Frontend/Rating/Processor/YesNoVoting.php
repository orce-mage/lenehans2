<?php

declare(strict_types=1);

namespace Amasty\Faq\Model\Frontend\Rating\Processor;

use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Exceptions\VotingNotAllowedException;
use Amasty\Faq\Model\Frontend\Rating\VotingProcessorInterface;
use Amasty\Faq\Model\Frontend\Rating\VotingRequest\VotingRequestInterface;
use Amasty\Faq\Model\QuestionRepository;
use Amasty\Faq\Model\ResourceModel\Question;
use Amasty\Faq\Model\Voting;

class YesNoVoting implements VotingProcessorInterface
{
    /**
     * @var Question\CollectionFactory
     */
    protected $questionCollectionFactory;

    /**
     * @var QuestionRepository
     */
    protected $repository;

    /**
     * @var Voting
     */
    protected $voting;

    public function __construct(
        Question\CollectionFactory $questionCollectionFactory,
        QuestionRepository $repository,
        Voting $voting
    ) {
        $this->questionCollectionFactory = $questionCollectionFactory;
        $this->repository = $repository;
        $this->voting = $voting;
    }

    public function getVotingData(array $questionIds): array
    {
        return array_map(function ($question) {
            $questionId = $question[QuestionInterface::QUESTION_ID];
            return [
                'id' => $questionId,
                'positiveRating' => $question[QuestionInterface::POSITIVE_RATING],
                'negativeRating' => $question[QuestionInterface::NEGATIVE_RATING],
                'isVoted' => $this->voting->isVotedQuestion($questionId),
                'isPositiveVoted' => $this->voting->isPositiveVotedQuestion($questionId)
            ];
        }, $this->getQuestionCollection($questionIds)->getData());
    }

    public function saveVote(VotingRequestInterface $request, QuestionInterface $question): void
    {
        if ($this->voting->isVotedQuestion($question->getQuestionId())) {
            throw new VotingNotAllowedException(__('You already voted.'), 'already-voted');
        }

        if ($request->getValue() > 0) {
            $question->setPositiveRating($question->getPositiveRating() + 1);
            $this->voting->setVotedQuestion($question->getQuestionId());
        } else {
            $question->setNegativeRating($question->getNegativeRating() + 1);
            $this->voting->setVotedQuestion($question->getQuestionId(), false);
        }

        $this->repository->save($question);
    }

    protected function getQuestionCollection(array $questionIds)
    {
        $questionCollection = $this->questionCollectionFactory->create();
        $questionCollection->addFieldToFilter(QuestionInterface::QUESTION_ID, ['in' => $questionIds]);
        $questionCollection->addFieldToSelect([
            QuestionInterface::QUESTION_ID,
            QuestionInterface::POSITIVE_RATING,
            QuestionInterface::NEGATIVE_RATING
        ]);

        return $questionCollection;
    }
}
