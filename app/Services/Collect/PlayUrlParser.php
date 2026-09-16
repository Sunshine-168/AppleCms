<?php

namespace App\Services\Collect;

class PlayUrlParser
{
    /**
     * @return list<array{name:string,episodes:list<array{name:string,num:int,url:string}>}>
     */
    public function parse(string $from, string $url): array
    {
        $froms = $from === '' ? [] : explode('$$$', $from);
        $urls = $url === '' ? [] : explode('$$$', $url);
        $groups = [];
        foreach ($froms as $i => $name) {
            $name = trim($name);
            if ($name === '') {
                continue;
            }
            $groups[] = [
                'name' => $name,
                'episodes' => $this->parseEpisodes((string) ($urls[$i] ?? '')),
            ];
        }

        return $groups;
    }

    /**
     * @return list<array{name:string,num:int,url:string}>
     */
    public function parseEpisodes(string $raw): array
    {
        $raw = str_replace('||', '//', $raw);
        $items = [];
        $num = 1;
        foreach (explode('#', $raw) as $piece) {
            $piece = trim($piece);
            if ($piece === '') {
                continue;
            }
            $parts = explode('$', $piece);
            if (count($parts) > 1) {
                $name = trim((string) array_shift($parts));
                $playUrl = trim(implode('$', $parts));
            } else {
                $name = '第'.$num.'集';
                $playUrl = $piece;
            }
            if ($playUrl === '') {
                continue;
            }
            $items[] = [
                'name' => $name !== '' ? $name : ('第'.$num.'集'),
                'num' => $num,
                'url' => $playUrl,
            ];
            $num++;
        }

        return $items;
    }
}
