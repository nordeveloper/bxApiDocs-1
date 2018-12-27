<?php
namespace Bitrix\Report\VisualConstructor;

use Bitrix\Report\VisualConstructor\Helper\Filter;


/**
 * Class AnalyticBoard
 * @package Bitrix\Report\VisualConstructor
 */
class AnalyticBoard
{
	private $title;
	private $boardKey;
	private $machineKey;
	private $filter;
	private $batchKey = null;
	private $buttons = [];
	private $disabled = false;


	public function __construct($boardId = '')
	{
		if ($boardId)
		{
			$this->setBoardKey($boardId);
			$boardControls = new BoardComponentButton('bitrix:report.visualconstructor.board.controls', '', [
				'BOARD_ID' => $this->getBoardKey(),
				'DEMO_TOGGLE' => false
			]);
			$this->addButton($boardControls);
			$feedbackButton = new BoardComponentButton('bitrix:report.analytics.feedback', '', [
				'BOARD_KEY' => $this->getBoardKey(),
			]);
			$this->addButton($feedbackButton);
		}



	}

	/**
	 * @return mixed
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param mixed $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @return mixed
	 */
	public function getBoardKey()
	{
		return $this->boardKey;
	}

	/**
	 * @param mixed $boardKey
	 */
	public function setBoardKey($boardKey)
	{
		$this->boardKey = $boardKey;
	}

	/**
	 * @return mixed
	 */
	public function getMachineKey()
	{
		return $this->machineKey ?: $this->boardKey;
	}

	/**
	 * @param mixed $machineKey
	 */
	public function setMachineKey($machineKey)
	{
		$this->machineKey = $machineKey;
	}

	/**
	 * @return Filter
	 */
	public function getFilter()
	{
		return $this->filter;
	}

	/**
	 * @param Filter $filter
	 */
	public function setFilter(Filter $filter)
	{
		$this->filter = $filter;
	}

	/**
	 * @return string
	 */
	public function getBatchKey()
	{
		return $this->batchKey;
	}

	/**
	 * @param string $batchKey
	 */
	public function setBatchKey($batchKey)
	{
		$this->batchKey = $batchKey;
	}

	/**
	 * @return bool
	 */
	public function isNestedInBatch()
	{
		return $this->batchKey !== null;
	}

	/**
	 * @param BoardButton $button
	 */
	public function addButton(BoardButton $button)
	{
		$this->buttons[] = $button;
	}

	public function getButtonsContent()
	{
		$result = [
			'html' => '',
			'assets' => [
				'js' => [],
				'css' => [],
				'string' => [],
			]
		];
		$buttons = $this->getButtons();
		foreach ($buttons as $button)
		{
			$result['html'] .= $button->process()->getHtml();
			foreach ($button->getJsList() as $jsPath)
			{
				$result['assets']['js'][] = $jsPath;
			}

			foreach ($button->getCssList() as $cssPath)
			{
				$result['assets']['css'][] = $cssPath;
			}

			foreach ($button->getStringList() as $string)
			{
				$result['assets']['string'][] = $string;
			}
		}

		return $result;
	}

	/**
	 * @return BoardButton[]
	 */
	public function getButtons()
	{
		return $this->buttons;
	}

	/**
	 * @return bool
	 */
	public function isDisabled()
	{
		return $this->disabled;
	}

	/**
	 * @param bool $disabled
	 */
	public function setDisabled($disabled)
	{
		$this->disabled = $disabled;
	}
}