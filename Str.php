<?php

namespace Str;

// https://stackoverflow.com/questions/1176904/how-to-remove-all-non-printable-characters-in-a-string

class Str {
	const ENC_UTF8 			= 'UTF-8';
	const ENC_LATIN 		= 'ISO-8859-1';
	const ENC_LATIN_WIN 	= 'WINDOWS-1252';
	
	//	Non-breaking spaces: https://www.compart.com/en/unicode/U+00A0
	private const NBSP 				= '\xA0';
	private const NBSP_UTF8 		= '\xC2'.self::NBSP;
	private const NBSP_UTF16 		= '\x00'.self::NBSP;
	
	//	Zero-width space: https://www.compart.com/en/unicode/U+200B
	private const ZWSP 				= '\x20\x0B'; // same in UTF8
	//private const ZWNJ 			= '\x20\x0C'; // same in UTF8
	//private const ZWJ 			= '\x20\x0D'; // same in UTF8
	
	//	Soft hyphen: https://www.compart.com/en/unicode/U+00AD
	private const SHY 				= '\xAD';
	private const SHY_UTF8 			= '\xC2'.self::SHY;
	private const SHY_UTF16 		= '\x00'.self::SHY;
	
	//	Non-breaking hyphen
	private const NBHY 				= '\x20\x11'; // same in UTF8
	
	/*
	//	Non-breaking spaces
	const NBSP 				= "\xA0";
	const NBSP_UTF8 		= "\xC2".self::NBSP;
	const NBSP_UNICODE 		= "\x00".self::NBSP;
	
	//	Soft hyphen
	const SHY 				= "\xAD";
	const SHY_UTF8 			= "\xC2".self::SHY;
	const SHY_UNICODE 		= "\x00".self::SHY;
	
	//	Non-breaking hyphen
	const NBHY 				= "\x20\x11";
	*/
	
	/*
		https://www.regular-expressions.info/unicode.html
		
		[:print:] == \p{Print} == \pP, negation: \P{Print} == \PP
		[:cntrl:] == \p{Cntrl} == \pC, negation: \P{Cntrl} == \PC
		
		- \PC is the same as [^\pC]					(matches all non control chars)
		- Therefore [^\PC] is the same as \pC 		(matches control chars)
	*/
	
	private const UNICODE_FILTER_PRINT 		= '\P{Cc}\p{Cf}\p{Cn}\p{Cs}';
	
	private const PATTERN_FILTER_PRINT 		= '/[^'.self::UNICODE_FILTER_PRINT.']/';
	private const PATTERN_FILTER_PRINT_N 	= '/[^'.self::UNICODE_FILTER_PRINT.'\n]/';
	private const PATTERN_FILTER_PRINT_S 	= '/[^'.self::UNICODE_FILTER_PRINT.'\s]/';
	
	private const PATTERN_MATCH_PRINT_S 	= '/[\PC\s]/';
	
	static public function filter_utf8(string $str, string $encoding=self::ENC_LATIN, string $allow_whitespace='n', bool $strip_mb4=true): string{
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
		
		$str = preg_replace($pattern.'u', '', mb_convert_encoding($str, self::ENC_UTF8, $encoding));
		if($strip_mb4){
			$str = self::strip_mb4($str);
		}
		
		return $str;
	}
	
	static public function decode_utf8(string $str): string{
		return mb_convert_encoding($str, self::ENC_LATIN, self::ENC_UTF8);
	}
	
	static public function is_valid_utf8(string $str): bool{
		return preg_match(self::PATTERN_FILTER_PRINT_S.'u', $str) ? false : true;
	}
	
	static public function check_printable_ratio(string $str, bool $utf8=false): ?array{
		$strlen 		= $utf8 ? mb_strlen($str) : strlen($str);
		$non_printable 	= preg_match_all(self::PATTERN_FILTER_PRINT_S.($utf8 ? 'u' : ''), $str);
		$printable 		= preg_match_all(self::PATTERN_MATCH_PRINT_S.($utf8 ? 'u' : ''), $str);
		
		if(!$printable || $printable != $strlen - $non_printable){
			return [
				'all'			=> $strlen,
				'non_printable'	=> $non_printable,
				'printable'		=> $printable,
				'diff'			=> abs($printable - $strlen + $non_printable)
			];
		}
		
		return null;
	}
	
	static public function trim(string $str, bool $allow_newlines=true): string{
		self::normalize($str);
		
		if($allow_newlines){
			$has_newline = strpos($str, "\n") !== false;
			if($has_newline){
				$str = implode("\n", array_map('trim', explode("\n", $str)));
			}
		}
		
		$str = trim($str);
		
		if($allow_newlines && $has_newline && strpos($str, "\n\n\n") !== false){
			$str = preg_replace("/\n{3,}/", "\n\n", $str);
		}
		
		if(strpos($str, '  ') !== false){
			$str = preg_replace('/ +/', ' ', $str);
		}
		
		return $str;
	}
	
	static public function normalize(string &$str): void{
		$str = preg_replace('/'.self::NBSP.'|'.self::ZWSP.'/u', ' ', $str);
		$str = preg_replace('/'.self::SHY.'|'.self::NBHY.'/u', '-', $str);
		//$str = preg_replace('/'.self::ZWNJ.'|'.self::ZWJ.'/u', '', $str);
		
		/*if(strpos($str, self::NBSP) !== false){
			$str = strtr($str, [
				self::NBSP_UTF8 	=> ' ',
				self::NBSP_UNICODE 	=> ' '
			]);
			
			//	Avoid breaking UTF8 unicode chars
			$str = preg_replace('/\xA0/u', ' ', $str);
		}
		
		if(strpos($str, self::SHY) !== false){
			$str = strtr($str, [
				self::SHY_UTF8 		=> '-',
				self::SHY_UNICODE 	=> '-'
			]);
			
			//	Avoid breaking UTF8 unicode chars
			$str = preg_replace('/\xAD/u', '-', $str);
		}
		
		if(strpos($str, self::NBHY) !== false){
			//	Avoid breaking UTF8 unicode chars
			$str = preg_replace('/\x20\x11/u', '-', $str);
		}*/
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