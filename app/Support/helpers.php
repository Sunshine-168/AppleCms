<?php

foreach (glob(__DIR__.'/Helpers/*.php') as $file)
{
    require_once $file;
}

if (! function_exists('admin_t')) {
    function admin_t(string $key, array $replace = []): string
    {
        $line = trans('admin.'.$key, $replace);

        return is_string($line) ? $line : $key;
    }
}

if (! function_exists('admin_t_key')) {
    function admin_t_key(string $raw, array $replace = []): string
    {
        $raw = trim($raw);
        if ($raw === '' || ! preg_match('/^[a-z][a-z0-9_]*(?:\.[a-z][a-z0-9_]*)+$/i', $raw)) {
            return $raw;
        }
        $line = admin_t($raw, $replace);
        if ($line === $raw || $line === 'admin.'.$raw) {
            return $raw;
        }

        return $line;
    }
}

if (! function_exists('admin_localize_extra_page')) {
    /**
     * @param  array<string, mixed>  $page
     * @return array<string, mixed>
     */
    function admin_localize_extra_page(array $page): array
    {
        foreach (['title', 'hint'] as $k) {
            if (isset($page[$k]) && is_string($page[$k])) {
                $page[$k] = admin_t_key($page[$k]);
            }
        }
        $fields = is_array($page['fields'] ?? null) ? $page['fields'] : [];
        foreach ($fields as $i => $field) {
            if (! is_array($field)) {
                continue;
            }
            foreach (['label', 'hint', 'placeholder'] as $k) {
                if (isset($field[$k]) && is_string($field[$k])) {
                    $field[$k] = admin_t_key($field[$k]);
                }
            }
            if (isset($field['options']) && is_array($field['options'])) {
                foreach ($field['options'] as $val => $lab) {
                    if (is_string($lab)) {
                        $field['options'][$val] = admin_t_key($lab);
                    }
                }
            }
            $fields[$i] = $field;
        }
        $page['fields'] = $fields;

        return $page;
    }
}
