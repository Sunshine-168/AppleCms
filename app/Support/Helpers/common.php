<?php


function theme_asset($path): string
{
    $theme = config('theme.default','default');

    return "/themes/$theme/assets/".$path;
}


function conf($val): string
{
    return config('system.settings.' . $val);
}

