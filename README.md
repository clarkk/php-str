# php-str
String UTF8 filtering and normalizing

## Filter non valid UTF8 chars
### Allows only spaces
```
$valid_utf8_str = \Str\Str::filter_utf8($str);
```

### Allows only spaces and new lines (\n)
```
$valid_utf8_str = \Str\Str::filter_utf8($str, 'n');
```

### Allows spaces and all whitespaces (\n\r\t)
```
$valid_utf8_str = \Str\Str::filter_utf8($str, 's');
```

## Validates UTF8
```
$is_valid_utf8 = \Str\Str::is_valid_utf8($str);
```

## Trims string
- Trims multilined string each line independently
- Trims multilined string multiple continguous line spaces (\n\n+) to maximum two (\n\n)
- Normalizes whitespaces before trimming `\Str\Str::normalize()`
```
$trimmed_str = \Str\Str::trim($str);
```

## Normalize string
- Converts different kinds of hyphen (-) to minus char (-)
- Converts different kinds of spaces like non-breaking spaces to a normal space.
```
$normalized_str = \Str\Str::normalize($str);
```
