<?php
/**
 * @author      EtMDB Devs (developers@etmdb.com)
 * @copyright   15/08/2017
 * @license     https://www.etmdb.com/license
 * @version     1.1.0
 */

namespace EtMDB\TelegramBot;


class DataHandler
{
    /**
     * @var
     */
    private $jsonResponse;

    /**
     * @param $string
     * @return bool
     */
    public function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     *  Decode JSON to array
     * @param $value
     * @return bool
     */
    public static function convertToJson($value)
    {
        $jsonResponse = json_decode($value, true);
        return $jsonResponse;
    }
}