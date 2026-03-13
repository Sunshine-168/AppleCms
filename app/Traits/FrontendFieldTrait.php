<?php
namespace App\Traits;

/**
 * 格式化数据
 */
trait FrontendFieldTrait
{
    // 数据库字段 → 前端字段映射
    protected array $fieldMap = [];

    // 默认隐藏字段
    protected array $hiddenFields = [];

    // 默认需要格式化的时间字段
    protected array $timeFields = [];

    // 链式隐藏字段
    protected array $chainHide = [];

    // 链式时间字段
    protected array $chainTimeFields = [];

    // 默认时间格式
    protected string $defaultTimeFormat = 'Y-m-d H:i:s';

    /**
     * 链式隐藏字段
     */
    public function hide(array $fields): static
    {
        $this->chainHide = array_merge($this->chainHide, $fields);
        return $this;
    }

    /**
     * 链式控制时间字段格式化
     * 可以指定时间格式
     */
    public function formatTime(array $fields, string $format = ''): static
    {
        $this->chainTimeFields = array_merge($this->chainTimeFields, $fields);

        if ($format)
        {
            $this->defaultTimeFormat = $format;
        }

        return $this;
    }

    /**
     * 转成前端数组
     */
    public function toFrontendArray(): array
    {
        $data   = $this->toArray();
        $result = [];

        $hidden     = array_merge($this->hiddenFields, $this->chainHide);
        $timeFields = array_merge($this->timeFields, $this->chainTimeFields);

        foreach ($this->fieldMap as $dbField => $frontendField)
        {
            if (in_array($dbField, $hidden))
            {
                continue;
            }

            if (isset($data[$dbField]))
            {
                if (in_array($dbField, $timeFields) && is_numeric($data[$dbField]))
                {
                    $result[$frontendField] = date($this->defaultTimeFormat, $data[$dbField]);
                } else
                {
                    $result[$frontendField] = $data[$dbField];
                }

            } else
            {
                $result[$frontendField] = null;
            }
        }

        // 清空链式字段，避免影响下次调用
        $this->chainHide            = [];
        $this->chainTimeFields      = [];
        $this->defaultTimeFormat    = 'Y-m-d H:i:s';

        return $result;
    }

    /**
     * 转成 JSON
     */
    public function toFrontendJson(): string
    {
        return json_encode($this->toFrontendArray(), JSON_UNESCAPED_UNICODE);
    }
}
