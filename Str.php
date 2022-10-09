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
	
	/*
		https://www.regular-expressions.info/unicode.html
		
		[:print:] == \p{Print} == \pP, negation: \P{Print} == \PP
		[:cntrl:] == \p{Cntrl} == \pC, negation: \P{Cntrl} == \PC
		
		- \PC is the same as [^\pC]					(matches all non control chars)
		- Therefore [^\PC] is the same as \pC 		(matches control chars)
	*/
	
	const PATTERN_FILTER_PRINT 		= '/[^\P{Cc}\p{Cf}\p{Cn}\p{Cs}]/';
	const PATTERN_FILTER_PRINT_N 	= '/[^\P{Cc}\p{Cf}\p{Cn}\p{Cs}\n]/';
	const PATTERN_FILTER_PRINT_S 	= '/[^\P{Cc}\p{Cf}\p{Cn}\p{Cs}\s]/';
	
	const PATTERN_MATCH_PRINT_S 	= '/[\PC\s]/';
	
	static public function filter_utf8(string $value, string $allow_whitespace='n', bool $strip_mb4=true): string{
		switch($allow_whitespace){
			//	Allows space and new line (\n)
			case 'n':
				$pattern = self::PATTERN_FILTER_PRINT_N;
				break;
			
			//	Allows all whitespaces (\n\r\t)
			case 's':
				$pattern = self::PATTERN_FILTER_PRINT_S;
				break;
			
			//	Only allows space
			default:
				$pattern = self::PATTERN_FILTER_PRINT;
		}
		
		$value = preg_replace($pattern.'u', '', mb_convert_encoding($value, 'UTF-8'));
		if($strip_mb4){
			$value = self::strip_mb4($value);
		}
		
		return $value;
	}
	
	static public function is_valid_utf8(string $value): bool{
		return preg_match(self::PATTERN_FILTER_PRINT_S.'u', $value) ? false : true;
	}
	
	static public function check_printable_ratio(string $value, bool $utf8=false): bool{
		$strlen 		= $utf8 ? mb_strlen($value) : strlen($value);
		$non_printable 	= preg_match_all(self::PATTERN_FILTER_PRINT_S.($utf8 ? 'u' : ''), $value);
		$printable 		= preg_match_all(self::PATTERN_MATCH_PRINT_S.($utf8 ? 'u' : ''), $value);
		
		return !$printable || $printable != $strlen - $non_printable ? false : true;
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
	
	static private function strip_mb4(string $str): string{
		// https://www.compart.com/en/unicode/plane
		// https://unicode.org/emoji/charts/index.html
		
		$planes_1_3 	= '\xF0[\x90-\xBF][\x80-\xBF]{2}';
		$planes_4_15 	= '[\xF1-\xF3][\x80-\xBF]{3}';
		$plane_16 		= '\xF4[\x80-\x8F][\x80-\xBF]{2}';
		
		return preg_replace("/(?:$planes_1_3|$planes_4_15|$plane_16)/", '', $str);
	}
}