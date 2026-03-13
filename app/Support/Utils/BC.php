<?php

namespace Utils;


use InvalidArgumentException;

class BC
{
    private string $value;
    private int $scale;

    public function __construct($initialValue = '0', int $scale = 2) {
        $this->value = (string)$initialValue;
        $this->scale = $scale;
    }

    // 加法
    public function add(...$numbers): self {
        foreach ($numbers as $num) {
            $this->value = bcadd($this->value, (string)$num, $this->scale + 2);
        }
        return $this;
    }

    // 减法
    public function sub(...$numbers): self {
        foreach ($numbers as $num) {
            $this->value = bcsub($this->value, (string)$num, $this->scale + 2);
        }
        return $this;
    }

    // 乘法
    public function mul(...$numbers): self {
        foreach ($numbers as $num) {
            $this->value = bcmul($this->value, (string)$num, $this->scale + 2);
        }
        return $this;
    }

    // 除法（自动检查除数不为0）
    public function div(...$numbers): self {
        foreach ($numbers as $num) {
            if (bccomp((string)$num, '0', $this->scale) === 0) {
                throw new InvalidArgumentException("除数不能为0");
            }
            $this->value = bcdiv($this->value, (string)$num, $this->scale + 2);
        }
        return $this;
    }

    // 获取最终结果（自动修正精度）
    public function result(): string {
        return bcdiv($this->value, '1', $this->scale);
    }

    // 静态快捷方法
    public static function calc($initialValue = '0', int $scale = 2): self {
        return new self($initialValue, $scale);
    }
}
