<?php

foreach (glob(__DIR__.'/Helpers/*.php') as $file)
{
    require_once $file;
}
