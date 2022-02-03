<?php

namespace Amasty\Faq\Model\Frontend\Rating;

use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Model\Frontend\Rating\VotingRequest\VotingRequestInterface;
use Magento\Framework\Exception\CouldNotSaveException;

interface VotingProcessorInterface
{
    /**
     * Returns voting data contains data for rendering on frontend
     *
     * @param array $questionIds
     * @return array
     */
    public function getVotingData(array $questionIds): array;

    /**
     * Process saving question's vote
     *
     * @param VotingRequestInterface $request
     * @param QuestionInterface $question
     *
     * @throws CouldNotSaveException
     */
    public function saveVote(VotingRequestInterface $request, QuestionInterface $question): void;
}
