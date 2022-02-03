<?php

namespace Amasty\Faq\Setup\Operation;

use Amasty\Faq\Api\Data\CategoryInterface;
use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Api\Data\VisitStatInterface;
use Magento\Framework\DB\Ddl\TriggerFactory;
use Magento\Framework\DB\Ddl\Trigger;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddTriggers
{
    /**
     * @var TriggerFactory
     */
    private $triggerFactory;

    public function __construct(
        TriggerFactory $triggerFactory
    ) {
        $this->triggerFactory = $triggerFactory;
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    public function addVisitStatTrigger(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $triggerName = 'update_visit_stat';
        $event = Trigger::EVENT_INSERT;

        /** @var Trigger $trigger */
        $trigger = $this->triggerFactory->create()
            ->setName($triggerName)
            ->setTime(Trigger::TIME_AFTER)
            ->setEvent($event)
            ->setTable($setup->getTable(CreateViewStatTables::TABLE_NAME));

        $trigger->addStatement($this->getVisitStatStatement($setup));

        $connection->dropTrigger($trigger->getName());
        $connection->createTrigger($trigger);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return string
     */
    private function getVisitStatStatement(SchemaSetupInterface $setup)
    {
        $categoryTableTrigger = sprintf(
            "UPDATE %s SET %s = %s + 1 WHERE %s = NEW.%s",
            $setup->getTable(CreateCategoryTable::TABLE_NAME),
            CategoryInterface::VISIT_COUNT,
            CategoryInterface::VISIT_COUNT,
            CategoryInterface::CATEGORY_ID,
            VisitStatInterface::CATEGORY_ID
        );

        $questionTableTrigger = sprintf(
            "UPDATE %s SET %s = %s + 1 WHERE %s = NEW.%s",
            $setup->getTable(CreateQuestionTable::TABLE_NAME),
            QuestionInterface::VISIT_COUNT,
            QuestionInterface::VISIT_COUNT,
            QuestionInterface::QUESTION_ID,
            VisitStatInterface::QUESTION_ID
        );

        return $categoryTableTrigger . ';' . $questionTableTrigger;
    }
}
