<?php

declare(strict_types=1);

namespace Amasty\Faq\Model\Frontend\Rating\Processor;

use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Model\Frontend\Rating\VotingRequest\VotingRequestInterface;

class Average extends YesNoVoting
{
    public function getVotingData(array $questionIds): array
    {
        return array_map(function ($question) {
            $questionId = $question[QuestionInterface::QUESTION_ID];
            return [
                'id' => $questionId,
                'isVoted' => $this->voting->isVotedQuestion($questionId),
                'average' => $question[QuestionInterface::AVERAGE_RATING] ?? 0,
                'total' => $question[QuestionInterface::AVERAGE_TOTAL] ?? 0,
            ];
        }, $this->getQuestionCollection($questionIds)->getData());
    }

    public function saveVote(VotingRequestInterface $request, QuestionInterface $question): void
    {
        if ($voteValue = (int)$request->getValue()) {
            $average = $question->getAverageRating();
            $total = $question->getAverageTotal();
            if ($request->isRevote() && $oldVote = (int)$request->getOldValue()) {
                $newAverage = ($average * $total + $voteValue - $oldVote) / $total;
            } else {
                $newAverage = ($average * $total + $voteValue) / ++$total;
            }
            $question->setAverageRating($newAverage);
            $question->setAverageTotal($total);
            $this->repository->save($question);
        }
    }

    protected function getQuestionCollection(array $questionIds)
    {
        $questionCollection = parent::getQuestionCollection($questionIds);
        $questionCollection->addFieldToSelect([
            QuestionInterface::AVERAGE_RATING,
            QuestionInterface::AVERAGE_TOTAL,
        ]);

        return $questionCollection;
    }
}
