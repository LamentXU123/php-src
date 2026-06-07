--TEST--
IntlNumberRangeFormatter::format() reports unconstructed objects
--EXTENSIONS--
intl
--SKIPIF--
<?php
if (!class_exists('IntlNumberRangeFormatter')) {
    die('skip IntlNumberRangeFormatter not available');
}
?>
--FILE--
<?php

$formatter = (new ReflectionClass(IntlNumberRangeFormatter::class))->newInstanceWithoutConstructor();

try {
    $formatter->format(1, 2);
} catch (Error $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

?>
--EXPECT--
Error: Found unconstructed IntlNumberRangeFormatter
