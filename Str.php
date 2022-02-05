<?php

namespace Str;

class Str {
	//	Non-breaking spaces
	const NBSP 			= "\xA0";
	const NBSP_UTF8 	= "\xC2".self::NBSP;
	const NBSP_UNICODE 	= "\x00".self::NBSP;
	
	//	Soft hyphen
	const SHY 			= "\xAD";
	const SHY_UTF8 		= "\xC2".self::SHY;
	const SHY_UNICODE 	= "\x00".self::SHY;
	
	//	Non-breaking hyphen
	const NBHY 			= "\x20\x11";
	
	//	Dashes
	// https://en.wikipedia.org/wiki/Hyphen#Soft_and_hard_hyphens
	// figure dash
	// en dash
	// em dash
	// horizontal bar
	
	static public function filter_utf8(string $value, bool $allow_newlines=true): string{
		return preg_replace('/[^[:print:]'.($allow_newlines ? '\n' : '').']/u', '', mb_convert_encoding($value, 'UTF-8'));
	}
	
	static public function trim(string $value, bool $allow_newlines=true): string{
		self::normalize($value);
		
		if($allow_newlines){
			$has_newline = strpos($value, "\n") !== false;
			if($has_newline){
				$value = implode("\n", array_map('trim', explode("\n", $value)));
			}
		}
		
		$value = trim($value);
		
		if($allow_newlines && $has_newline && strpos($value, "\n\n\n") !== false){
			$value = preg_replace("/\n{3,}/", "\n\n", $value);
		}
		
		if(strpos($value, '  ') !== false){
			$value = preg_replace('/ +/', ' ', $value);
		}
		
		return $value;
	}
	
	static public function normalize(string &$value){
		if(strpos($value, self::NBSP) !== false){
			$value = strtr($value, [
				self::NBSP_UTF8 	=> ' ',
				self::NBSP_UNICODE 	=> ' '
			]);
			
			//	Avoid breaking UTF8 unicode chars
			$value = preg_replace('/\xA0/u', ' ', $value);
		}
		
		if(strpos($value, self::SHY) !== false){
			$value = strtr($value, [
				self::SHY_UTF8 		=> '-',
				self::SHY_UNICODE 	=> '-'
			]);
			
			//	Avoid breaking UTF8 unicode chars
			$value = preg_replace('/\xAD/u', '-', $value);
		}
		
		if(strpos($value, self::NBHY) !== false){
			//	Avoid breaking UTF8 unicode chars
			$value = preg_replace('/\x20\x11/u', '-', $value);
		}
	}
}