<?php

$en = include __DIR__ . '/../resources/lang/en/admin.php';
$ja = include __DIR__ . '/../resources/lang/ja/admin.php';

function flattenLocale(array $values, string $prefix = ''): array
{
    $result = [];

    foreach ($values as $key => $value) {
        $path = $prefix === '' ? $key : $prefix . '.' . $key;

        if (is_array($value)) {
            $result += flattenLocale($value, $path);
        } else {
            $result[$path] = $value;
        }
    }

    return $result;
}

$flatEn = flattenLocale($en);
$flatJa = flattenLocale($ja);
$missing = array_diff_key($flatEn, $flatJa);
$same = [];

foreach ($flatEn as $key => $value) {
    if (array_key_exists($key, $flatJa) && $flatJa[$key] === $value) {
        $same[$key] = $value;
    }
}

$uiSame = array_filter(
    $same,
    static fn (string $key): bool => str_starts_with($key, 'ui.'),
    ARRAY_FILTER_USE_KEY
);

echo sprintf(
    "en=%d ja=%d missing=%d same=%d ui_same=%d\n",
    count($flatEn),
    count($flatJa),
    count($missing),
    count($same),
    count($uiSame)
);

file_put_contents(
    __DIR__ . '/locale_audit.json',
    json_encode(
        ['missing' => $missing, 'ui_same' => $uiSame],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    )
);
