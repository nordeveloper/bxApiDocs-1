<?
namespace Bitrix\Crm\Order;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Helpers\Order\Builder\OrderBuilder;
use Bitrix\Sale\Helpers\Order\Builder\SettingsContainer;

if (!Loader::includeModule('sale'))
{
	return;
}

/**
 * Class OrderBuilderCrm
 * @package Bitrix\Crm\Order
 * @internal
 */
final class OrderBuilderCrm extends OrderBuilder
{
	/**
	 * OrderBuilderCrm constructor.
	 * @param SettingsContainer $settings
	 */
	public function __construct(SettingsContainer $settings)
	{
		parent::__construct($settings);
		$this->setBasketBuilder(new BasketBuilderCrm($this));
	}

	/**
	 * @return $this
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	public function buildBasket()
	{
		if(is_array($this->formData['PRODUCT']) && !empty($this->formData['PRODUCT']))
		{
			foreach($this->formData['PRODUCT'] as $k => $p)
			{
				if(isset($p['FIELDS_VALUES']))
				{
					$fieldsValues = $p['FIELDS_VALUES'];

					try
					{
						$fieldsValues = Json::decode($fieldsValues);

						if(is_array($fieldsValues))
						{
							$fields = array_intersect_key($p, array_flip(BasketItem::getAllFields()));
							$fields = array_merge($fieldsValues, $fields);
						}
					}
					catch(ArgumentException $e)
					{
						$this->getErrorsContainer()->addError(
							new Error(
								Loc::getMessage("CRM_ORDERBUILDER_PRODUCT_ERROR"),
								['#BASKET_CODE#' => $k]
							)
						);
					}
				}

				$fields['PRICE'] = str_replace([' ', ','], ['', '.'], $fields['PRICE']);
				$fields['QUANTITY'] = str_replace([' ', ','], ['', '.'], $fields['QUANTITY']);
				$fields['OFFER_ID'] = $fields['PRODUCT_ID'];
				$this->formData['PRODUCT'][$k] = $fields;
			}
		}

		return parent::buildBasket();
	}

	/**
	 * @return $this
	 * @throws ArgumentException
	 */
	public function setFields()
	{
		$fields = ['COMMENTS', 'STATUS_ID'];

		foreach($fields as $field)
		{
			if(isset($this->formData[$field]))
			{
				$r = $this->order->setField($field, $this->formData[$field]);
				if (!$r->isSuccess())
				{
					$this->getErrorsContainer()->addErrors($r->getErrors());
				}
			}
		}

		if (!empty($this->formData['REQUISITE_BINDING']) && is_array($this->formData['REQUISITE_BINDING']))
		{
			$this->order->setRequisiteLink($this->formData['REQUISITE_BINDING']);
		}

		return parent::setFields();
	}

	public function buildPayments()
	{
		if(is_array($this->formData["PAYMENT"]))
		{
			foreach($this->formData["PAYMENT"] as $idx => $data)
			{
				if(is_array($data['fields']))
				{
					$this->formData["PAYMENT"][$idx] = $data['fields'];
				}
			}
		}

		return parent::buildPayments();
	}
}