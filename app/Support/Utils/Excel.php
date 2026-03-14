<?php
namespace App\Support\Utils;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use function App\Utils\mkdir;

class Excel
{
    /**
     * 获取表头
     */
    public static function setHeader(string $type): array
    {
        return config("excel.{$type}", []);
    }

    /**
     * 导入 Excel
     */
    public static function importExcel(UploadedFile $file): bool|array|string
    {
        try {
            $validator = Validator::make(
                ['file' => $file],
                ['file' => 'required|file|mimes:xls,xlsx,csv|max:51200']
            );

            if ($validator->fails()) {
                return $validator->errors()->first();
            }

            $dir = public_path('upload/topic');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $fileName = date('YmdHis') . '_' . uniqid('', true) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $fileName);
            $filePath = $dir . DIRECTORY_SEPARATOR . $fileName;

            $readerMap = [
                'xlsx' => 'Xlsx',
                'csv'  => 'Csv',
                'xls'  => 'Xls',
            ];
            $type = $readerMap[$file->extension()] ?? 'Xls';

            $reader = IOFactory::createReader($type);
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);

            $spreadsheet    = $reader->load($filePath);
            $data           = $spreadsheet->getActiveSheet()->toArray();

            if (count($data) <= 1) return false;

            array_shift($data); // 移除表头
            return $data;

        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    /**
     * 创建 CSV 文件
     */
    public static function createCsvFile(string $fileName, string $type): bool|string
    {
        $header = array_values(self::setHeader($type));
        $dir    = public_path('storage/' . $type);

        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filePath = $dir . '/' . $fileName . '.csv';

        // 中文兼容 Excel
        $bom        = "\xEF\xBB\xBF";
        $content    = $bom . implode(',', $header) . "\r\n";

        return file_put_contents($filePath, $content) !== false ? str_replace(public_path(), '', $filePath) : false;
    }

    /**
     * 创建 Excel 文件
     */
    public static function createExcelFile(string $fileName, string $type): bool|string
    {
        $header = array_values(self::setHeader($type));
        $dir    = public_path('storage/' . $type);

        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filePath = $dir . '/' . $fileName . '.xlsx';

        $spreadsheet    = new Spreadsheet();
        $sheet          = $spreadsheet->getActiveSheet();

        // 使用 fromArray 批量写入表头
        $sheet->fromArray($header, null, 'A1');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($filePath);

        unset($spreadsheet, $writer);

        return file_exists($filePath) ? str_replace(public_path(), '', $filePath) : false;
    }

    /**
     * 追加 CSV 数据
     */
    public static function formatDataAppend(array $data, string $type, string $fileName): bool
    {
        $header = array_keys(self::setHeader($type));
        $dir    = dirname($fileName);

        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $row = [];

        foreach ($header as $key)
        {
            $row[] = isset($data[$key]) ? '"' . $data[$key] . "\t" . '"' : '""';
        }

        $appendStr = implode(',', $row) . "\r\n";

        // 中文兼容
        $appendStr = mb_convert_encoding($appendStr, 'UTF-8', 'auto');

        return (bool)file_put_contents($fileName, $appendStr, FILE_APPEND);
    }
}
