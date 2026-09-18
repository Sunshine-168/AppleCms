<?php
class Crawler
{
    private $html = '';
    private $charset = 'UTF-8';
    private $lastHttpCode = 0;
    private $lastHtml = '';
    private $url = '';  

    public function __construct($charset = 'UTF-8') { $this->charset = strtoupper($charset); }

    public function fetch($url, $timeout = 30, $retries = 2)
    {
        $this->url = $url;
        $attempt = 0;
        $lastError = '';
        while ($attempt <= $retries) {
            $ch = curl_init();
            $fakeIp = mt_rand(1, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255);
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                CURLOPT_HTTPHEADER => ["X-Forwarded-For: $fakeIp"]
            ]);
            $this->html = curl_exec($ch);
            $error = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $this->lastHttpCode = $httpCode;
            $this->lastHtml = $this->html;
            if ($error === '' && $httpCode == 200) break;
            $lastError = $error ?: "HTTP状态码: $httpCode";
            $attempt++;
            if ($attempt <= $retries) usleep(500000);
        }
        if ($attempt > $retries) throw new Exception("cURL错误：{$lastError}");
        if ($this->charset !== 'UTF-8') {
            $this->html = mb_convert_encoding($this->html, 'UTF-8', $this->charset);
            $this->lastHtml = $this->html;
        }
        return $this->html;
    }

    public function getHttpCode() { return $this->lastHttpCode; }
    public function getHtml() { return $this->lastHtml; }

    public static function buildUrl($template, $vars = [])
    {
        if (empty($template)) return '';
        $r = $template;
        if (isset($vars['novel_id'])) {
            $r = str_replace('{novel_id}', $vars['novel_id'], $r);
            $r = str_replace('{novel_id/1000}', floor($vars['novel_id'] / 1000), $r);
        }
        if (isset($vars['chapter_id'])) $r = str_replace('{chapter_id}', $vars['chapter_id'], $r);
        if (isset($vars['page'])) $r = str_replace('{page}', $vars['page'], $r);
        return $r;
    }

    private function _wildcardToRegex($pattern)
    {
        $p = preg_quote($pattern, '#');
        $p = str_replace(['\\{num\\}', '\\{str\\}', '\\{blank\\}', '\\{rn\\}'], ['\d+', '[^\s]+', '\s+', '\r?\n|\r'], $p);
        return '(?:' . $p . ')';
    }

    private function _hasWildcard($s) { return (bool)preg_match('/\{num\}|\{str\}|\{blank\}|\{rn\}/', $s); }

    public function cut($start, $end, $multiple = false, $include = false, $offset = 0)
    {
        $html = $this->html;
        if ($this->_hasWildcard($start) || $this->_hasWildcard($end)) {
            $re = '#' . $this->_wildcardToRegex($start) . '(.*?)' . $this->_wildcardToRegex($end) . '#isu';
            if ($multiple) {
                preg_match_all($re, $html, $ms, PREG_SET_ORDER, $offset);
                $out = [];
                foreach ($ms as $m) $out[] = $include ? $m[0] : $m[1];
                return $out;
            } else {
                return preg_match($re, $html, $m, 0, $offset) ? ($include ? $m[0] : $m[1]) : '';
            }
        }
        if ($multiple) {
            $results = [];
            $pos = $offset;
            while (($s = mb_strpos($html, $start, $pos)) !== false) {
                $cS = $s + ($include ? 0 : mb_strlen($start));
                $e = mb_strpos($html, $end, $s + mb_strlen($start));
                if ($e === false) break;
                $cE = $include ? $e + mb_strlen($end) : $e;
                $results[] = mb_substr($html, $cS, $cE - $cS);
                $pos = $e + mb_strlen($end);
            }
            return $results;
        }
        $s = mb_strpos($html, $start, $offset);
        if ($s === false) return '';
        $cS = $s + ($include ? 0 : mb_strlen($start));
        $e = mb_strpos($html, $end, $s + mb_strlen($start));
        if ($e === false) return '';
        $cE = $include ? $e + mb_strlen($end) : $e;
        return mb_substr($html, $cS, $cE - $cS);
    }

    public function cutInArea($areaStart, $areaEnd, $start, $end, $multiple = false, $include = false, $offset = 0)
    {
        $area = $this->_getArea($areaStart, $areaEnd);
        if ($area === null) return $multiple ? [] : '';
        $tmp = new self($this->charset);
        $tmp->html = $area;
        return $tmp->cut($start, $end, $multiple, $include, $offset);
    }

    private function _getArea($start, $end)
    {
        if ($start === '' && $end === '') return $this->html;
        $s = ($start !== '') ? mb_strpos($this->html, $start) : 0;
        if ($s === false) return null;
        $from = $s + mb_strlen($start);
        $e = ($end !== '') ? mb_strpos($this->html, $end, $from) : mb_strlen($this->html);
        return ($e !== false) ? mb_substr($this->html, $from, $e - $from) : null;
    }

    public function extractIdAndTitle($aStart, $aEnd, $idStart, $idEnd, $tStart, $tEnd)
    {
        $area = $this->_getArea($aStart, $aEnd);
        if ($area === null) return [];
        $tmp = new self($this->charset);
        $tmp->html = $area;
        $ids = $tmp->cut($idStart, $idEnd, true);
        $titles = $tmp->cut($tStart, $tEnd, true);
        $res = [];
        $cnt = min(count($ids), count($titles));
        for ($i = 0; $i < $cnt; $i++) {
            if ($ids[$i] !== '' && $titles[$i] !== '') {
                $res[] = ['id' => trim($ids[$i]), 'title' => trim(strip_tags($titles[$i]))];
            }
        }
        return $res;
    }

    public function extractVolumesAndChapters($config)
    {
        $html = $this->html;
        $areaS = $config['chapter_area_start'] ?? '';
        $areaE = $config['chapter_area_end'] ?? '';
        $vS = $config['volume_title_start'] ?? '';
        $vE = $config['volume_title_end'] ?? '';
        $idS = $config['chapter_id_start'] ?? '';
        $idE = $config['chapter_id_end'] ?? '';
        $tS = $config['chapter_title_start'] ?? '';
        $tE = $config['chapter_title_end'] ?? '';

        $chapHtml = $areaS && $areaE ? ($this->_getArea($areaS, $areaE) ?? $html) : $html;
        $tmp = new self($this->charset);
        $tmp->html = $chapHtml;

        $volTitles = [];
        if ($vS && $vE) {
            $volTitles = $tmp->cut($vS, $vE, true);
        }

        if (empty($volTitles)) {
            $chaps = $this->_extractChapters($tmp, $idS, $idE, $tS, $tE);
            return ['volumes' => [['title' => '默认卷', 'chapters' => $chaps]], 'total_chapters' => count($chaps)];
        }

        $volRegex = '#' . $this->_wildcardToRegex($vS) . '(.*?)' . $this->_wildcardToRegex($vE) . '#isu';
        $parts = preg_split($volRegex, $chapHtml, -1, PREG_SPLIT_DELIM_CAPTURE);
        $volumes = [];
        $curVol = '默认卷';
        $curChaps = [];
        for ($i = 0; $i < count($parts); $i++) {
            if ($i % 2 == 1) {
                if (!empty($curChaps)) $volumes[] = ['title' => $curVol, 'chapters' => $curChaps];
                $curVol = trim(strip_tags($parts[$i]));
                $curChaps = [];
            } else {
                $subTmp = new self($this->charset);
                $subTmp->html = $parts[$i];
                $chaps = $this->_extractChapters($subTmp, $idS, $idE, $tS, $tE);
                $curChaps = array_merge($curChaps, $chaps);
            }
        }
        if (!empty($curChaps) || !empty($volumes)) $volumes[] = ['title' => $curVol, 'chapters' => $curChaps];
        $total = array_sum(array_map(function($v) { return count($v['chapters']); }, $volumes));
        return ['volumes' => $volumes, 'total_chapters' => $total];
    }

    private function _extractChapters($crawler, $idS, $idE, $tS, $tE)
    {
        if (empty($idS) || empty($idE) || empty($tS) || empty($tE)) return [];
        $ids = $crawler->cut($idS, $idE, true);
        $titles = $crawler->cut($tS, $tE, true);
        $res = [];
        $cnt = min(count($ids), count($titles));
        for ($i = 0; $i < $cnt; $i++) {
            $id = trim($ids[$i]);
            $title = trim(strip_tags($titles[$i]));
            if ($id === '' && $title === '') continue;
            $pureId = '';
            if (preg_match('/(\d+)\.html/', $id, $m)) $pureId = $m[1];
            elseif (is_numeric($id)) $pureId = $id;
            $res[] = ['id' => $pureId ?: $id, 'title' => $title, 'url' => '']; 
        }
        return $res;
    }
}