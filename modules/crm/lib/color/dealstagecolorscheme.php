<?php
namespace Bitrix\Crm\Color;
use Bitrix\Main;
class DealStageColorScheme extends PhaseColorScheme
{
	/**
	 * @param int $categoryID Deal category ID.
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 */
	public function __construct($categoryID = 0)
	{
		if(!is_int($categoryID))
		{
			$categoryID = (int)$categoryID;
		}
		$categoryID = max($categoryID, 0);

		$this->categoryID = $categoryID;
		parent::__construct(self::prepareCategoryOptionName($categoryID));
	}
	/** @var int */
	private $categoryID = 0;
	/** @var DealStageColorScheme[]  */
	private static $items = array();
	/**
	 * Get Deal category ID
	 * @return int
	 */
	public function getCategoryId()
	{
		return $this->categoryID;
	}
	/**
	 * Get default element color by semantic ID.
	 * @param string $stageID Deal stage ID.
	 * @param int $categoryID Deal category ID.
	 * @return string
	 */
	public static function getDefaultColorByStage($stageID, $categoryID = 0)
	{
		return self::getDefaultColorBySemantics(\CCrmDeal::GetSemanticID($stageID, $categoryID));
	}
	/**
	 * Get default color for element.
	 * @param string $name Element Name.
	 * @return string
	 */
	public function getDefaultColor($name)
	{
		return self::getDefaultColorByStage($name, $this->categoryID);
	}
	/**
	 * Setup scheme by default
	 * @return void
	 */
	public function setupByDefault()
	{
		$this->reset();
		$infos = \CCrmDeal::GetStages($this->categoryID);
		foreach($infos as $k => $v)
		{
			$this->addElement(new PhaseColorSchemeElement($k, $this->getDefaultColor($k)));
		}
	}
	/**
	 * Get scheme by category
	 * @param int $categoryID Deal category ID.
	 * @return DealStageColorScheme
	 * @throws Main\ArgumentNullException
	 */
	public static function getByCategory($categoryID = 0)
	{
		if(!is_int($categoryID))
		{
			$categoryID = (int)$categoryID;
		}
		$categoryID = max($categoryID, 0);

		if(!self::$items[$categoryID])
		{
			self::$items[$categoryID] = new DealStageColorScheme($categoryID);
			if(!self::$items[$categoryID]->load())
			{
				self::$items[$categoryID]->setupByDefault();
			}
		}
		return self::$items[$categoryID];
	}
	/**
	 * Prepare option name for specified category.
	 * @param int $categoryID Deal category ID.
	 */
	protected static function prepareCategoryOptionName($categoryID)
	{
		return $categoryID > 0 ? "CONFIG_STATUS_DEAL_STAGE_{$categoryID}" : 'CONFIG_STATUS_DEAL_STAGE';
	}
	/**
	 * Remove scheme by category.
	 * @param int $categoryID Deal category ID.
	 * @return bool
	 */
	public static function removeByCategory($categoryID)
	{
		if(!is_int($categoryID))
		{
			$categoryID = (int)$categoryID;
		}

		if($categoryID <= 0)
		{
			return false;
		}

		self::removeByName(self::prepareCategoryOptionName($categoryID));
		return true;
	}
	/**
	 * Get current scheme (for default category)
	 * @return DealStageColorScheme
	 * @throws Main\ArgumentNullException
	 */
	public static function getCurrent()
	{
		return self::getByCategory(0);
	}
}