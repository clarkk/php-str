<?php

namespace Str;

class Str {
	const NBSP 			= "\xA0";
	const NBSP_UTF8 	= "\xC2\xA0";
	const NBSP_UNICODE 	= "\x00\xA0";
	const NNBSP_UNICODE = "\x20\x2F";
	
	static public function filter_utf8(string $value, bool $allow_newlines=true): string{
		return preg_replace('/[^[:print:]'.($allow_newlines ? '\n' : '').']/u', '', mb_convert_encoding($value, 'UTF-8'));
	}
	
	static public function trim(string $value, bool $allow_newlines=true): string{
		$value = strtr($value, [
			self::NBSP_UTF8 	=> ' ',
			self::NBSP_UNICODE 	=> ' ',
			self::NNBSP_UNICODE => ' ',
			self::NBSP 			=> ' '
		]);
		
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
}