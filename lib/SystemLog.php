<?php
/**
 * @author      EtMDB Devs (developers@etmdb.com)
 * @copyright   15/08/2017
 * @license     https://www.etmdb.com/license
 * @version     1.1.0
 */

namespace EtMDB\TelegramBot;


class SystemLog
{
    /**
     * @param $logMessage
     */
    public static function addLog($logMessage)
    {
        date_default_timezone_set('Europe/Helsinki');

        error_log(date("Y-m-d H:i:s") . ' message ' . $logMessage . PHP_EOL,
            3,
            dirname(__DIR__) . '/log/errors.log');
    }
}