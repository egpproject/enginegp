<?php
if(!DEFINED('EGP'))
    exit(header('Refresh: 0; URL=http://'.$_SERVER['SERVER_NAME'].'/404'));

// Подключение filp/whoops
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();
// логи в файл
$loggingInFile = new \Whoops\Handler\PlainTextHandler();
$loggingInFile->loggerOnly(true);
$loggingInFile->setLogger((new \Monolog\Logger('EngineGP', [(new \Monolog\Handler\StreamHandler(ROOT . '/logs/cron.log'))->setFormatter((new \Monolog\Formatter\LineFormatter(null, null, true)))])));
$whoops->pushHandler($loggingInFile);

// Подгрузка трейта
if(!file_exists(CRON.$task.'.php'))
    exit('Invalid cron method' . PHP_EOL);

$device = '!mobile';
$user = array('id' => 0, 'group' => 'admin');

class cron
{
    public static $seping = 5;
    public static $process = array(
        'cs' => 'hlds_',
        'cssold' => 'srcds_i686',
        'css' => 'srcds_',
        'csgo' => 'srcds_',
        'samp' => 'samp',
        'crmp' => 'samp',
        'mta' => 'mta',
        'mc' => 'java'
    );

    public static $quakestat = array(
        'cs' => 'a2s',
        'cssold' => 'a2s',
        'css' => 'a2s',
        'csgo' => 'a2s',
        'mta' => 'eye'
    );

    public static $admins_file = array(
        'cs' => 'cstrike/addons/amxmodx/configs/users.ini',
        'cssold' => 'cstrike/addons/sourcemod/configs/admins_simple.ini',
        'css' => 'cstrike/addons/sourcemod/configs/admins_simple.ini',
        'csgo' => 'csgo/addons/sourcemod/configs/admins_simple.ini'
    );

    public static function thread($num, $type, $aData)
    {
        $threads = array();

        for($n = 1; $n <= $num; $n+=1)
        {
            $data = '';
            $i = 0;
            foreach($aData as $key => $val)
            {
                if($i == cron::$seping)
                    break;
                    $data .= $val.' ';
                    unset($aData[$key]);
                    $i+=1;
            }

            $aData = array_values($aData);

            $threads[] = $type.' '.substr($data, 0, -1);
        }

        return $threads;
    }
}

include(CRON.$task.'.php');

new $task();
?>