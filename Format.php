<?php

namespace Str;

class Format {
	static public function xml_decode(string $xml): array{
		$xml 	= simplexml_load_string($xml);
		$json 	= json_encode($xml);
		
		return json_decode($json, true);
	}
	
	static public function str_amount(float $num, int $dec=0, string $thousand_sep='', string $decimal_sep=''): string{
		$locale = \dbdata\Lang::get_locale();
		
		return number_format($num, $dec, $decimal_sep ?: $locale['decimal_point'], $thousand_sep ?: $locale['thousands_sep']);
	}
	
	static public function amount(string $amount): int{
		/*$amount = str_replace(' ', '', $amount);
		$pos_dot = strpos($amount, '.');
		$pos_comma = strpos($amount, ',');
		if($pos_dot !== false && $pos_comma !== false){
			$amount = str_replace($pos_dot > $pos_comma ? ',' : '.', '', $amount);
		}
		
		return (int)round(((float)str_replace(',', '.', $amount) * 100));*/
	}
}