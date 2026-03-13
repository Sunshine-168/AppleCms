<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class CmsFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function postOnlyRules(array $rules): array
    {
        return $this->isMethod('post') ? $rules : [];
    }

    protected function transMessage(string $key, string $fallback = ''): string
    {
        $translated = __($key);

        return $translated === $key && $fallback !== '' ? $fallback : $translated;
    }

    protected function sanitizeMap(): array
    {
        return [];
    }

    protected function prepareForValidation(): void
    {
        $map = $this->sanitizeMap();
        if (empty($map)) {
            return;
        }

        $data = $this->all();
        foreach ($map as $field => $length) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            $data[$field] = $this->sanitizeValue($data[$field], $length);
        }

        $this->merge($data);
    }

    protected function sanitizeValue(mixed $value, int $length): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($item) => $this->sanitizeValue($item, $length), $value);
        }

        if ($value === null || is_bool($value)) {
            return $value;
        }

        if (!is_scalar($value)) {
            return $value;
        }

        $value = (string) $value;
        if (function_exists('cms_filter_xss')) {
            $value = mac_filter_xss($value);
        }

        return mb_substr($value, 0, $length);
    }
}
