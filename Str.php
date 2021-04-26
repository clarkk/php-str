<?php

namespace Str;

class Str {
	static public function filter_utf8(string $value, bool $allow_newlines=true): string{
		return preg_replace('/[^[:print:]'.($allow_newlines ? '\n' : '').']/u', '', mb_convert_encoding($value, 'UTF-8'));
	}
	
	static public function trim(string $value, bool $allow_newlines=true): string{
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