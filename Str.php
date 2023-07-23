<?php

namespace Str;

// https://stackoverflow.com/questions/1176904/how-to-remove-all-non-printable-characters-in-a-string

class Str {
	const ENC_UTF8 			= 'UTF-8';
	const ENC_LATIN 		= 'ISO-8859-1';
	const ENC_LATIN_WIN 	= 'WINDOWS-1252';
	
	//	Non-breaking space: https://www.compart.com/en/unicode/U+00A0
	private const NBSP 				= '\xA0';
	private const NBSP_UTF8 		= '\xC2'.self::NBSP;
	private const NBSP_UTF16 		= '\x00'.self::NBSP;
	private const NBSP_UNICODE 		= '00A0';
	
	//	Zero-width space: https://www.compart.com/en/unicode/U+200B
	private const ZWSP_UTF8 		= '\xE2\x80\x8B';
	private const ZWSP_UTF16 		= '\x20\x0B';
	private const ZWSP_UNICODE 		= '200B';
	//private const ZWNJ_UTF16 		= '\x20\x0C';
	//private const ZWJ_UTF16 		= '\x20\x0D';
	
	//	Soft hyphen: https://www.compart.com/en/unicode/U+00AD
	private const SHY 				= '\xAD';
	private const SHY_UTF8 			= '\xC2'.self::SHY;
	private const SHY_UTF16 		= '\x00'.self::SHY;
	private const SHY_UNICODE 		= '00AD';
	
	//	Non-breaking hyphen: https://www.compart.com/en/unicode/U+2011
	private const NBHY_UTF8 		= '\xE2\x80\x91';
	private const NBHY_UTF16 		= '\x20\x11';
	private const NBHY_UNICODE 		= '2011';
	
	/*
		https://www.regular-expressions.info/unicode.html
		
		[:print:] == \p{Print} == \pP, negation: \P{Print} == \PP
		[:cntrl:] == \p{Cntrl} == \pC, negation: \P{Cntrl} == \PC
		
		- \PC is the same as [^\pC]					(matches all non control chars)
		- Therefore [^\PC] is the same as \pC 		(matches control chars)
	*/
	
	private const UNICODE_FILTER_PRINT 		= '\P{Cc}\p{Cf}\p{Cn}\p{Cs}';
	private const WHITESPACES 				= '\n\r\t';
	
	private const PATTERN_FILTER_PRINT 		= '/[^'.self::UNICODE_FILTER_PRINT.']/';
	private const PATTERN_FILTER_PRINT_N 	= '/[^'.self::UNICODE_FILTER_PRINT.'\n]/';
	private const PATTERN_FILTER_PRINT_S 	= '/[^'.self::UNICODE_FILTER_PRINT.self::WHITESPACES.']/';
	
	private const PATTERN_MATCH_PRINT_S 	= '/[\PC'.self::WHITESPACES.']/';
	
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
	
	static public function check_printable_ratio(string &$str, bool $utf8=false): ?array{
		self::normalize($str, $utf8);
		
		$strlen 		= $utf8 ? mb_strlen($str) : strlen($str);
		$non_printable 	= preg_match_all(self::PATTERN_FILTER_PRINT_S.($utf8 ? 'u' : ''), $str);
		$printable 		= preg_match_all(self::PATTERN_MATCH_PRINT_S.($utf8 ? 'u' : ''), $str);
		
		$diff = $printable - $strlen + $non_printable;
		if(!$printable || $diff){
			return [
				'all'			=> $strlen,
				'non_printable'	=> $non_printable,
				'printable'		=> $printable,
				'diff'			=> abs($diff)
			];
		}
		
		return null;
	}
	
	static public function trim(string $str, bool $allow_newlines=true, bool $utf8=true): string{
		self::normalize($str, $utf8);
		
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
	
	static public function normalize(string &$str, bool $utf8=true): void{
		if($utf8){
			$str = preg_replace('/\x{'.self::NBSP_UNICODE.'}/u', ' ', $str) ?? '';
			$str = preg_replace('/[\x{'.self::ZWSP_UNICODE.'}\x{'.self::SHY_UNICODE.'}]/u', '', $str) ?? '';
			$str = preg_replace('/\x{'.self::NBHY_UNICODE.'}/u', '-', $str) ?? '';
		}
		else{
			$str = preg_replace('/'.self::NBSP_UTF16.'|'.self::NBSP_UTF8.'|'.self::NBSP.'/', ' ', $str) ?? '';
			$str = preg_replace('/'.self::ZWSP_UTF16.'|'.self::ZWSP_UTF8.'|'.self::SHY_UTF16.'|'.self::SHY_UTF8.'|'.self::SHY.'/', '', $str) ?? '';
			$str = preg_replace('/'.self::NBHY_UTF16.'|'.self::NBHY_UTF8.'/', '-', $str) ?? '';
		}
	}
	
	static public function simplify(string $str): string{
		// https://www.w3schools.com/charsets/ref_html_entities_4.asp
		
		$html = htmlentities($str, ENT_QUOTES, self::ENC_UTF8);
		if(strpos($html, '&') === false){
			return $str;
		}
		
		return html_entity_decode(preg_replace('/&([a-z]{1,2})(?:grave|acute|circ|tilde|uml|ring|lig|cedil|slash);/i', '$1', $html), ENT_QUOTES, self::ENC_UTF8);
	}
	
	static private function strip_mb4(string $str): string{
		// https://www.compart.com/en/unicode/plane
		// https://unicode.org/emoji/charts/index.html
		
		$planes_1_3 	= '\xF0[\x90-\xBF][\x80-\xBF]{2}';
		$planes_4_15 	= '[\xF1-\xF3][\x80-\xBF]{3}';
		$plane_16 		= '\xF4[\x80-\x8F][\x80-\xBF]{2}';
		
		return preg_replace("/$planes_1_3|$planes_4_15|$plane_16/", '', $str);
	}
}