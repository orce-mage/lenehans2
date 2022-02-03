<?php
declare(strict_types=1);

namespace Amasty\Faq\Model\Emails;

class NotifierProvider
{
    const TYPE_ADMIN = 'admin';
    const TYPE_CUSTOMER = 'customer';

    /**
     * @var array
     */
    private $notifiers;

    public function __construct(
        array $notifiers = []
    ) {
        $this->initializeNotifiers($notifiers);
    }

    public function get(string $type): ?Notifier\NotifierInterface
    {
        return $this->notifiers[$type] ?? null;
    }

    private function initializeNotifiers(array $notifiers): void
    {
        foreach ($notifiers as $type => $notifier) {
            if (!$notifier instanceof Notifier\NotifierInterface) {
                throw new \LogicException(sprintf('Notifier must implement %s', Notifier\NotifierInterface::class));
            }
            $this->notifiers[$type] = $notifier;
        }
    }
}
