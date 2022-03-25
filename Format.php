<?php

namespace Str;

class Format {
	static private $datasize_units = [
		'Gb'	=> 1024 * 1024 * 1024,
		'Mb'	=> 1024 * 1024,
		'Kb'	=> 1024,
		'Bytes'	=> 1
	];
	
	static public function xml_decode(string $xml): array{
		$xml 	= simplexml_load_string($xml);
		$json 	= json_encode($xml);
		
		return json_decode($json, true);
	}
	
	static public function num(float $num, int $dec=0, ?string $thousand_sep=null, ?string $decimal_sep=null): string{
		$locale = \dbdata\Lang::get_locale();
		
		return number_format($num, $dec, $decimal_sep ?? $locale['decimal_point'], $thousand_sep ?? $locale['thousands_sep']);
	}
	
	static public function amount(string $amount): int{
		$locale = \dbdata\Lang::get_locale();
		$amount = str_replace(' ', '', $amount);
		$d 		= strrpos($amount, $locale['decimal_point']);
		$t 		= strrpos($amount, $locale['thousands_sep']);
		
		if($d !== false && $t !== false){
			$amount = str_replace(max($d, $t) == $d ? $locale['thousands_sep'] : $locale['decimal_point'], '', $amount);
		}
		
		return round((float)str_replace(',', '.', $amount) * 100);
	}
	
	static public function datasize(int $int): string{
		if(!$int){
			return '0';
		}
		
		foreach(self::$datasize_units as $unit => $value){
			$scale = $int / $value;
			if($scale >= 1){
				return self::num($scale, is_int($scale) ? 0 : 2).' '.$unit;
			}
		}
		
		return '0';
	}
}