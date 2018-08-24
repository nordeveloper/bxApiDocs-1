<?php
namespace Bitrix\Crm\Color;
use Bitrix\Main;
class LeadStatusColorScheme extends PhaseColorScheme
{
	public function __construct()
	{
		parent::__construct('CONFIG_STATUS_STATUS');
	}
	/** @var LeadStatusColorScheme|null  */
	private static $current = null;

	/**
	 * Get default element color by semantic ID.
	 * @param string $statusID Lead status ID.
	 * @return string
	 */
	public static function getDefaultColorByStatus($statusID)
	{
		return self::getDefaultColorBySemantics(\CCrmLead::GetSemanticID($statusID));
	}
	/**
	 * Get default color for element.
	 * @param string $name Element Name.
	 * @return string
	 */
	public function getDefaultColor($name)
	{
		return self::getDefaultColorByStatus($name);
	}
	/**
	 * Setup scheme by default
	 * @return void
	 */
	public function setupByDefault()
	{
		$this->reset();
		$infos = \CCrmLead::GetStatuses();
		foreach($infos as $k => $v)
		{
			$this->addElement(new PhaseColorSchemeElement($k, $this->getDefaultColor($k)));
		}
	}
	/**
	 * Get current scheme
	 * @return LeadStatusColorScheme
	 * @throws Main\ArgumentNullException
	 */
	public static function getCurrent()
	{
		if(self::$current === null)
		{
			self::$current = new LeadStatusColorScheme();
			if(!self::$current->load())
			{
				self::$current->setupByDefault();
			}
		}
		return self::$current;
	}
}