--TEST--
JIT CMP: 010 Range inference must not underflow at PHP_INT_MIN
--EXTENSIONS--
opcache
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.file_update_protection=0
opcache.jit=tracing
opcache.jit_buffer_size=16M
opcache.jit_hot_loop=1
--FILE--
<?php
function test($value) {
    $result = 0;
    for ($i = 0; $i < 10; $i++) {
        if (PHP_INT_MIN <= $value) {
            $result++;
        }
    }
    return $result;
}
var_dump(test(false));
?>
--EXPECT--
int(0)
