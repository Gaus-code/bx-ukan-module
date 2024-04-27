<?php

namespace Up\Ukan\Model;

use Bitrix\Main\Localization\Loc, Bitrix\Main\ORM\Data\DataManager, Bitrix\Main\ORM\Fields\DatetimeField, Bitrix\Main\ORM\Fields\IntegerField, Bitrix\Main\ORM\Fields\StringField, Bitrix\Main\ORM\Fields\TextField, Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Up\Ukan\Service\Configuration;

Loc::loadMessages(__FILE__);

/**
 * Class TaskTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TITLE string(255) mandatory
 * <li> DESCRIPTION text mandatory
 * <li> MAX_PRICE int optional
 * <li> PRIORITY int mandatory
 * <li> CLIENT_ID int mandatory
 * <li> CONTRACTOR_ID int optional
 * <li> STATUS_ID int mandatory
 * <li> PROJECT_ID int optional
 * <li> CREATED_AT datetime mandatory
 * <li> UPDATED_AT datetime mandatory
 * </ul>
 *
 * @package Bitrix\Ukan
 **/
class TaskTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'up_ukan_task';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new IntegerField(
				'ID', [
						'primary' => true,
						'autocomplete' => true,
						'title' => Loc::getMessage('TASK_ENTITY_ID_FIELD'),
					]
			),
			new StringField(
				'TITLE', [
						   'required' => true,
						   'validation' => [__CLASS__, 'validateTitle'],
						   'title' => Loc::getMessage('TASK_ENTITY_TITLE_FIELD'),
					   ]
			),
			new TextField(
				'DESCRIPTION', [
								 'required' => true,
								 'title' => Loc::getMessage('TASK_ENTITY_DESCRIPTION_FIELD'),
							 ]
			),
			new IntegerField(
				'MAX_PRICE', [
							   'title' => Loc::getMessage('TASK_ENTITY_MAX_PRICE_FIELD'),
						   ]
			),
			new IntegerField(
				'CLIENT_ID', [
							   'required' => true,
							   'title' => Loc::getMessage('TASK_ENTITY_CLIENT_ID_FIELD'),
						   ]
			),
			new Reference(
				'CLIENT', UserTable::class, Join::on('this.CLIENT_ID', 'ref.ID')
			),
			new IntegerField(
				'CONTRACTOR_ID', [
								   'title' => Loc::getMessage('TASK_ENTITY_CONTRACTOR_ID_FIELD'),
							   ]
			),
			new Reference(
				'CONTRACTOR', UserTable::class, Join::on('this.CONTRACTOR_ID', 'ref.ID')
			),
			new StringField(
				'STATUS', [
							'required' => true,
							'validation' => [__CLASS__, 'validateStatus'],
							'title' => Loc::getMessage('TASK_ENTITY_STATUS_FIELD'),
							'default_value' => Configuration::getOption('task_status')['search_contractor'],
						]
			),
			new IntegerField(
				'PROJECT_STAGE_ID',
				[
					'title' => Loc::getMessage('TASK_ENTITY_PROJECT_STAGE_ID_FIELD')
				]
			),
			new Reference(
				'PROJECT_STAGE',
				ProjectStageTable::class,
				Join::on('this.PROJECT_STAGE_ID', 'ref.ID')
			),
			new DatetimeField(
				'CREATED_AT', [
								'required' => true,
								'title' => Loc::getMessage('TASK_ENTITY_CREATED_AT_FIELD'),
								'default_value' => function() {
									return new DateTime();
								},
							]
			),
			new DatetimeField(
				'UPDATED_AT', [
								'required' => true,
								'title' => Loc::getMessage('TASK_ENTITY_UPDATED_AT_FIELD'),
								'default_value' => function() {
									return new DateTime();
								},
							]
			),
			new IntegerField(
				'CATEGORY_ID', [
								 'title' => Loc::getMessage('TASK_ENTITY_CATEGORY_ID_FIELD'),
							 ]
			),
			new Reference(
				'CATEGORY', CategoriesTable::class, Join::on('this.CATEGORY_ID', 'ref.ID')
			),
			new ExpressionField(
				'SEARCH_PRIORITY', "IF (%s='Active', 1, 0)", ['CLIENT.SUBSCRIPTION_STATUS']
			),
			new OneToMany(
				'RESPONSES', ResponseTable::class, 'TASK'
			),
			new OneToMany(
				'FEEDBACKS', FeedbackTable::class, 'TASK'
			),
			new OneToMany(
				'RESPONSES', ResponseTable::class, 'TASK'
			),
			new DateField(
				'DEADLINE', [
							  'title' => Loc::getMessage('TASK_ENTITY_DEADLINE_FIELD'),
						  ]
			),
			(new ManyToMany(
				'TAGS', TagTable::class
			))->configureTableName('up_ukan_tag_task'),
			new Reference(
				'PROJECT', ProjectTable::class, Join::on('this.PROJECT_STAGE.PROJECT_ID', 'ref.ID')
			),
		];
	}

	/**
	 * Returns validators for TITLE field.
	 *
	 * @return array
	 */
	public static function validateTitle()
	{
		return [
			new LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for STATUS field.
	 *
	 * @return array
	 */
	public static function validateStatus()
	{
		return [
			new LengthValidator(null, 255),
		];
	}

	// public static function onAfterAdd(Event $event)
	// {
	// 	$taskId = $event->getParameter("id");
	// 	$data = $event->getParameter("fields");
	//
	// 	if (isset($data['PROJECT_STAGE_ID']))
	// 	{
	// 		$task = TaskTable::getById($taskId)->fetchObject();
	//
	// 		$task->fillProjectStage();
	// 		$projectStage = $task->getProjectStage();
	//
	// 		$projectStageStatuses = Configuration::getOption('project_stage_status');
	// 		if ($projectStage->getStatus() === $projectStageStatuses['queue'] || $projectStage->getStatus() === $projectStageStatuses['waiting_to_start'])
	// 		{
	// 			$task->setStatus(Configuration::getOption('task_status')['queue']);
	// 		}
	// 		if ($task->getDeadline() > $projectStage->getExpectedCompletionDate())
	// 		{
	// 			$projectStage->setExpectedCompletionDate($task->getDeadline());
	// 			$projectStage->save();
	// 		}
	// 	}
	// }

	// public static function onDelete(Event $event)
	// {
	// 	$taskId = $event->getParameter("id");
	// 	$task = TaskTable::getById($taskId)->fetchObject();
	//
	// 	if ($task->getProjectStageId())
	// 	{
	// 		$task->fillProjectStage();
	// 		$projectStage = $task->getProjectStage();
	//
	// 		if ($task->getDeadline() == $projectStage->getExpectedCompletionDate())
	// 		{
	// 			$projectStage->fillTasks();
	// 			$deadlineList = $projectStage->getTasks()->getDeadlineList();
	//
	// 			unset($deadlineList[array_search($task->getDeadline(),$deadlineList)]);
	// 			if ($deadlineList)
	// 			{
	// 				$expectedCompletionDate = max($deadlineList);
	// 			}
	// 			else
	// 			{
	// 				$expectedCompletionDate = null;
	// 			}
	//
	// 			$projectStage->setExpectedCompletionDate($expectedCompletionDate);
	// 			$projectStage->save();
	// 		}
	// 	}
	// }

	public static function onBeforeUpdate(Event $event)
	{
		$taskId = $event->getParameter("id");
		$data = $event->getParameter("fields");
		$result = new \Bitrix\Main\Entity\EventResult();

		if ($data['PROJECT_STAGE'])
		{
			$projectStage = $data['PROJECT_STAGE'];

			if ($projectStage->getId())
			{
				$projectStage = $data['PROJECT_STAGE'];
				$projectStageStatuses = Configuration::getOption('project_stage_status');
				$taskStatuses = Configuration::getOption('task_status');
				if ($projectStage->getStatus() === $projectStageStatuses['queue'] || $projectStage->getStatus() === $projectStageStatuses['waiting_to_start'])
				{
					$data['STATUS']=$taskStatuses['queue'];
				}
				elseif ($projectStage->getStatus() === $projectStageStatuses['independent'])
				{
					$data['STATUS']=$taskStatuses['waiting_to_start'];
				}
				$result->modifyFields($data);
			}
		}
		return $result;
	}

	// public static function onUpdate(Event $event)
	// {
	// 	$taskId = $event->getParameter("id");
	// 	$data = $event->getParameter("fields");
	// 	if (empty($data['PROJECT_STAGE_ID']) || empty($data['DEADLINE']))
	// 	{
	// 		$taskAfterUpdate = TaskTable::getById($taskId)->fetchObject();
	// 		// var_dump([isset($data['PROJECT_STAGE_ID']), $taskAfterUpdate->getProjectStageId()]); die;
	// 		if (empty($data['PROJECT_STAGE_ID']) && $taskAfterUpdate->getProjectStageId())
	// 		{
	// 			// echo 'пиздец'; die;
	// 			$taskAfterUpdate->fillProjectStage();
	// 			$oldProjectStage = $taskAfterUpdate->getProjectStage();
	//
	// 			if ($taskAfterUpdate->getDeadline() == $oldProjectStage->getExpectedCompletionDate())
	// 			{
	// 				$oldProjectStage->fillTasks();
	// 				$deadlineList = $oldProjectStage->getTasks()->getDeadlineList();
	// 				unset($deadlineList[array_search($taskAfterUpdate->getDeadline(), $deadlineList)]);
	// 				if ($deadlineList)
	// 				{
	// 					$expectedCompletionDate = max($deadlineList);
	// 				}
	// 				else
	// 				{
	// 					$expectedCompletionDate = null;
	// 				}
	//
	// 				$oldProjectStage->setExpectedCompletionDate($expectedCompletionDate);
	// 				$oldProjectStage->save();
	// 			}
	// 		}
	// 	}
	// 	if (isset($data['PROJECT_STAGE_ID']))
	// 	{
	//
	// 		$newProjectStage = ProjectStageTable::getById($data['PROJECT_STAGE_ID'])->fetchObject();
	//
	// 		if ($data['DEADLINE'])
	// 		{
	// 			$deadline = $data['DEADLINE'];
	// 		}
	// 		else
	// 		{
	// 			$deadline = $taskAfterUpdate->getDeadline();
	// 		}
	//
	// 		if ($deadline > $newProjectStage->getExpectedCompletionDate())
	// 		{
	// 			$newProjectStage->setExpectedCompletionDate($deadline);
	// 			$newProjectStage->save();
	// 		}
	// 	}
	// }
}