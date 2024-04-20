<?php

class TaskDetailFooterComponent extends CBitrixComponent
{
	public function executeComponent()
	{
		$this->fetchActivity();
		$this->includeComponentTemplate();
	}

	public function onPrepareComponentParams($arParams)
	{
		global $USER;
		$arParams['USER_ID'] = (int)$USER->getId();

		if (!$arParams['USER_ACTIVITY'] || !in_array($arParams['USER_ACTIVITY'], [
			'.default',
			'contractor_this_task',
			'contractor_from_project',
			'owner',
			'reject',
			'wait'
			], true))
		{
			$arParams['USER_ACTIVITY'] = '.default' ;
		}

		$arParams['TASK_STATUSES'] = \Up\Ukan\Service\Configuration::getOption('task_status');

		return $arParams;
	}

	public function fetchActivity()
	{
		switch ($this->arParams['USER_ACTIVITY'])
		{
			case 'owner':
				$this->fetchOwnerActivity();
				break;
			case 'contractor_this_task':
				$this->fetchContractorActivity();
				break;
			case 'contractor_from_project':
				$this->fetchContractor();
				break;
			case 'reject':
			case 'wait':
			default:
				break;
		}

	}

	private function fetchOwnerActivity()
	{
		if ($this->arParams['TASK']->getStatus() === $this->arParams['TASK_STATUSES']['new'] )
		{
			$this->fetchResponses();
		}
		elseif ($this->arParams['TASK']->getStatus() === $this->arParams['TASK_STATUSES']['at_work'])
		{
			$this->fetchContractor();
		}
		elseif ($this->arParams['TASK']->getStatus() === $this->arParams['TASK_STATUSES']['done'])
		{
			$this->setUserSentFeedback();
			$this->fillFeedbacks();
		}
	}

	private function fetchContractorActivity()
	{
		if ($this->arParams['TASK']->getStatus() !== $this->arParams['TASK_STATUSES']['done'] )
		{
			$this->fetchClient();
		}
		else
		{
			$this->setUserSentFeedback();
			$this->fillFeedbacks();
		}
	}
	private function fetchResponses()
	{
		$query = \Up\Ukan\Model\ResponseTable::query();
		$query->setSelect(['*', 'TASK', 'TASK.PROJECT' , 'CONTRACTOR.B_USER'])
			  ->where('TASK_ID', $this->arParams['TASK']->getId())
			  ->where('STATUS', \Up\Ukan\Service\Configuration::getOption('response_status')['wait'])
			  ->addOrder('CREATED_AT', 'DESC')
			  ->setLimit(5);

		$this->arResult['RESPONSES'] = $query->fetchCollection();
	}

	private function fetchClient()
	{
		if ($this->arParams['TASK'] && $this->arParams['TASK']->fillClient()->fillBUser())
		{
			$this->arResult['CLIENT'] = $this->arParams['TASK']->getClient();
		}
	}

	private function setUserSentFeedback()
	{
		if ($this->arParams['TASK'])
		{
			$feedback = \Up\Ukan\Model\FeedbackTable::query()
													->setSelect(['*'])
													->where('TASK_ID', $this->arParams['TASK']->getId())
													->where('FROM_USER_ID', $this->arParams['USER_ID'])
													->fetchObject();

			$this->arResult['USER_SENT_FEEDBACK'] = isset($feedback);

		}
	}
	private function fillFeedbacks()
	{
		if ($this->arParams['TASK'])
		{
			$this->arParams['TASK']->fillFeedbacks();
			$this->arParams['TASK']->getFeedbacks()->fillFromUser()->fillBUser();
			$this->arParams['TASK']->getFeedbacks()->fillToUser()->fillBUser();
		}
	}

	private function fetchContractor()
	{
		if ($this->arParams['TASK'] && $this->arParams['TASK']->fillContractor()->fillBUser())
		{
			$this->arResult['CONTRACTOR'] = $this->arParams['TASK']->getContractor();
		}
	}

}