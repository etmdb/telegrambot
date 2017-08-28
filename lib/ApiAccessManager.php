<?php
/**
 * @author      EtMDB Devs (developers@etmdb.com)
 * @copyright   15/08/2017
 * @license     https://www.etmdb.com/license
 * @version     1.1.0
 */

namespace EtMDB\TelegramBot;


class ApiAccessManager
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * ApiAccessManager constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @var array
     */
    protected $postFields = [];

    /**
     * @return bool|mixed
     */
    public function getAccessToken()
    {
        if ($this->checkTokenValidity()) {
            SystemLog::addLog('Access Token is valid: ' . $this->getAccessTokenFromFile());
            return $this->getAccessTokenFromFile();
        } else {
            //Access token has expired we have to create a new one
            SystemLog::addLog('Access Token is not valid.');

            if ($this->getAccessTokenFromFile() === '' && $this->getRefreshTokenFromFile() === '') {
                //We do not have previously generated access token and refresh token we have to create a new one

                $curlResponse = $this->curlRequestHandler('NEW_TOKEN');
                SystemLog::addLog('Creating a new access token. ' . $curlResponse);

                if ($curlResponse === null) {
                    return false;
                }
            } else {
                //We have previously generated access token and refresh token... We can use the refresh token to make new one

                $curlResponse = $this->curlRequestHandler('REFRESH_TOKEN');
                SystemLog::addLog('Using previous refresh token. ' . $curlResponse);

                if ($curlResponse === null) {
                    return false;
                }
            }

            $dataHandler = new DataHandler();
            $tokenData = DataHandler::convertToJson($curlResponse);
            if (!$dataHandler->isJson($tokenData)) {
                return false;
            } else {
                SystemLog::addLog('Token data: ' . json_encode($tokenData));
                $this->writeAccessTokenToFile($tokenData['access_token'], $tokenData['refresh_token']);
                return $tokenData['access_token'];
            }
        }
    }

    /**
     * @return mixed |null
     */
    private function getAccessTokenFromFile()
    {
        $filename = 'conf/.accesstoken';

        if (!file_exists($filename) || !is_readable($filename)) {
            SystemLog::addLog('File doesn\'t exist or not readable for existing access token : ' . $filename);
            return null;
        } else {
            #TODO Use php apc_cache rather than file
            // var_dump(apc_fetch('tokens'));

            $accessTokenStoreFile = fopen($filename, "r");
            $accessT0k3n = fread($accessTokenStoreFile, 500000);
            SystemLog::addLog('Tokens : ' . $accessT0k3n);
            list($accTok, $refTok) = explode(":", $accessT0k3n);
            fclose($accessTokenStoreFile);

            return $accTok;
        }
    }

    /**
     * @return mixed| null
     */
    private function getRefreshTokenFromFile()
    {
        $filename = 'conf/.accesstoken';

        if (!file_exists($filename) || !is_readable($filename)) {
            SystemLog::addLog('File doesn\'t exist or not readable for refresh token: ' . $filename);
            return null;
        } else {
            #TODO Use php apc_cache rather than file
            // var_dump(apc_fetch('tokens'));

            $accessTokenStoreFile = fopen($filename, "r");
            $accessT0k3n = fread($accessTokenStoreFile, 500000);
            list($accTok, $refTok) = explode(":", $accessT0k3n);
            fclose($accessTokenStoreFile);

            return $refTok;
        }
    }

    /**
     * @param $accessTokenToWrite
     * @param $refreshTokenToWrite
     * @return null
     */
    private function writeAccessTokenToFile($accessTokenToWrite, $refreshTokenToWrite)
    {
        #TODO Use php apc_cache rather than file to store the token variables
        // apc_store('tokens', $accessTokenToWrite . ":" . $refreshTokenToWrite);

        $filename = 'conf/.accesstoken';

        if (!file_exists($filename) || !is_writable($filename)) {
            SystemLog::addLog('File doesn\'t exist or is not writable to new access token : ' . $filename);
            return null;
        } else {
            $accessTokenStoreFile = fopen($filename, "w");

            #TODO we could use file_put_contents(file,data,mode,context)
            fwrite($accessTokenStoreFile, $accessTokenToWrite . ":" . $refreshTokenToWrite);
            fclose($accessTokenStoreFile);
            return;
        }
    }

    /**
     * @return bool
     */
    private function checkTokenValidity()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://etmdb.com/api/v1/movie/detail/1');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->setHeader());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);

        $serverResponse = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        SystemLog::addLog('HTTP status Code: ' . $httpStatusCode);

        if ($httpStatusCode === 200) {
            return true;
        } else {
            SystemLog::addLog('Authorization unsuccessful. HTTP status Code: ' . $httpStatusCode);
            return false;
        }
    }

    /**
     * @param $fieldsType
     * @return mixed|null
     */
    public function curlRequestHandler($fieldsType)
    {
        $credential = $this->config['CLIENT_ID'] . ':' . $this->config['CLIENT_SECRET'];

        $accCurl = curl_init();
        curl_setopt($accCurl, CURLOPT_URL, "https://'.$credential.'@etmdb.com/api/oauth/token/");
        curl_setopt($accCurl, CURLOPT_POST, true);
        curl_setopt($accCurl, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($accCurl, CURLOPT_TIMEOUT, 120);
        curl_setopt($accCurl, CURLOPT_POSTFIELDS, $this->setPostFields($fieldsType));
        curl_setopt($accCurl, CURLOPT_RETURNTRANSFER, true);
        $returnedJsonForNewToken = curl_exec($accCurl);

        SystemLog::addLog('Returned Json For New Token ' . $returnedJsonForNewToken);

        // Check if any error occurred during the curl request
        if (curl_errno($accCurl)) {
            $curlInfo = curl_getinfo($accCurl);
            SystemLog::addLog('Took ' . $curlInfo['total_time'] . ' seconds to send a request to ' . $curlInfo['url']);
            return null;
        } else if (array_key_exists('error', json_decode($returnedJsonForNewToken))) {
            SystemLog::addLog('Curl request error response ' . $returnedJsonForNewToken);
            return $returnedJsonForNewToken;
        } else {
            curl_close($accCurl);
            return $returnedJsonForNewToken;
        }
    }

    /**
     * @param $fieldsType
     * @return array
     */
    private function setPostFields($fieldsType)
    {
        switch ($fieldsType) {
            case 'NEW_TOKEN':
                $this->postFields = array(
                    "grant_type" => 'password',
                    "username" => $this->config['USERNAME'],
                    "password" => $this->config['PASSWORD'],
                    "scope" => "write groups read"
                );
                break;
            case 'REFRESH_TOKEN':
                $this->postFields = array(
                    "grant_type" => "refresh_token",
                    "client_id" => $this->config['CLIENT_ID'],
                    "client_secret" => $this->config['CLIENT_SECRET'],
                    "refresh_token" => $this->getRefreshTokenFromFile()
                );
                break;
            default:
                $this->postFields = array();
        }

        return $this->postFields;
    }

    /**
     * @return array|null
     */
    public function setHeader()
    {
        $accessToken = $this->getAccessTokenFromFile();
        SystemLog::addLog('Access token from file : ' . $accessToken);

        if (!is_string($accessToken)) {
            return null;
        }
        return [
            'Accept: application/json',
            'Authorization: Bearer ' . $this->getAccessTokenFromFile()
        ];
    }

    /**
     * @return string
     */
    public function setTelegramApiUrl()
    {
        $TelegramApiUrl= $this->config['TG_AUTH_URL'] . $this->config['TG_TOKEN'] . '/';
		
		return $TelegramApiUrl;
    }
}