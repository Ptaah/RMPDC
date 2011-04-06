<?php
namespace choonz;
const MPD_HOST  = 'localhost';
const MPD_PORT  = '6600';
const CACHE_DIR = '/tmp/choonzcache';


if(function_exists('apc_store'))
{
    \Cache::getInstance('choonz', array(
        'type'   => 'apc'
    ));    
}
else
{
    \Cache::getInstance('choonz', array(
        'type'   => 'disk',
        'path'   => CACHE_DIR,
        'create' => true
    ));
}

function time2minutes($seconds)
{
    if($seconds > 0)
    {
        $mins = floor($seconds / 60);
        $secs = $seconds % 60;
        return str_pad($mins, 2, '0', STR_PAD_LEFT) . ':' . str_pad($secs, 2, '0', STR_PAD_LEFT);
    }
    return '00:00';
}

try
{
    $mpd = \Net_MPD::factory('Playback', MPD_HOST, MPD_PORT);
    $mpd->getStatus();
}
catch(\PEAR_Exception $e)
{
    if($e->getCode() == 102)
    {
        die('MPD not responding ! Check your connection parameters in file "' . __FILE__ . '".');
    }
}