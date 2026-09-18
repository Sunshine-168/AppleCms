<?php

namespace Plugins\CjRule\Support;

/** 简易 CSS → XPath（后代、标签、#id、.class、[attr]；复杂选择器请用 xpath: 前缀） */
class CssSelector
{
    public static function toXPath(string $selector, bool $relative = false): string
    {
        $selector = trim($selector);
        if ($selector === '') {
            throw new \InvalidArgumentException('选择器为空');
        }
        if (str_starts_with(strtolower($selector), 'xpath:')) {
            return trim(substr($selector, 6));
        }

        $parts = preg_split('/\s+/', $selector) ?: [];
        $xpath = $relative ? '.' : '';
        foreach ($parts as $part) {
            if ($part === '>' || $part === '') {
                continue;
            }
            $xpath .= '//'.self::simple($part);
        }

        return $xpath !== '' && $xpath !== '.' ? $xpath : ($relative ? './/*' : '//*');
    }

    protected static function simple(string $part): string
    {
        $tag = '*';
        $id = null;
        $classes = [];
        $attrs = [];

        if (preg_match('/^([A-Za-z][\w-]*)/', $part, $m)) {
            $tag = strtolower($m[1]);
            $part = substr($part, strlen($m[1]));
        } elseif (str_starts_with($part, '*')) {
            $part = substr($part, 1);
        }

        if (preg_match('/^#([\w-]+)/', $part, $m)) {
            $id = $m[1];
            $part = substr($part, strlen($m[0]));
        }

        while (preg_match('/^\.([\w-]+)/', $part, $m)) {
            $classes[] = $m[1];
            $part = substr($part, strlen($m[0]));
        }

        while (preg_match('/^\[([^\]]+)\]/', $part, $m)) {
            $attrs[] = $m[1];
            $part = substr($part, strlen($m[0]));
        }

        if ($part !== '') {
            throw new \InvalidArgumentException('不支持的选择器，请改用 xpath: 前缀。片段：'.$part);
        }

        $pred = [];
        if ($id) {
            $pred[] = '@id="'.self::escape($id).'"';
        }
        foreach ($classes as $class) {
            $pred[] = "contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')";
        }
        foreach ($attrs as $attr) {
            if (preg_match('/^([\w-]+)\s*=\s*[\'"]?([^\'"]*)[\'"]?$/', $attr, $m)) {
                $pred[] = '@'.$m[1].'="'.self::escape($m[2]).'"';
            } else {
                $pred[] = '@'.trim($attr);
            }
        }

        return $tag.($pred !== [] ? '['.implode(' and ', $pred).']' : '');
    }

    protected static function escape(string $value): string
    {
        return str_replace('"', '&quot;', $value);
    }
}
