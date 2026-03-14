<?php


function theme_asset($path)
{
    $theme = config('theme.default','default');

    return "/themes/$theme/assets/".$path;
}
