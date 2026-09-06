--TEST--
Sorting packed integer arrays during by-reference foreach preserves iterator positions
--FILE--
<?php
function check($actual, $expected) {
    if ($actual !== $expected) {
        throw new Exception(var_export([$actual, $expected], true));
    }
}

foreach (['sort', 'rsort'] as $sort) {
    foreach ([false, true] as $removeCurrent) {
        $values = [9, 3, 1, 2];
        $visited = [];
        $first = true;
        foreach ($values as $key => &$value) {
            $visited[] = [$key, $value];
            if ($first) {
                $first = false;
                if ($removeCurrent) {
                    // Remove the referenced element while keeping the iterator active.
                    array_shift($values);
                    foreach (array_keys($values) as $index) {
                        check(ReflectionReference::fromArrayElement($values, $index), null);
                    }
                }
                $sort($values);
            }
        }
        unset($value);
        if ($removeCurrent) {
            $expected = $sort === 'sort' ? [1, 2, 3] : [3, 2, 1];
            check($visited, [[0, 9], [0, $expected[0]], [1, 2], [2, $expected[2]]]);
        } else {
            $expected = $sort === 'sort' ? [1, 2, 3, 9] : [9, 3, 2, 1];
            check($visited, [[0, 9], [1, $expected[1]], [2, $expected[2]], [3, $expected[3]]]);
        }
        check($values, $expected);
    }
    echo "$sort: OK\n";
}
?>
--EXPECT--
sort: OK
rsort: OK
