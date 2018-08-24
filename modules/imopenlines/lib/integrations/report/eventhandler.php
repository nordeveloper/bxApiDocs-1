<?php
namespace Bitrix\ImOpenLines\Integrations\Report;

use Bitrix\ImOpenLines\Integrations\Report\Handlers\Dialog;
use Bitrix\ImOpenLines\Integrations\Report\Handlers\Treatment;
use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\Category;
use Bitrix\Report\VisualConstructor\Entity\Dashboard;
use Bitrix\Report\VisualConstructor\Entity\DashboardRow;
use Bitrix\Report\VisualConstructor\Entity\Report;
use Bitrix\Report\VisualConstructor\Entity\Widget;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;
use Bitrix\Report\VisualConstructor\Handler\BaseWidget;
use Bitrix\Report\VisualConstructor\Helper\Util;
use Bitrix\Report\VisualConstructor\Views\Component\GroupedDataGrid;
use Bitrix\Report\VisualConstructor\Views\Component\Number;
use Bitrix\Report\VisualConstructor\Views\Component\NumberBlock;
use Bitrix\Report\VisualConstructor\Views\JsComponent\Activity;
use Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart\DonutDiagram;
use Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart\LinearGraph;
use Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart\MultiDirectional;

/**
 * Class EventHandler
 * @package Bitrix\Tasks\Integration\Report
 */
class EventHandler
{
	/**
	 * @return BaseReport[]
	 */
	public static function onReportsCollect()
	{
		return array(
			new Handlers\Treatment(),
			new Handlers\Dialog(),
		);
	}

	/**
	 * @return Category[]
	 */
	public static function onCategoriesCollect()
	{
		$openLinesCategory = new Category();
		$openLinesCategory->setKey('open_lines');
		$openLinesCategory->setLabel(Loc::getMessage('REPORT_OPEN_LINES_CATEGORY_NEW'));
		$openLinesCategory->setParentKey('main');


		$categories[] = $openLinesCategory;
		return $categories;
	}

	public static function onViewsCollect()
	{

	}

	/**
	 * @return string Board unique id.
	 */
	public static function getOpenLinesBoardId()
	{
		return 'open_lines_report_base_board';
	}
	/**
	 * @return Dashboard[]
	 */
	public static function onDefaultBoardsCollect()
	{
		$board = new Dashboard();
		$board->setVersion('v3');
		$board->setBoardKey(self::getOpenLinesBoardId());
		$board->setGId(Util::generateUserUniqueId());
		$board->setUserId(0);

		$firstRow = new DashboardRow();
		$firstRow->setGId(Util::generateUserUniqueId());
		$firstRow->setWeight(1);
		$firstCellId = 'cell_' . randString(4);
		$secondCellId = 'cell_' . randString(4);

		$map = array(
			'type' => 'cell-container',
			'orientation' => 'horizontal',
			'elements' => array(
				array(
					'type' => 'cell',
					'id' => $firstCellId
				),
				array(
					'type' => 'cell',
					'id' => $secondCellId
				)
			)
		);
		$firstRow->setLayoutMap($map);
		$board->addRows($firstRow);

		$loadByChannelWidget= self::buildWidgetLoadByChannels();
		$loadByChannelWidget->setWeight($firstCellId);
		$loadByChannelWidget->setIsPattern(true);
		$firstRow->addWidgets($loadByChannelWidget);

		$numberBlockWithTreatmentCount = self::buildNumberBlockWithTreatmentCount();
		$numberBlockWithTreatmentCount->setWeight($secondCellId);
		$numberBlockWithTreatmentCount->setIsPattern(true);
		$firstRow->addWidgets($numberBlockWithTreatmentCount);


		$secondRow = new DashboardRow();
		$secondRow->setGId(Util::generateUserUniqueId());
		$secondRow->setWeight(2);
		$firstCellId = 'cell_' . randString(4);
		$secondCellId = 'cell_' . randString(4);

		$map = array(
			'type' => 'cell-container',
			'orientation' => 'horizontal',
			'elements' => array(
				array(
					'type' => 'cell',
					'id' => $firstCellId
				),
				array(
					'type' => 'cell',
					'id' => $secondCellId
				)
			)
		);
		$secondRow->setLayoutMap($map);
		$board->addRows($secondRow);

		$numberWithAverageTime= self::buildNumberWithAverageAnswerTime();
		$numberWithAverageTime->setWeight($firstCellId);
		$numberWithAverageTime->setIsPattern(true);
		$secondRow->addWidgets($numberWithAverageTime);

		$numberWithContentment = self::buildNumberContentment();
		$numberWithContentment->setWeight($secondCellId);
		$numberWithContentment->setIsPattern(true);
		$secondRow->addWidgets($numberWithContentment);


		$thirdRow = new DashboardRow();
		$thirdRow->setGId(Util::generateUserUniqueId());
		$thirdRow->setWeight(3);
		$firstCellId = 'cell_' . randString(4);
		$secondCellId = 'cell_' . randString(4);

		$map = array(
			'type' => 'cell-container',
			'orientation' => 'horizontal',
			'elements' => array(
				array(
					'type' => 'cell',
					'id' => $firstCellId
				),
				array(
					'type' => 'cell',
					'id' => $secondCellId
				)
			)
		);
		$thirdRow->setLayoutMap($map);
		$board->addRows($thirdRow);

		$dynamicsOfReaction= self::buildDynamicsOfReaction();
		$dynamicsOfReaction->setWeight($firstCellId);
		$dynamicsOfReaction->setIsPattern(true);
		$thirdRow->addWidgets($dynamicsOfReaction);

		$activity = self::buildActivity();
		$activity->setWeight($secondCellId);
		$activity->setIsPattern(true);
		$thirdRow->addWidgets($activity);


		$fourthRow = new DashboardRow();
		$fourthRow->setGId(Util::generateUserUniqueId());
		$fourthRow->setWeight(4);
		$firstCellId = 'cell_' . randString(4);

		$map = array(
			'type' => 'cell-container',
			'orientation' => 'horizontal',
			'elements' => array(
				array(
					'type' => 'cell',
					'id' => $firstCellId
				),
			)
		);
		$fourthRow->setLayoutMap($map);
		$board->addRows($fourthRow);

		$dynamicsOfVote = self::buildDynamicsOfVote();
		$dynamicsOfVote->setWeight($firstCellId);
		$dynamicsOfVote->setIsPattern(true);
		$fourthRow->addWidgets($dynamicsOfVote);


		$fifthRow = new DashboardRow();
		$fifthRow->setGId(Util::generateUserUniqueId());
		$fifthRow->setWeight(5);
		$firstCellId = 'cell_' . randString(4);

		$map = array(
			'type' => 'cell-container',
			'orientation' => 'horizontal',
			'elements' => array(
				array(
					'type' => 'cell',
					'id' => $firstCellId
				),
			)
		);
		$fifthRow->setLayoutMap($map);
		$board->addRows($fifthRow);

		$staffCutStatistics= self::buildStaffCutStatistics();
		$staffCutStatistics->setWeight($firstCellId);
		$staffCutStatistics->setIsPattern(true);
		$fifthRow->addWidgets($staffCutStatistics);





		$result = array();
		$result[] = $board;
		return $result;
	}


	/**
	 * @return Widget
	 */
	private static function buildWidgetLoadByChannels()
	{
		$widget = new Widget();
		$widget->setGId(Util::generateUserUniqueId());
		$widget->setWidgetClass(BaseWidget::getClassName());
		$widget->setViewKey(DonutDiagram::VIEW_KEY);
		$widget->setCategoryKey('open_lines');
		$widget->setBoardId('open_lines_report_base_board');

		$widget->getWidgetHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_LOAD_BY_CHANNEL_WIDGET_TITLE'));
		$widget->addConfigurations($widget->getWidgetHandler()->getConfigurations());


		$report = new Report();
		$report->setGId(Util::generateUserUniqueId());
		$report->setReportClassName(Treatment::getClassName());
		$report->setWidget($widget);
		$report->getReportHandler()->updateFormElementValue('groupingBy', Treatment::GROUP_BY_CHANEL);
		$report->getReportHandler()->updateFormElementValue('calculate', Treatment::WHAT_WILL_CALCULATE_ALL);
		$report->addConfigurations($report->getReportHandler()->getConfigurations());
		$widget->addReports($report);


		return $widget;
	}


	/**
	 * @return Widget
	 */
	private static function buildNumberBlockWithTreatmentCount()
	{
		$widget = new Widget();
		$widget->setGId(Util::generateUserUniqueId());
		$widget->setWidgetClass(BaseWidget::getClassName());
		$widget->setViewKey(NumberBlock::VIEW_KEY);
		$widget->setCategoryKey('open_lines');
		$widget->setBoardId('open_lines_report_base_board');

		$widget->getWidgetHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_TREATMENT_COUNT_NUMBER_BLOCK_WIDGET_TITLE'));
		$widget->addConfigurations($widget->getWidgetHandler()->getConfigurations());


		$allTreatmentCount = new Report();
		$allTreatmentCount->setGId(Util::generateUserUniqueId());
		$allTreatmentCount->setReportClassName(Treatment::getClassName());
		$allTreatmentCount->setWidget($widget);
		$allTreatmentCount->getReportHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_ALL_TREATMENT_COUNT_NUMBER_BLOCK_TITLE'));
		$allTreatmentCount->getReportHandler()->updateFormElementValue('calculate', Treatment::WHAT_WILL_CALCULATE_ALL);
		$allTreatmentCount->addConfigurations($allTreatmentCount->getReportHandler()->getConfigurations());
		$widget->addReports($allTreatmentCount);

		$firstTreatmentCount = new Report();
		$firstTreatmentCount->setGId(Util::generateUserUniqueId());
		$firstTreatmentCount->setReportClassName(Treatment::getClassName());
		$firstTreatmentCount->setWidget($widget);
		$firstTreatmentCount->getReportHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_FIRST_TREATMENT_COUNT_NUMBER_BLOCK_TITLE'));
		$firstTreatmentCount->getReportHandler()->updateFormElementValue('calculate', Treatment::WHAT_WILL_CALCULATE_FIRST);
		$firstTreatmentCount->addConfigurations($firstTreatmentCount->getReportHandler()->getConfigurations());
		$widget->addReports($firstTreatmentCount);


		$secondTreatmentCount = new Report();
		$secondTreatmentCount->setGId(Util::generateUserUniqueId());
		$secondTreatmentCount->setReportClassName(Treatment::getClassName());
		$secondTreatmentCount->setWidget($widget);
		$secondTreatmentCount->getReportHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_DUPLICATE_TREATMENT_COUNT_NUMBER_BLOCK_TITLE'));
		$secondTreatmentCount->getReportHandler()->updateFormElementValue('calculate', Treatment::WHAT_WILL_CALCULATE_DUPLICATE);
		$secondTreatmentCount->addConfigurations($secondTreatmentCount->getReportHandler()->getConfigurations());
		$widget->addReports($secondTreatmentCount);

		return $widget;
	}


	/**
	 * @return Widget
	 */
	private static function buildNumberWithAverageAnswerTime()
	{
		$widget = new Widget();
		$widget->setGId(Util::generateUserUniqueId());
		$widget->setWidgetClass(BaseWidget::getClassName());
		$widget->setViewKey(Number::VIEW_KEY);
		$widget->setCategoryKey('open_lines');
		$widget->setBoardId('open_lines_report_base_board');

		$widget->getWidgetHandler()->updateFormElementValue('label',Loc::getMessage('REPORT_AVERAGE_NUMBER_BLOCK_WIDGET_TITLE'));
		$widget->addConfigurations($widget->getWidgetHandler()->getConfigurations());

		$averageTimeToAnswer = new Report();
		$averageTimeToAnswer->setGId(Util::generateUserUniqueId());
		$averageTimeToAnswer->setReportClassName(Dialog::getClassName());
		$averageTimeToAnswer->setWidget($widget);
		$averageTimeToAnswer->getReportHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_AVERAGE_TIME_TO_ANSWER_TITLE'));
		$averageTimeToAnswer->getReportHandler()->updateFormElementValue('color', '#4fc3f7');
		$averageTimeToAnswer->getReportHandler()->updateFormElementValue('calculate', Dialog::WHAT_WILL_CALCULATE_AVERAGE_TIME_TO_ANSWER);
		$averageTimeToAnswer->addConfigurations($averageTimeToAnswer->getReportHandler()->getConfigurations());
		$widget->addReports($averageTimeToAnswer);

		return $widget;
	}


	/**
	 * @return Widget
	 */
	private static function buildNumberContentment()
	{
		$widget = new Widget();
		$widget->setGId(Util::generateUserUniqueId());
		$widget->setWidgetClass(BaseWidget::getClassName());
		$widget->setViewKey(Number::VIEW_KEY);
		$widget->setCategoryKey('open_lines');
		$widget->setBoardId('open_lines_report_base_board');

		$widget->getWidgetHandler()->updateFormElementValue('label' , Loc::getMessage('REPORT_CONTENTMENT_NUMBER_BLOCK_WIDGET_TITLE'));
		$widget->addConfigurations($widget->getWidgetHandler()->getConfigurations());

		$contentment = new Report();
		$contentment->setGId(Util::generateUserUniqueId());
		$contentment->setReportClassName(Treatment::getClassName());
		$contentment->setWidget($widget);
		$contentment->getReportHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_CONTENTMENT_TITLE'));
		$contentment->getReportHandler()->updateFormElementValue('color', '#eec200');
		$contentment->getReportHandler()->updateFormElementValue('calculate', Treatment::WHAT_WILL_CALCULATE_CONTENTMENT);
		$contentment->addConfigurations($contentment->getReportHandler()->getConfigurations());
		$widget->addReports($contentment);

		return $widget;
	}


	/**
	 * @return Widget
	 */
	private static function buildDynamicsOfReaction()
	{
		$widget = new Widget();
		$widget->setGId(Util::generateUserUniqueId());
		$widget->setWidgetClass(BaseWidget::getClassName());
		$widget->setViewKey(MultiDirectional::VIEW_KEY);
		$widget->setCategoryKey('open_lines');
		$widget->setBoardId('open_lines_report_base_board');

		$widget->getWidgetHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_DYNAMICS_OF_REACTION_WIDGET_TITLE'));
		$widget->addConfigurations($widget->getWidgetHandler()->getConfigurations());

		$allTreatmentCount = new Report();
		$allTreatmentCount->setGId(Util::generateUserUniqueId());
		$allTreatmentCount->setReportClassName(Treatment::getClassName());
		$allTreatmentCount->setWidget($widget);
		$allTreatmentCount->getReportHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_ALL_TREATMENT_COUNT_TITLE'));
		$allTreatmentCount->getReportHandler()->updateFormElementValue('color', '#ff8792');
		$allTreatmentCount->getReportHandler()->updateFormElementValue('groupingBy', Treatment::GROUP_BY_DATE);
		$allTreatmentCount->getReportHandler()->updateFormElementValue('calculate', Treatment::WHAT_WILL_CALCULATE_ALL);
		$allTreatmentCount->addConfigurations($allTreatmentCount->getReportHandler()->getConfigurations());
		$widget->addReports($allTreatmentCount);

		$averageAnswerTime = new Report();
		$averageAnswerTime->setGId(Util::generateUserUniqueId());
		$averageAnswerTime->setReportClassName(Dialog::getClassName());
		$averageAnswerTime->setWidget($widget);
		$averageAnswerTime->getReportHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_AVERAGE_TIME_TO_ANSWER_TITLE'));
		$averageAnswerTime->getReportHandler()->updateFormElementValue('color', '#ff9752');
		$averageAnswerTime->getReportHandler()->updateFormElementValue('groupingBy', Dialog::GROUP_BY_DATE);
		$averageAnswerTime->getReportHandler()->updateFormElementValue('calculate', Dialog::WHAT_WILL_CALCULATE_AVERAGE_TIME_TO_ANSWER);
		$averageAnswerTime->addConfigurations($averageAnswerTime->getReportHandler()->getConfigurations());
		$widget->addReports($averageAnswerTime);

		return $widget;
	}

	/**
	 * @return Widget
	 */
	private static function buildActivity()
	{
		$widget = new Widget();
		$widget->setGId(Util::generateUserUniqueId());
		$widget->setWidgetClass(BaseWidget::getClassName());
		$widget->setViewKey(Activity::VIEW_KEY);
		$widget->setCategoryKey('open_lines');
		$widget->setBoardId('open_lines_report_base_board');

		$widget->getWidgetHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_ACTIVITY_WIDGET_TITLE'));
		$widget->addConfigurations($widget->getWidgetHandler()->getConfigurations());

		$allTreatmentCount = new Report();
		$allTreatmentCount->setGId(Util::generateUserUniqueId());
		$allTreatmentCount->setReportClassName(Treatment::getClassName());
		$allTreatmentCount->setWidget($widget);
		$allTreatmentCount->getReportHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_ALL_TREATMENT_COUNT_TITLE'));
		$allTreatmentCount->getReportHandler()->updateFormElementValue('groupingBy', Treatment::GROUP_BY_DATE);
		$allTreatmentCount->getReportHandler()->updateFormElementValue('calculate', Treatment::WHAT_WILL_CALCULATE_ALL_TREATMENT_BY_HOUR);
		$allTreatmentCount->addConfigurations($allTreatmentCount->getReportHandler()->getConfigurations());
		$widget->addReports($allTreatmentCount);

		return $widget;
	}

	/**
	 * @return Widget
	 */
	private static function buildDynamicsOfVote()
	{
		$widget = new Widget();
		$widget->setGId(Util::generateUserUniqueId());
		$widget->setWidgetClass(BaseWidget::getClassName());
		$widget->setViewKey(LinearGraph::VIEW_KEY);
		$widget->setCategoryKey('open_lines');
		$widget->setBoardId('open_lines_report_base_board');

		$widget->getWidgetHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_DYNAMICS_OF_VOTE_WIDGET_TITLE'));
		$widget->addConfigurations($widget->getWidgetHandler()->getConfigurations());

		$positiveMarkedTreatments = new Report();
		$positiveMarkedTreatments->setGId(Util::generateUserUniqueId());
		$positiveMarkedTreatments->setReportClassName(Treatment::getClassName());
		$positiveMarkedTreatments->setWidget($widget);
		$positiveMarkedTreatments->getReportHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_POSITIVE_MARKED_TREATMENT_COUNT_TITLE'));
		$positiveMarkedTreatments->getReportHandler()->updateFormElementValue('color', '#ff8792');
		$positiveMarkedTreatments->getReportHandler()->updateFormElementValue('groupingBy', Treatment::GROUP_BY_DATE);
		$positiveMarkedTreatments->getReportHandler()->updateFormElementValue('calculate', Treatment::WHAT_WILL_CALCULATE_POSITIVE_MARK);
		$positiveMarkedTreatments->addConfigurations($positiveMarkedTreatments->getReportHandler()->getConfigurations());
		$widget->addReports($positiveMarkedTreatments);

		$negativeMarkedTreatments = new Report();
		$negativeMarkedTreatments->setGId(Util::generateUserUniqueId());
		$negativeMarkedTreatments->setReportClassName(Treatment::getClassName());
		$negativeMarkedTreatments->setWidget($widget);
		$negativeMarkedTreatments->getReportHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_NEGATIVE_MARKED_ACTIVITY_WIDGET_TITLE'));
		$negativeMarkedTreatments->getReportHandler()->updateFormElementValue('color', '#ff9752');
		$negativeMarkedTreatments->getReportHandler()->updateFormElementValue('groupingBy', Treatment::GROUP_BY_DATE);
		$negativeMarkedTreatments->getReportHandler()->updateFormElementValue('calculate', Treatment::WHAT_WILL_CALCULATE_NEGATIVE_MARK);
		$negativeMarkedTreatments->addConfigurations($negativeMarkedTreatments->getReportHandler()->getConfigurations());
		$widget->addReports($negativeMarkedTreatments);

		return $widget;
	}

	/**
	 * @return Widget
	 */
	private static function buildStaffCutStatistics()
	{
		$widget = new Widget();
		$widget->setGId(Util::generateUserUniqueId());
		$widget->setWidgetClass(BaseWidget::getClassName());
		$widget->setViewKey(GroupedDataGrid::VIEW_KEY);
		$widget->setCategoryKey('open_lines');
		$widget->setBoardId('open_lines_report_base_board');

		$widget->getWidgetHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_STAFF_CUT_WIDGET_TITLE'));
		$widget->addConfigurations($widget->getWidgetHandler()->getConfigurations());


		$contentment = new Report();
		$contentment->setGId(Util::generateUserUniqueId());
		$contentment->setReportClassName(Treatment::getClassName());
		$contentment->setWidget($widget);
		$contentment->getReportHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_CONTENTMENT_TITLE'));
		$contentment->getReportHandler()->updateFormElementValue('groupingBy', Treatment::GROUP_BY_RESPONSIBLE);
		$contentment->getReportHandler()->updateFormElementValue('calculate', Treatment::WHAT_WILL_CALCULATE_CONTENTMENT);
		$contentment->addConfigurations($contentment->getReportHandler()->getConfigurations());

		$widget->addReports($contentment);

		$allTreatmentCount = new Report();
		$allTreatmentCount->setGId(Util::generateUserUniqueId());
		$allTreatmentCount->setReportClassName(Treatment::getClassName());
		$allTreatmentCount->setWidget($widget);
		$allTreatmentCount->getReportHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_ALL_TREATMENT_COUNT_TITLE'));
		$allTreatmentCount->getReportHandler()->updateFormElementValue('groupingBy', Treatment::GROUP_BY_RESPONSIBLE);
		$allTreatmentCount->getReportHandler()->updateFormElementValue('calculate', Treatment::WHAT_WILL_CALCULATE_ALL);
		$allTreatmentCount->addConfigurations($allTreatmentCount->getReportHandler()->getConfigurations());
		$widget->addReports($allTreatmentCount);


		$firstTreatmentCount = new Report();
		$firstTreatmentCount->setGId(Util::generateUserUniqueId());
		$firstTreatmentCount->setReportClassName(Treatment::getClassName());
		$firstTreatmentCount->setWidget($widget);
		$firstTreatmentCount->getReportHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_FIRST_TREATMENT_COUNT_NUMBER_BLOCK_TITLE'));
		$firstTreatmentCount->getReportHandler()->updateFormElementValue('groupingBy', Treatment::GROUP_BY_RESPONSIBLE);

		$firstTreatmentCount->getReportHandler()->updateFormElementValue('calculate', Treatment::WHAT_WILL_CALCULATE_FIRST);
		$firstTreatmentCount->addConfigurations($firstTreatmentCount->getReportHandler()->getConfigurations());
		$widget->addReports($firstTreatmentCount);


		$secondTreatmentCount = new Report();
		$secondTreatmentCount->setGId(Util::generateUserUniqueId());
		$secondTreatmentCount->setReportClassName(Treatment::getClassName());
		$secondTreatmentCount->setWidget($widget);
		$secondTreatmentCount->getReportHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_DUPLICATE_TREATMENT_COUNT_NUMBER_BLOCK_TITLE'));
		$secondTreatmentCount->getReportHandler()->updateFormElementValue('groupingBy', Treatment::GROUP_BY_RESPONSIBLE);

		$secondTreatmentCount->getReportHandler()->updateFormElementValue('calculate', Treatment::WHAT_WILL_CALCULATE_DUPLICATE);
		$secondTreatmentCount->addConfigurations($secondTreatmentCount->getReportHandler()->getConfigurations());
		$widget->addReports($secondTreatmentCount);

		$appointedTreatmentCount = new Report();
		$appointedTreatmentCount->setGId(Util::generateUserUniqueId());
		$appointedTreatmentCount->setReportClassName(Treatment::getClassName());
		$appointedTreatmentCount->setWidget($widget);
		$appointedTreatmentCount->getReportHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_APPOINTED_TREATMENT_COUNT_NUMBER_BLOCK_TITLE'));
		$appointedTreatmentCount->getReportHandler()->updateFormElementValue('groupingBy', Treatment::GROUP_BY_RESPONSIBLE);
		$appointedTreatmentCount->getReportHandler()->updateFormElementValue('calculate', Treatment::WHAT_WILL_CALCULATE_ALL_APPOINTED);
		$appointedTreatmentCount->addConfigurations($appointedTreatmentCount->getReportHandler()->getConfigurations());
		$widget->addReports($appointedTreatmentCount);


		$answeredTreatmentCount = new Report();
		$answeredTreatmentCount->setGId(Util::generateUserUniqueId());
		$answeredTreatmentCount->setReportClassName(Treatment::getClassName());
		$answeredTreatmentCount->setWidget($widget);
		$answeredTreatmentCount->getReportHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_ANSWERED_TREATMENT_COUNT_NUMBER_BLOCK_TITLE'));
		$answeredTreatmentCount->getReportHandler()->updateFormElementValue('groupingBy', Treatment::GROUP_BY_RESPONSIBLE);

		$answeredTreatmentCount->getReportHandler()->updateFormElementValue('calculate', Treatment::WHAT_WILL_CALCULATE_ANSWERED);
		$answeredTreatmentCount->addConfigurations($answeredTreatmentCount->getReportHandler()->getConfigurations());
		$widget->addReports($answeredTreatmentCount);

		$skippedTreatmentCount = new Report();
		$skippedTreatmentCount->setGId(Util::generateUserUniqueId());
		$skippedTreatmentCount->setReportClassName(Treatment::getClassName());
		$skippedTreatmentCount->setWidget($widget);
		$skippedTreatmentCount->getReportHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_SKIPPED_TREATMENT_COUNT_NUMBER_BLOCK_TITLE'));
		$skippedTreatmentCount->getReportHandler()->updateFormElementValue('groupingBy', Treatment::GROUP_BY_RESPONSIBLE);
		$skippedTreatmentCount->getReportHandler()->updateFormElementValue('calculate', Treatment::WHAT_WILL_CALCULATE_SKIPPED);
		$skippedTreatmentCount->addConfigurations($skippedTreatmentCount->getReportHandler()->getConfigurations());
		$widget->addReports($skippedTreatmentCount);


		$averageAnswerTime = new Report();
		$averageAnswerTime->setGId(Util::generateUserUniqueId());
		$averageAnswerTime->setReportClassName(Dialog::getClassName());
		$averageAnswerTime->setWidget($widget);
		$averageAnswerTime->getReportHandler()->updateFormElementValue('label', Loc::getMessage('REPORT_AVERAGE_TIME_TO_ANSWER_TITLE'));
		$averageAnswerTime->getReportHandler()->updateFormElementValue('groupingBy', Dialog::GROUP_BY_RESPONSIBLE);
		$averageAnswerTime->getReportHandler()->updateFormElementValue('calculate', Dialog::WHAT_WILL_CALCULATE_AVERAGE_TIME_TO_ANSWER);
		$averageAnswerTime->addConfigurations($averageAnswerTime->getReportHandler()->getConfigurations());
		$widget->addReports($averageAnswerTime);





		return $widget;
	}
}