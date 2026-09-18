<?php

namespace App\Support;

class Captcha
{
    private const WIDTH = 160;

    private const HEIGHT = 48;

    /** @var array<string, list<string>> 5×7 pixel maps (no SVG <text>) */
    private const GLYPHS = [
        '0' => ['01110', '10001', '10001', '10001', '10001', '10001', '01110'],
        '1' => ['00100', '01100', '00100', '00100', '00100', '00100', '01110'],
        '2' => ['01110', '10001', '00001', '00010', '00100', '01000', '11111'],
        '3' => ['11110', '00001', '00001', '01110', '00001', '00001', '11110'],
        '4' => ['10010', '10010', '10010', '11111', '00010', '00010', '00010'],
        '5' => ['11111', '10000', '11110', '00001', '00001', '10001', '01110'],
        '6' => ['01110', '10000', '11110', '10001', '10001', '10001', '01110'],
        '7' => ['11111', '00001', '00010', '00100', '01000', '01000', '01000'],
        '8' => ['01110', '10001', '10001', '01110', '10001', '10001', '01110'],
        '9' => ['01110', '10001', '10001', '01111', '00001', '00001', '01110'],
        '+' => ['00100', '00100', '00100', '11111', '00100', '00100', '00100'],
        '-' => ['00000', '00000', '00000', '11111', '00000', '00000', '00000'],
        '=' => ['00000', '00000', '11111', '00000', '11111', '00000', '00000'],
        '?' => ['01110', '10001', '00001', '00010', '00100', '00000', '00100'],
    ];

    public static function generate(): array
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);

        if (random_int(0, 1) === 1) {
            $question = "{$a} + {$b} = ?";
            $answer = $a + $b;
        } else {
            if ($a < $b) {
                [$a, $b] = [$b, $a];
            }
            $question = "{$a} - {$b} = ?";
            $answer = $a - $b;
        }

        session(['captcha' => $answer]);

        return [
            'question' => $question,
            'a' => $a,
            'b' => $b,
        ];
    }

    public static function check(?string $answer): bool
    {
        $expect = session('captcha');
        session()->forget('captcha');

        if ($expect === null || $answer === null || trim($answer) === '') {
            return false;
        }

        return (int) $expect === (int) $answer;
    }

    public static function response(): \Illuminate\Http\Response
    {
        $cap = self::generate();
        $question = (string) ($cap['question'] ?? '');
        $headers = ['Cache-Control' => 'no-store'];

        if (function_exists('imagecreatetruecolor') && function_exists('imagepng')) {
            $png = self::png($question);
            if ($png !== '') {
                return response($png, 200, $headers + ['Content-Type' => 'image/png']);
            }
        }

        return response(self::svg($question), 200, $headers + ['Content-Type' => 'image/svg+xml']);
    }

    private static function png(string $question): string
    {
        $im = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        if ($im === false) {
            return '';
        }

        $bg = imagecolorallocate($im, 0xec, 0xfb, 0xf4);
        $ink = imagecolorallocate($im, 0x12, 0x7a, 0x4a);
        $noise = imagecolorallocate($im, 0x9d, 0xd2, 0xb8);
        $curve = imagecolorallocate($im, 0x7e, 0xc9, 0xa4);
        if ($bg === false || $ink === false || $noise === false || $curve === false) {
            imagedestroy($im);

            return '';
        }

        imagefilledrectangle($im, 0, 0, self::WIDTH - 1, self::HEIGHT - 1, $bg);

        $dots = random_int(80, 120);
        for ($i = 0; $i < $dots; $i++) {
            imagesetpixel($im, random_int(0, self::WIDTH - 1), random_int(0, self::HEIGHT - 1), $noise);
        }

        $lines = random_int(2, 3);
        imagesetthickness($im, 1);
        for ($i = 0; $i < $lines; $i++) {
            imageline(
                $im,
                random_int(0, 40),
                random_int(4, self::HEIGHT - 5),
                random_int(self::WIDTH - 40, self::WIDTH - 1),
                random_int(4, self::HEIGHT - 5),
                $curve
            );
        }

        for ($i = 0; $i < 2; $i++) {
            self::drawQuadratic(
                $im,
                $curve,
                random_int(2, 20),
                random_int(6, self::HEIGHT - 7),
                random_int(50, 110),
                random_int(0, self::HEIGHT - 1),
                random_int(140, self::WIDTH - 2),
                random_int(6, self::HEIGHT - 7)
            );
        }

        $font = 5;
        $cw = function_exists('imagefontwidth') ? imagefontwidth($font) : 9;
        $chh = function_exists('imagefontheight') ? imagefontheight($font) : 15;
        $x = 10 + random_int(0, 6);
        $baseY = (int) max(2, (self::HEIGHT - $chh) / 2);

        foreach (str_split($question) as $ch) {
            if ($ch !== ' ') {
                imagestring($im, $font, $x + random_int(-2, 3), $baseY + random_int(-4, 4), $ch, $ink);
            }
            $x += $cw + random_int(1, 4);
        }

        ob_start();
        imagepng($im);
        $data = (string) ob_get_clean();
        imagedestroy($im);

        return $data;
    }

    private static function drawQuadratic(mixed $im, int $color, int $x0, int $y0, int $x1, int $y1, int $x2, int $y2): void
    {
        $prevX = $x0;
        $prevY = $y0;
        $steps = 28;
        for ($i = 1; $i <= $steps; $i++) {
            $t = $i / $steps;
            $mt = 1 - $t;
            $x = (int) round($mt * $mt * $x0 + 2 * $mt * $t * $x1 + $t * $t * $x2);
            $y = (int) round($mt * $mt * $y0 + 2 * $mt * $t * $y1 + $t * $t * $y2);
            imageline($im, $prevX, $prevY, $x, $y, $color);
            $prevX = $x;
            $prevY = $y;
        }
    }

    private static function svg(string $question): string
    {
        $parts = [
            '<svg xmlns="http://www.w3.org/2000/svg" width="'.self::WIDTH.'" height="'.self::HEIGHT.'" viewBox="0 0 '.self::WIDTH.' '.self::HEIGHT.'">',
            '<rect width="'.self::WIDTH.'" height="'.self::HEIGHT.'" fill="#ecfbf4"/>',
        ];

        $circles = random_int(40, 70);
        for ($i = 0; $i < $circles; $i++) {
            $parts[] = '<circle cx="'.random_int(1, self::WIDTH - 1).'" cy="'.random_int(1, self::HEIGHT - 1).'" r="'.random_int(1, 2).'" fill="#9dd2b8"/>';
        }

        $lines = random_int(2, 3);
        for ($i = 0; $i < $lines; $i++) {
            $parts[] = '<line x1="'.random_int(0, 30).'" y1="'.random_int(4, 44).'" x2="'.random_int(130, 159).'" y2="'.random_int(4, 44).'" stroke="#7ec9a4" stroke-width="1"/>';
        }

        for ($i = 0; $i < 2; $i++) {
            $parts[] = '<path d="M '.random_int(2, 18).' '.random_int(8, 40)
                .' Q '.random_int(50, 110).' '.random_int(0, 47)
                .' '.random_int(140, 158).' '.random_int(8, 40)
                .'" fill="none" stroke="#7ec9a4" stroke-width="1"/>';
        }

        $cell = 2;
        $x = 12 + random_int(0, 4);
        $baseY = 16 + random_int(-2, 2);

        foreach (str_split($question) as $ch) {
            if ($ch === ' ') {
                $x += 8;
                continue;
            }
            $glyph = self::GLYPHS[$ch] ?? null;
            if ($glyph === null) {
                $x += 10;
                continue;
            }
            $gy = $baseY + random_int(-3, 3);
            $gx = $x + random_int(-1, 1);
            foreach ($glyph as $row => $bits) {
                $len = strlen($bits);
                for ($col = 0; $col < $len; $col++) {
                    if ($bits[$col] !== '1') {
                        continue;
                    }
                    $parts[] = '<rect x="'.($gx + $col * $cell).'" y="'.($gy + $row * $cell).'" width="'.$cell.'" height="'.$cell.'" fill="#127a4a"/>';
                }
            }
            $x += 5 * $cell + 4 + random_int(0, 2);
        }

        $parts[] = '</svg>';

        return implode('', $parts);
    }
}
