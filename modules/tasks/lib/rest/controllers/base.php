<?php
/**
 * Created by PhpStorm.
 * User: maxyc
 * Date: 10.08.18
 * Time: 12:44
 */
namespace Bitrix\Tasks\Rest\Controllers;

class Base extends \Bitrix\Main\Engine\Controller
{
	public function manifestAction()
	{
		$list = [];

		$reflection = new \ReflectionClass(get_called_class());

		foreach($reflection->getMethods() as $method)
		{
			if(get_called_class() != $method->class || !$method->isPublic())
			{
				continue;
			}

			$pattern = "#\@([a-zA-Z]+\s*[a-zA-Z0-9, ()_].*)#";
			preg_match_all($pattern, $method->getDocComment(), $matches, PREG_PATTERN_ORDER);
			$docParams = [];
			foreach($matches[1] as $row)
			{
				list($paramName, $paramText) = explode(' ', $row, 2);

				switch ($paramName)
				{
					case 'param':
						list($fieldType, $fieldName, $fieldDesc) = explode(' ', $paramText, 3);
						$fieldName = substr($fieldName, 1);

						$docParams[$paramName][$fieldName] = [
							'type'        => $fieldType,
							'description' => $fieldDesc
						];
					break;
					case 'return':
						list($fieldType, $fieldDesc) = explode(' ', $paramText, 2);

						$docParams[$paramName] = [
							'type'        => $fieldType,
							'description' => $fieldDesc
						];
					break;
				}
			}

			$params = [];
			foreach($method->getParameters() as $param)
			{
				$params[] = [
					'name'=>$param->getName(),
					'description'=>$docParams['param'][$param->getName()]['description'],
					'optional'=>$param->isOptional(),
					'default'=>$param->isOptional() ? strtolower((string)$param->getDefaultValue()) : 'null',
					'type'=>$docParams['param'][$param->getName()]['type'] //TODO php 7+
				];
			}

			preg_match('#\/\*\*\n.*?\* (.*?)$#im', $method->getDocComment(), $match);
			$title = trim($match[1]);

			$list[ $method->getName() ] = [
				'comment'=>$title,
				'docComment'=>trim($method->getDocComment()),
				'arguments'=>$params ? $params : null,
				'return'=>$method->getReturnType() ? $method->getReturnType() : $docParams['return']['type']
			];
		}

		return $list;
	}
}