<?php

namespace Bitrix\Crm\Integration\Report;

use Bitrix\Crm\Integration\Report\Dashboard\CommonLeadAnalyticBoard;
use Bitrix\Crm\Integration\Report\Dashboard\LeadAnalytic\CommonLead;
use Bitrix\Crm\Integration\Report\Dashboard\LeadAnalytic\NewLead;
use Bitrix\Crm\Integration\Report\Dashboard\LeadAnalytic\RepeatLead;
use Bitrix\Crm\Integration\Report\Dashboard\LeadAnalyticBoard;
use Bitrix\Crm\Integration\Report\Dashboard\Sales\SalesPeriodCompare;
use Bitrix\Crm\Integration\Report\Dashboard\Sales\SalesDynamic;
use Bitrix\Crm\Integration\Report\Dashboard\Sales\SalesFunnelBoard;
use Bitrix\Crm\Integration\Report\Dashboard\Sales\SalesPlanBoard;
use Bitrix\Crm\Integration\Report\Filter\Base;
use Bitrix\Crm\Integration\Report\Filter\ClientBaseFilter;
use Bitrix\Crm\Integration\Report\Filter\Deal\SalesDynamicFilter;
use Bitrix\Crm\Integration\Report\Filter\Lead\CommonLead as CommonLeadFilter;
use Bitrix\Crm\Integration\Report\Filter\Lead\NewLead as NewLeadFilter;
use Bitrix\Crm\Integration\Report\Filter\Lead\RepeatLead as RepeatLeadBoard;
use Bitrix\Crm\Integration\Report\Filter\LeadAnalyticsFilter;
use Bitrix\Crm\Integration\Report\Filter\ManagerEfficiencyFilter;
use Bitrix\Crm\Integration\Report\Filter\SalesFunnelFilter;
use Bitrix\Crm\Integration\Report\Handler\Client;
use Bitrix\Crm\Integration\Report\Handler\Company;
use Bitrix\Crm\Integration\Report\Handler\Contact;
use Bitrix\Crm\Integration\Report\Handler\Deal;
use Bitrix\Crm\Integration\Report\Handler\Lead;
use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\AnalyticBoard;
use Bitrix\Report\VisualConstructor\AnalyticBoardBatch;
use Bitrix\Report\VisualConstructor\BoardButton;
use Bitrix\Report\VisualConstructor\Category;
use Bitrix\Report\VisualConstructor\Entity\Dashboard;
use Bitrix\Report\VisualConstructor\Entity\DashboardRow;
use Bitrix\Report\VisualConstructor\Entity\Report;
use Bitrix\Report\VisualConstructor\Entity\Widget;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;
use Bitrix\Report\VisualConstructor\Handler\BaseWidget;
use Bitrix\Report\VisualConstructor\Helper\Util;
use Bitrix\Report\VisualConstructor\Views\Component\Number;
use Bitrix\Report\VisualConstructor\Views\JsComponent\AmChart\LinearGraph;

/**
 * Class EventHandler
 * @package Bitrix\Crm\Integration\Report
 */
class EventHandler
{
	const TRAFFIC_EFFECT_BOARD_KEY = 'traffic_effect_analytics';
	const ADVERTISE_SUM_EFFECT_BOARD_KEY = 'advertise_sum_effect_analytics';
	const MANAGER_EFFICIENCY_BOARD_KEY = 'crm_manager_efficiency';
	const CLIENT_BASE_BOARD_KEY = 'crm_client_base';
	const DISTRIBUTION_OF_REPEAT_SALES = 'crm_distribution_of_repeat_sales';

	/**
	 * @return AnalyticBoardBatch[]
	 */
	public static function onAnalyticPageBatchCollect()
	{
		$batchList = [];

		$lead = new AnalyticBoardBatch();
		$lead->setKey('lead_analytic');
		$lead->setTitle(Loc::getMessage('CRM_REPORT_LEAD_ANALYTIC_BATCH_TITLE'));
		$batchList[] = $lead;

		$sales = new AnalyticBoardBatch();
		$sales->setKey('sales_analytic');
		$sales->setTitle(Loc::getMessage('CRM_REPORT_SALES_BATCH_TITLE'));
		$batchList[] = $sales;

		$managerEfficiency = new AnalyticBoardBatch();
		$managerEfficiency->setKey('manager_efficiency');
		$managerEfficiency->setTitle(Loc::getMessage('CRM_REPORT_MANAGER_EFFICIENCY_BATCH_TITLE'));
		$batchList[] = $managerEfficiency;

		$clients = new AnalyticBoardBatch();
		$clients->setKey('clients');
		$clients->setTitle(Loc::getMessage('CRM_REPORT_CLIENTS_BATCH_TITLE'));
		$batchList[] = $clients;

		$crossCutting = new AnalyticBoardBatch();
		$crossCutting->setKey('cross_cutting_analytic');
		$crossCutting->setTitle(Loc::getMessage('CRM_REPORT_CROSS_CUTTING_BATCH_TITLE'));
		$batchList[] = $crossCutting;

		return $batchList;
	}

	/**
	 * @return AnalyticBoard[]
	 */
	public static function onAnalyticPageCollect()
	{
		$analyticPageList = [];

		$leadAnalytics = new AnalyticBoard(CommonLead::BOARD_KEY);
		$leadAnalytics->setBatchKey('lead_analytic');
		$leadAnalytics->setTitle(Loc::getMessage('CRM_REPORT_COMMON_LEAD_BOARD_TITLE'));
		$leadAnalytics->setFilter(new CommonLeadFilter(CommonLead::BOARD_KEY));
		$analyticPageList[] = $leadAnalytics;

		$leadAnalytics = new AnalyticBoard(NewLead::BOARD_KEY);
		$leadAnalytics->setBatchKey('lead_analytic');
		$leadAnalytics->setTitle(Loc::getMessage('CRM_REPORT_NEW_LEAD_BOARD_TITLE'));
		$leadAnalytics->setFilter(new NewLeadFilter(NewLead::BOARD_KEY));
		$analyticPageList[] = $leadAnalytics;

		$leadAnalytics = new AnalyticBoard(RepeatLead::BOARD_KEY);
		$leadAnalytics->setBatchKey('lead_analytic');
		$leadAnalytics->setTitle(Loc::getMessage('CRM_REPORT_REPEATED_LEAD_BOARD_TITLE'));
		$leadAnalytics->setFilter(new RepeatLeadBoard(RepeatLead::BOARD_KEY));
		$analyticPageList[] = $leadAnalytics;

		$salesFunnel = new AnalyticBoard(SalesFunnelBoard::BOARD_KEY);
		$salesFunnel->setBatchKey('sales_analytic');
		$salesFunnel->setTitle(Loc::getMessage('CRM_REPORT_SALES_FUNNEL_BOARD_TITLE'));
		$salesFunnel->setFilter(new SalesFunnelFilter(SalesFunnelBoard::BOARD_KEY));
		$analyticPageList[] = $salesFunnel;

		$salesPlan = new AnalyticBoard(SalesPlanBoard::BOARD_KEY);
		$salesPlan->setBatchKey('sales_analytic');
		$salesPlan->setTitle(Loc::getMessage('CRM_REPORT_SALES_TARGET_BOARD_TITLE'));
		$salesPlan->setFilter(new Base(SalesPlanBoard::BOARD_KEY));
		$salesPlan->setDisabled(true);
		$analyticPageList[] = $salesPlan;

		$salesDynamic = new AnalyticBoard(SalesDynamic::BOARD_KEY);
		$salesDynamic->setBatchKey('sales_analytic');
		$salesDynamic->setTitle(Loc::getMessage('CRM_REPORT_SALES_DYNAMIC_BOARD_TITLE'));
		$salesDynamic->setBoardKey(SalesDynamic::BOARD_KEY);
		$salesDynamic->setFilter(new SalesDynamicFilter(SalesDynamic::BOARD_KEY));
		$analyticPageList[] = $salesDynamic;

		$salesPeriodCompare = new AnalyticBoard(SalesPeriodCompare::BOARD_KEY);
		$salesPeriodCompare->setBatchKey('sales_analytic');
		$salesPeriodCompare->setTitle(Loc::getMessage('CRM_REPORT_PERIOD_COMPARE_BOARD_TITLE'));
		$salesPeriodCompare->setBoardKey(SalesPeriodCompare::BOARD_KEY);
		$salesPeriodCompare->setFilter(new Base(SalesPeriodCompare::BOARD_KEY));
		$analyticPageList[] = $salesPeriodCompare;

		$managerEfficiency = new AnalyticBoard();
		$managerEfficiency->setBatchKey('manager_efficiency');
		$managerEfficiency->setTitle(Loc::getMessage('CRM_REPORT_MANAGER_EFFICIENCY_BOARD_TITLE'));
		$managerEfficiency->setBoardKey(self::MANAGER_EFFICIENCY_BOARD_KEY);
		$managerEfficiency->setFilter(new ManagerEfficiencyFilter(self::MANAGER_EFFICIENCY_BOARD_KEY));
		$managerEfficiency->setDisabled(true);
		$analyticPageList[] = $managerEfficiency;

		$managerEfficiencyDynamics = new AnalyticBoard();
		$managerEfficiencyDynamics->setBatchKey('manager_efficiency');
		$managerEfficiencyDynamics->setTitle(Loc::getMessage('CRM_REPORT_EFFICIENCY_DYNAMIC_BOARD_TITLE'));
		$managerEfficiencyDynamics->setBoardKey('manager_efficiency_dynamics');
		$managerEfficiencyDynamics->setFilter(new ManagerEfficiencyFilter('manager_efficiency_dynamics'));
		$managerEfficiencyDynamics->setDisabled(true);
		$analyticPageList[] = $managerEfficiencyDynamics;

		$contactDynamic = new AnalyticBoard();
		$contactDynamic->setBatchKey('clients');
		$contactDynamic->setTitle(Loc::getMessage('CRM_REPORT_CONTACT_DYNAMIC_BOARD_TITLE'));
		$contactDynamic->setBoardKey('contact_dynamic');
		$contactDynamic->setFilter(new ClientBaseFilter('contact_dynamic'));
		$contactDynamic->setDisabled(true);
		$analyticPageList[] = $contactDynamic;

		$companyDynamic = new AnalyticBoard();
		$companyDynamic->setBatchKey('clients');
		$companyDynamic->setTitle(Loc::getMessage('CRM_REPORT_COMPANY_DYNAMIC_BOARD_TITLE'));
		$companyDynamic->setBoardKey('company_dynamic');
		$companyDynamic->setFilter(new ClientBaseFilter('company_dynamic'));
		$companyDynamic->setDisabled(true);
		$analyticPageList[] = $companyDynamic;

		$stableCustomers = new AnalyticBoard();
		$stableCustomers->setBatchKey('clients');
		$stableCustomers->setTitle(Loc::getMessage('CRM_REPORT_STABLE_CLIENTS_BOARD_TITLE'));
		$stableCustomers->setBoardKey('stable_customers');
		$stableCustomers->setFilter(new ClientBaseFilter('stable_customers'));
		$stableCustomers->setDisabled(true);
		$analyticPageList[] = $stableCustomers;

		$financeRating = new AnalyticBoard();
		$financeRating->setBatchKey('clients');
		$financeRating->setTitle(Loc::getMessage('CRM_REPORT_FINANCE_RATING_BOARD_TITLE'));
		$financeRating->setBoardKey('finance_rating');
		$financeRating->setFilter(new ClientBaseFilter('finance_rating'));
		$financeRating->setDisabled(true);
		$analyticPageList[] = $financeRating;

		$board = new AnalyticBoard();
		$board->setTitle(Loc::getMessage('CRM_REPORT_ADVERTISE_SUM_EFFECT_BOARD_TITLE'));
		$board->setBoardKey(self::ADVERTISE_SUM_EFFECT_BOARD_KEY);
		$board->setFilter(new Filter\TrafficEffectFilter(self::ADVERTISE_SUM_EFFECT_BOARD_KEY));
		$board->setBatchKey('cross_cutting_analytic');
		$board->setDisabled(true);
		$analyticPageList[] = $board;

		$board = new AnalyticBoard();
		$board->setTitle(Loc::getMessage('CRM_REPORT_TRAFFIC_EFFECT_BOARD_TITLE'));
		$board->setBoardKey(self::TRAFFIC_EFFECT_BOARD_KEY);
		$board->setFilter(new Filter\TrafficEffectFilter(self::TRAFFIC_EFFECT_BOARD_KEY));
		$board->setBatchKey('cross_cutting_analytic');
		$board->addButton(
			new BoardButton(
				'<button class="ui-btn ui-btn-primary">'.
				Loc::getMessage('CRM_REPORT_TRAFFIC_EFFECT_CONFIG_BUTTON_TITLE').
				'</button>'
			)
		);
		$board->setDisabled(true);
		$analyticPageList[] = $board;

		return $analyticPageList;
	}

	/**
	 * @return BaseReport[]
	 */
	public static function onReportHandlerCollect()
	{
		$reportHandlerCollection = [];
		$reportHandlerCollection[] = new Lead();
		$reportHandlerCollection[] = new Client();
		$reportHandlerCollection[] = new Contact();
		$reportHandlerCollection[] = new Company();
		$reportHandlerCollection[] = new Deal();

		return $reportHandlerCollection;
	}

	/**
	 * @return array
	 */
	public static function onViewsCollect()
	{
		$viewsList = [];
		$viewsList[] = new View\SalesPlan();
		$viewsList[] = new View\ColumnFunnel();

		return $viewsList;
	}

	/**
	 * @return Category[]
	 */
	public static function onReportCategoriesCollect()
	{
		$categories = [];
		$crmCategory = new Category();
		$crmCategory->setKey('crm');
		$crmCategory->setLabel('CRM');
		$crmCategory->setParentKey('main');
		$categories[] = $crmCategory;

		return $categories;
	}

	/**
	 * @return Dashboard[]
	 */
	public static function onDefaultBoardsCollect()
	{
		$dashboards = [];
		$dashboards[] = CommonLead::get();
		$dashboards[] = NewLead::get();
		$dashboards[] = RepeatLead::get();

		$dashboards[] = SalesPlanBoard::get();
		$dashboards[] = SalesFunnelBoard::get();
		$dashboards[] = SalesDynamic::get();
		$dashboards[] = SalesPeriodCompare::get();

		$dashboards[] = self::buildClientBaseDefaultBoard();

		return $dashboards;
	}


	/**
	 * @return Dashboard
	 */
	private static function buildClientBaseDefaultBoard()
	{
		$board = new Dashboard();
		$board->setVersion('v1');
		$board->setBoardKey(self::CLIENT_BASE_BOARD_KEY);
		$board->setGId(Util::generateUserUniqueId());
		$board->setUserId(0);

		$firstRow = DashboardRow::factoryWithHorizontalCells(1);
		$firstRow->setWeight(1);
		$clientBaseLinearGraph = self::buildClientBaseLinerGraph();
		$clientBaseLinearGraph->setWeight($firstRow->getLayoutMap()['elements'][0]['id']);
		$firstRow->addWidgets($clientBaseLinearGraph);
		$board->addRows($firstRow);

		$secondRow = DashboardRow::factoryWithHorizontalCells(2);
		$secondRow->setWeight(2);
		$newClientCount = self::buildNewClientCountNumberBlock();
		$newClientCount->setWeight($secondRow->getLayoutMap()['elements'][0]['id']);
		$secondRow->addWidgets($newClientCount);
		$board->addRows($secondRow);

		$repeatedClientCount = self::buildRepeatedClientCountNumberBlock();
		$repeatedClientCount->setWeight($secondRow->getLayoutMap()['elements'][1]['id']);
		$secondRow->addWidgets($repeatedClientCount);
		$board->addRows($secondRow);

		return $board;
	}

	/**
	 * @return Widget
	 */
	private static function buildClientBaseLinerGraph()
	{
		$widget = new Widget();
		$widget->setGId(Util::generateUserUniqueId());
		$widget->setWidgetClass(BaseWidget::getClassName());
		$widget->setViewKey(LinearGraph::VIEW_KEY);
		$widget->setCategoryKey('crm');
		$widget->setBoardId(self::CLIENT_BASE_BOARD_KEY);

		$widget->getWidgetHandler()->updateFormElementValue('label', Loc::getMessage('CRM_REPORT_CLIENT_BASE_LINEAR_GRAPH_WIDGET_TITLE'));
		$widget->addConfigurations($widget->getWidgetHandler()->getConfigurations());

		$contactsCountDynamics = new Report();
		$contactsCountDynamics->setGId(Util::generateUserUniqueId());
		$contactsCountDynamics->setReportClassName(Client::getClassName());
		$contactsCountDynamics->setWidget($widget);
		$contactsCountDynamics->getReportHandler()->updateFormElementValue('label', 'clients');
		$contactsCountDynamics->getReportHandler()->updateFormElementValue('color', '#ff8792');
		$contactsCountDynamics->getReportHandler()->updateFormElementValue('groupingBy', Client::GROUPING_BY_DATE);
		$contactsCountDynamics->getReportHandler()->updateFormElementValue(
			'calculate',
			Client::WHAT_WILL_CALCULATE_COUNT
		);
		$contactsCountDynamics->addConfigurations($contactsCountDynamics->getReportHandler()->getConfigurations());
		$widget->addReports($contactsCountDynamics);

		return $widget;
	}

	/**
	 * @return Widget
	 */
	private static function buildNewClientCountNumberBlock()
	{
		$widget = new Widget();
		$widget->setGId(Util::generateUserUniqueId());
		$widget->setWidgetClass(BaseWidget::getClassName());
		$widget->setViewKey(Number::VIEW_KEY);
		$widget->setCategoryKey('crm');
		$widget->setBoardId(self::CLIENT_BASE_BOARD_KEY);

		$widget->getWidgetHandler()->updateFormElementValue('label', Loc::getMessage('CRM_REPORT_NEW_CLIENT_NUMBER_BLOCK_WIDGET_TITLE'));
		$widget->addConfigurations($widget->getWidgetHandler()->getConfigurations());

		$newClientCount = new Report();
		$newClientCount->setGId(Util::generateUserUniqueId());
		$newClientCount->setReportClassName(Company::getClassName());
		$newClientCount->setWidget($widget);
		$newClientCount->getReportHandler()->updateFormElementValue('label', Loc::getMessage('CRM_REPORT_NEW_CLIENT_NUMBER_BLOCK_WIDGET_TITLE'));
		$newClientCount->getReportHandler()->updateFormElementValue('color', '#4fc3f7');
		$newClientCount->getReportHandler()->updateFormElementValue('calculate', Company::WHAT_WILL_CALCULATE_COUNT);
		$newClientCount->addConfigurations($newClientCount->getReportHandler()->getConfigurations());
		$widget->addReports($newClientCount);

		return $widget;
	}

	/**
	 * @return Widget
	 */
	private static function buildRepeatedClientCountNumberBlock()
	{
		$widget = new Widget();
		$widget->setGId(Util::generateUserUniqueId());
		$widget->setWidgetClass(BaseWidget::getClassName());
		$widget->setViewKey(Number::VIEW_KEY);
		$widget->setCategoryKey('crm');
		$widget->setBoardId(self::CLIENT_BASE_BOARD_KEY);

		$widget->getWidgetHandler()->updateFormElementValue('label', Loc::getMessage('CRM_REPORT_REPEATED_CLIENT_NUMBER_BLOCK_WIDGET_TITLE'));
		$widget->addConfigurations($widget->getWidgetHandler()->getConfigurations());

		$newClientCount = new Report();
		$newClientCount->setGId(Util::generateUserUniqueId());
		$newClientCount->setReportClassName(Company::getClassName());
		$newClientCount->setWidget($widget);
		$newClientCount->getReportHandler()->updateFormElementValue('label', Loc::getMessage('CRM_REPORT_REPEATED_CLIENT_NUMBER_BLOCK_WIDGET_TITLE'));
		$newClientCount->getReportHandler()->updateFormElementValue('color', '#eec200');
		$newClientCount->getReportHandler()->updateFormElementValue('calculate', Company::WHAT_WILL_CALCULATE_COUNT);
		$newClientCount->addConfigurations($newClientCount->getReportHandler()->getConfigurations());
		$widget->addReports($newClientCount);

		return $widget;
	}

}