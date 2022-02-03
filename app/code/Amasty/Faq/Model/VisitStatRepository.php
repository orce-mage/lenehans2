<?php

namespace Amasty\Faq\Model;

use Amasty\Faq\Api\Data\VisitStatInterface;
use Amasty\Faq\Api\VisitStatRepositoryInterface;
use Amasty\Faq\Model\ResourceModel\VisitStat as Resource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class VisitStatRepository implements VisitStatRepositoryInterface
{
    /**
     * Model data storage
     *
     * @var array
     */
    private $visitStat;

    /**
     * @var VisitStatFactory
     */
    private $visitStatFactory;

    /**
     * @var Resource
     */
    private $visitStatResource;

    public function __construct(
        Resource $visitStatResource,
        VisitStatFactory $visitStatFactory
    ) {
        $this->visitStatResource = $visitStatResource;
        $this->visitStatFactory = $visitStatFactory;
    }

    /**
     * @inheritdoc
     */
    public function getById($visitId)
    {
        if (!isset($this->visitStat[$visitId])) {
            /** @var \Amasty\Faq\Model\VisitStat $stat */
            $stat = $this->visitStatFactory->create();
            $this->visitStatResource->load($stat, $visitId);
            if (!$stat->getVisitId()) {
                throw new NoSuchEntityException(__('Visit with specified ID "%1" not found.', $visitId));
            }
            $this->visitStat[$visitId] = $stat;
        }

        return $this->visitStat[$visitId];
    }

    /**
     * @inheritdoc
     */
    public function save(VisitStatInterface $visitStat)
    {
        try {
            if ($visitStat->getVisitId()) {
                $visitStat = $this->getById($visitStat->getVisitId())->addData($visitStat->getData());
            }
            $this->visitStatResource->save($visitStat);
            unset($this->visitStat[$visitStat->getVisitId()]);
        } catch (\Exception $e) {
            if ($visitStat->getVisitId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save visit with ID %1. Error: %2',
                        [$visitStat->getVisitId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new visit. Error: %1', $e->getMessage()));
        }

        return $visitStat;
    }

    /**
     * @inheritdoc
     */
    public function deleteAll()
    {
        try {
            $this->visitStatResource->clearTable();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
