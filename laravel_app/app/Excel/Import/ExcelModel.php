<?php

namespace App\Excel\Import;

abstract class ExcelModel
{
	protected $settings = [];

	public function __call($methodName, $params = null)
	{
		$methodPrefix = substr($methodName, 0, 3);
		$key = self::camelCase(substr($methodName, 3));
		if($methodPrefix == 'set' && count($params) == 1)
		{
			$value = $params[0];
			$this->settings[$key] = $value;
		}
		elseif($methodPrefix == 'get')
		{
			if(array_key_exists($key, $this->settings)) return $this->settings[$key];
		}
		else
		{
			exit('Opps! The method is not defined!');
		}
	}

	/**
	 * Convert a string into a CamelCase string format
	 *
	 * @param $str
	 * @param $noStrip
	 *
	 * @return String
	 */
	public static function camelCase($str, array $noStrip = array())
	{
		// non-alpha and non-numeric characters become spaces
		$str = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $str);
		$str = trim($str);
		// uppercase the first character of each word
		$str = ucwords($str);
		$str = str_replace(" ", "", $str);
		$str = lcfirst($str);

		return $str;
	}
}
