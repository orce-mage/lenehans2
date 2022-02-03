<?php
declare(strict_types=1);

namespace Amasty\Faq\Model\Frontend\Rating\VotingRequest;

use Magento\Framework\Api\AbstractSimpleObject;

class VotingRequest extends AbstractSimpleObject implements VotingRequestInterface
{
    const QUESTION_ID = 'question_id';
    const VALUE = 'value';
    const IS_REVOTE = 'is_revote';
    const OLD_VALUE = 'old_value';

    public function getQuestionId(): int
    {
        return (int)$this->_get(self::QUESTION_ID);
    }

    public function getValue(): string
    {
        return (string)$this->_get(self::VALUE);
    }

    public function isRevote(): bool
    {
        return (bool)$this->_get(self::IS_REVOTE);
    }

    public function getOldValue(): ?string
    {
        return $this->_get(self::OLD_VALUE);
    }
}
