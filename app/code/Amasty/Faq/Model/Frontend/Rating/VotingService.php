<?php

declare(strict_types=1);

namespace Amasty\Faq\Model\Frontend\Rating;

use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Exceptions\VotingNotAllowedException;
use Amasty\Faq\Model\ConfigProvider;
use Amasty\Faq\Model\Frontend\Rating\VotingRequest\VotingRequestInterface;
use Amasty\Faq\Model\Voting;
use Magento\Customer\Model\Session;

class VotingService
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Voting
     */
    protected $voting;

    /**
     * @var VotingProcessorInterface[]
     */
    private $votingProcessors;

    public function __construct(
        ConfigProvider $configProvider,
        Session $session,
        Voting $voting,
        array $votingProcessors = []
    ) {
        $this->configProvider = $configProvider;
        $this->session = $session;
        $this->voting = $voting;
        foreach ($votingProcessors as $processor) {
            if (!($processor instanceof VotingProcessorInterface)) {
                throw new \LogicException(
                    sprintf('Voting processor must implement %s', VotingProcessorInterface::class)
                );
            }
        }
        $this->votingProcessors = $votingProcessors;
    }

    public function getVotingData(array $questionIds, $votingBehavior = null)
    {
        $processor = $this->getVotingProcessor($votingBehavior);

        return $processor->getVotingData($questionIds);
    }

    public function saveVotingData(
        VotingRequestInterface $request,
        QuestionInterface $question,
        string $votingBehavior = null
    ): void {
        $this->assertVotingAllowed();
        $processor = $this->getVotingProcessor($votingBehavior);
        $processor->saveVote($request, $question);
    }

    private function getVotingProcessor(?string $votingBehavior = null): VotingProcessorInterface
    {
        $behavior = $votingBehavior ?: $this->configProvider->getVotingBehavior();

        if (!isset($this->votingProcessors[$behavior])) {
            throw new \LogicException(
                sprintf('Voting processor is not defined for "%s" voting behavior', $behavior)
            );
        }

        return $this->votingProcessors[$behavior];
    }

    private function assertVotingAllowed()
    {
        if (!$this->configProvider->isGuestRatingAllowed() && !$this->session->isLoggedIn()) {
            throw new VotingNotAllowedException(
                __('Please, login to rate the question.'),
                'voting-not-allowed-for-guest'
            );
        }
    }
}
