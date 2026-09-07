--TEST--
Packed zval sorting keeps its buffer alive when conversions modify the sorted variable
--FILE--
<?php
function check($actual, $expected) {
    if ($actual !== $expected) {
        throw new Exception(var_export([$actual, $expected], true));
    }
}
class ReentrantString {
    public static ?Closure $action = null;
    public static int $destroyed = 0;
    public function __construct(public int $value) {}
    public function __toString(): string {
        $action = self::$action;
        self::$action = null;
        if ($action) $action();
        return (string) $this->value;
    }
    public function __destruct() { self::$destroyed++; }
}
function makeValues() {
    $values = [];
    for ($i = 64; $i > 0; $i--) $values[] = new ReentrantString($i);
    return $values;
}

foreach (['sort', 'rsort'] as $sort) {
    $values = makeValues();
    $original = $values;
    ReentrantString::$action = function () use (&$values) { $values[] = 'appended'; };
    check($sort($values, SORT_STRING), true);
    check($values, [...$original, 'appended']);
    unset($values, $original);

    $values = makeValues();
    ReentrantString::$action = function () use (&$values) { $values = ['replacement']; };
    check($sort($values, SORT_STRING), true);
    check($values, ['replacement']);

    $values = makeValues();
    $weak = WeakReference::create($values[0]);
    ReentrantString::$destroyed = 0;
    ReentrantString::$action = function () use (&$values) { $values = null; gc_collect_cycles(); };
    check($sort($values, SORT_STRING), true);
    check($values, null);
    check($weak->get(), null);
    check(ReentrantString::$destroyed, 64);

    $values = makeValues();
    $expected = $values;
    $expected[0] = 'changed';
    $referenceSort = $sort === 'sort' ? 'asort' : 'arsort';
    $referenceSort($expected, SORT_STRING);
    ReentrantString::$action = function () use (&$values, $sort) {
        $values[0] = 'changed';
        $sort($values, SORT_STRING);
    };
    check($sort($values, SORT_STRING), true);
    check($values, array_values($expected));
    unset($values, $expected);

    $values = makeValues();
    $ids = array_map(spl_object_id(...), $values);
    ReentrantString::$action = function () { throw new RuntimeException('conversion'); };
    try {
        $sort($values, SORT_STRING);
        throw new Exception('Missing exception');
    } catch (RuntimeException $e) {
        check($e->getMessage(), 'conversion');
    }
    $resultIds = array_map(spl_object_id(...), $values);
    sort($ids);
    sort($resultIds);
    check($resultIds, $ids);
    check(array_is_list($values), true);
    unset($values);

    $values = array_fill(0, 64, []);
    $first = true;
    set_error_handler(function () use (&$values, &$first) {
        if ($first) {
            $first = false;
            $values = ['error handler'];
            gc_collect_cycles();
        }
        return true;
    });
    try {
        check($sort($values, SORT_STRING), true);
    } finally {
        restore_error_handler();
    }
    check($first, false);
    check($values, ['error handler']);
    echo "$sort: OK\n";
}
?>
--EXPECT--
sort: OK
rsort: OK
