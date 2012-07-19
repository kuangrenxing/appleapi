<?php

class Arrays
{
	public static function rand(array $array, $count = 1)
	{
		$arrayCount	= count($array);
		
		if ($count < 1 || $arrayCount < 1)
			return array();
		
		if ($count > $arrayCount)
			$count = $arrayCount;
		
		$keys		= array_rand($array, $count);
		$randArray	= array();
		
		if (!is_array($keys))
			$keys = array($keys);
		
		foreach ($keys as $key)
			$randArray[$key] = $array[$key];
		
		return $randArray;
	}
}
