<?php

namespace App\Support;

class Captcha
{
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
}
