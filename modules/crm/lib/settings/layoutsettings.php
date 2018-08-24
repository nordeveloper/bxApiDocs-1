<?php
namespace Bitrix\Crm\Settings;
use Bitrix\Main;
class LayoutSettings
{
	/** @var LayoutSettings */
	private static $current = null;

	/** @var BooleanSetting */
	private $enableSlider = null;
	/** @var BooleanSetting */
	private $enableSimpleTimeFormat = null;
	/** @var BooleanSetting */
	private $enableUserNameSorting = null;

	function __construct()
	{
		$this->enableSlider = new BooleanSetting('enable_slider', false);
		$this->enableSimpleTimeFormat = new BooleanSetting('enable_simple_time_format', true);
		$this->enableUserNameSorting = new BooleanSetting('enable_user_name_sorting', false);
	}
	/**
	 * Get current instance
	 * @return LayoutSettings
	 */
	public static function getCurrent()
	{
		if(self::$current === null)
		{
			self::$current = new LayoutSettings();
		}
		return self::$current;
	}
	/**
	 * Check if slider enabled for edit and view actions
	 * @return bool
	 */
	public function isSliderEnabled()
	{
		return $this->enableSlider->get();
	}
	/**
	 * Enabled slider for edit and view actions
	 * @param bool $enabled Enabled Flag.
	 * @return void
	 */
	public function enableSlider($enabled)
	{
		$this->enableSlider->set($enabled);
	}
	/**
	 * Check if simple time format enabled for display system fields (CREATED, LAST_MODIFIED and etc)
	 * @return bool
	 */
	public function isSimpleTimeFormatEnabled()
	{
		return $this->enableSimpleTimeFormat->get();
	}
	/**
	 * Enable simple time format
	 * @param bool $enabled Enabled Flag.
	 * @return void
	 */
	public function enableSimpleTimeFormat($enabled)
	{
		$this->enableSimpleTimeFormat->set($enabled);
	}
	/**
	 * Check if user name sorting enabled
	 * @return bool
	 */
	public function isUserNameSortingEnabled()
	{
		return $this->enableUserNameSorting->get();
	}
	/**
	 * Enable user name sorting
	 * @param bool $enabled Enabled Flag.
	 * @return void
	 */
	public function enableUserNameSorting($enabled)
	{
		$this->enableUserNameSorting->set($enabled);
	}
}