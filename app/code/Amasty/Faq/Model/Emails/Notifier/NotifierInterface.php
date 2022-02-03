<?php
declare(strict_types=1);

namespace Amasty\Faq\Model\Emails\Notifier;

interface NotifierInterface
{
    /**
     * Sends email
     *
     * @param \Amasty\Faq\Api\Data\QuestionInterface $question
     */
    public function notify(\Amasty\Faq\Api\Data\QuestionInterface $question): void;
}
