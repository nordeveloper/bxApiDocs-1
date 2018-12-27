<?php


namespace Bitrix\Crm\Order\Rest;


class Internalizer extends \Bitrix\Sale\Rest\Internalizer
{

	public function prepare()
	{
		$name = $this->getName();
		$arguments = $this->getArguments();
		$controller = $this->getController();

		$fields = parent::prepare()['fields'];

		if($name == 'modify'
			|| $name == 'trymodify'
			|| $name == 'import')
		{
			$argumentsFields = $arguments['fields'];

			if($controller instanceof \Bitrix\Sale\Controller\Order)
			{
				if(isset($argumentsFields['ORDER']['CLIENTS']))
					$fields['ORDER']['CLIENTS'] = $this->prepareModifyListFields($argumentsFields['ORDER']['CLIENTS'], new \Bitrix\Crm\Order\Rest\Entity\OrderContactCompany(), ['XML_ID']);//only for importAction XML_ID

				if(isset($argumentsFields['ORDER']['REQUISITE_LINK']))
					$fields['ORDER']['REQUISITE_LINK'] = $this->prepareModifyListFields($argumentsFields['ORDER']['REQUISITE_LINK'], new \Bitrix\Crm\Order\Rest\Entity\OrderRequisiteLink());
			}
			elseif ($controller instanceof \Bitrix\Crm\Order\Rest\Entity\OrderContactCompany)
			{
				//TODO: доделать rest для Клиентов
			}
			elseif ($controller instanceof \Bitrix\Crm\Order\Rest\Entity\OrderRequisiteLink)
			{
				//TODO: доделать rest для Реквизита заказа
			}
		}

		$arguments['fields'] = $fields;

		return $arguments;
	}
}