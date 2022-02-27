<?php

namespace Str;

class Format {
	static public function xml_decode(string $xml): array{
		$xml 	= simplexml_load_string($xml);
		$json 	= json_encode($xml);
		
		return json_decode($json, true);
	}
}