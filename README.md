# php-str
Small bundle of methods to sanitize and format strings

## Classes
- [\Str\Str](#strstr)
  - [Sanitize UTF8](#sanitize-utf8-filter-non-valid-utf8-chars)
  - [Validate UTF8](#validate-utf8)
  - [Trim string](#trim-string)
  - [Normalize string](#normalize-string)
- [\Str\Format](#strformat)

## \Str\Str

### Sanitize UTF8 (filter non valid UTF8 chars)
```
//  Allow only spaces
$valid_utf8_str = \Str\Str::filter_utf8($str);

//  Allow only spaces and new lines (\n)
$valid_utf8_str = \Str\Str::filter_utf8($str, 'n');

//  Allow spaces and all whitespaces (\n\r\t)
$valid_utf8_str = \Str\Str::filter_utf8($str, 's');
```

### Validate UTF8
```
$is_valid_utf8 = \Str\Str::is_valid_utf8($str);
```

### Trim string
- Trims multi-lined string each line independently
- Trims multi-lined string multiple continguous line spaces (\n\n+) to maximum two (\n\n)
- Normalizes whitespaces before trimming `\Str\Str::normalize()`
```
$trimmed_str = \Str\Str::trim($str);
```

### Normalize string
- Converts different kinds of hyphen (-) to minus char (-)
- Converts different kinds of spaces like non-breaking spaces to a normal space.
```
$normalized_str = \Str\Str::normalize($str);
```

## \Str\Format
```
//  Set local conversion
\Str\Format::init('da_DK');

//  Converts a int/float to monetary string
echo \Str\Format::num(123456.89, 2); // 123.456,89

//	Converts a monetary string to int
echo \Str\Format::amount('123.456,89'); // 12345689

//  Converts Bytes to human-readable string
echo \Str\Format::datasize(1231234); // 1,17 Mb

//  Converts a XML string to a simple JSON object
$json = \Str\Format::xml_decode($xml);
```