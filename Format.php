<?php

namespace Str;

class Format {
	static private $datasize_units = [
		'Gb'	=> 1024 * 1024 * 1024,
		'Mb'	=> 1024 * 1024,
		'Kb'	=> 1024,
		'Bytes'	=> 1
	];
	
	static private $decimal_point 	= '.';
	static private $thousand_sep 	= ',';
	
	const DECIMAL_POINT = 'decimal_point';
	const THOUSAND_SEP 	= 'thousands_sep';
	
	const BLOCK_SIZE = 4096;
	
	static public function init(string $locale){
		setlocale(LC_MONETARY, $locale);
		$localeconv = localeconv();
		
		self::$decimal_point 	= $localeconv['mon_decimal_point'];
		self::$thousand_sep 	= $localeconv['mon_thousands_sep'];
		
		setlocale(LC_COLLATE, $locale);
		setlocale(LC_CTYPE, $locale);
	}
	
	static public function get_locale(): array{
		return [
			self::DECIMAL_POINT	=> self::$decimal_point,
			self::THOUSAND_SEP	=> self::$thousand_sep
		];
	}
	
	static public function xml_decode(string $xml): array{
		$xml 	= simplexml_load_string($xml);
		$json 	= json_encode($xml);
		
		return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
	}
	
	static public function dec(float $num, int $dec=0, ?string $decimal_sep=null): string{
		return number_format($num, $dec, $decimal_sep ?? self::$decimal_point, '');
	}
	
	static public function num(float $num, int $dec=0, ?string $thousand_sep=null, ?string $decimal_sep=null): string{
		return number_format($num, $dec, $decimal_sep ?? self::$decimal_point, $thousand_sep ?? self::$thousand_sep);
	}
	
	static public function amount(string $amount): int{
		$amount = str_replace(' ', '', $amount);
		$d 		= strrpos($amount, self::$decimal_point);
		$t 		= strrpos($amount, self::$thousand_sep);
		
		if($d !== false && $t !== false){
			//	Strip thousand separator
			$amount = str_replace(max($d, $t) == $d ? self::$thousand_sep : self::$decimal_point, '', $amount);
		}
		elseif($d !== false || $t !== false){
			//	Strip both if only thousand separator and no decimal point
			if(strlen($amount) - max($d, $t) - 1 == 3){
				return round((float)str_replace([self::$thousand_sep, self::$decimal_point], '', $amount) * 100);
			}
		}
		
		$int = round((float)str_replace(',', '.', $amount) * 100);
		if(preg_match('/[^\d.\-]/', $int)){
			return 0;
		}
		
		return $int;
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
	
	static public function datasize_block(int $int): int{
		return ceil($int / self::BLOCK_SIZE) * self::BLOCK_SIZE;
	}
}