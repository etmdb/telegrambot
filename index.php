<?php

/**
 * @author      EtMDB Devs (developers@etmdb.com)
 * @copyright   15/08/2017
 * @license     https://www.etmdb.com/license
 * @version     1.1.0
 */

require_once(__DIR__ . '/init.php');

use EtMDB\TelegramBot\RequestHandler;
use EtMDB\TelegramBot\SystemLog;
use EtMDB\TelegramBot\DataHandler;
use EtMDB\TelegramBot\ApiAccessManager;

define('SEARCH_MOVIE', 0);
define('SEARCH_PEOPLE', 1);
define('SEARCH_CINEMA', 2);
define('SEARCH_COMPANY', 3);

SystemLog::addLog('-------------------------------------');
SystemLog::addLog('-------------------------------------');
SystemLog::addLog('-------------------------------------');
SystemLog::addLog('-------------------------------------');
SystemLog::addLog('Start process ....');


$requestHandler = new RequestHandler($config);
$apiAccessHandler = new ApiAccessManager($config);

// check if anything is received
$jsonFromTG = file_get_contents("php://input");

if (isset($jsonFromTG)) {
    $decodedJsonFromTG = DataHandler::convertToJson($jsonFromTG);

	
    // if received a PM
    if (isset($decodedJsonFromTG["message"]["chat"]["id"])) {
	    $sessionChatID = $decodedJsonFromTG["message"]["chat"]["id"];
        $messageText = $decodedJsonFromTG['message']['text'];
        // This if statement works on all /commands sent to the bot
        // This is made to prevent user confusion
        if ($messageText[0] == "/") {
            $keyboard = array(
                "inline_keyboard" => array(
                    array(
                        array(
                            "text" => "Search movies",
                            "switch_inline_query_current_chat" => ""
                        )
                    ),
                    array(
                        array(
                            "text" => "Search artists (/a)",
                            "switch_inline_query_current_chat" => "/a "
                        )
                    ),
                    array(
                        array(
                            "text" => "Search cinemas (/b)",
                            "switch_inline_query_current_chat" => "/b "
                        )
                    ),
                    array(
                        array(
                            "text" => "Search film companies (/c)",
                            "switch_inline_query_current_chat" => "/c "
                        )
                    )
                )
            );
            $keyboard = json_encode($keyboard, true);
            $requestHandler->sendTextMessage($sessionChatID, "What would you like to search for?", "HTML", $keyboard);


			
        } else if (isset($decodedJsonFromTG['message']['entities'])) {
            //This works on messages that contain entities, such as the article the bot receives when an inline result is chosen
        } else {
            $keyboard = array(
                "inline_keyboard" => array(
                    array(
                        array(
                            "text" => "Search movies",
                            "switch_inline_query_current_chat" => $messageText
                        )
                    ),
                    array(
                        array(
                            "text" => "Search artists (/a)",
                            "switch_inline_query_current_chat" => "/a " . $messageText
                        )
                    ),
                    array(
                        array(
                            "text" => "Search cinemas (/b)",
                            "switch_inline_query_current_chat" => "/b " . $messageText
                        )
                    ),
                    array(
                        array(
                            "text" => "Search film companies (/c)",
                            "switch_inline_query_current_chat" => "/c " . $messageText
                        )
                    )
                )
            );

            $keyboard = json_encode($keyboard, true);
            $requestHandler->sendTextMessage($sessionChatID, "<i>Where should I search for: </i><b>" . $messageText . "</b>", "HTML", $keyboard);
        }
    } // if received an inline query

    else
        if (isset($decodedJsonFromTG["inline_query"]["id"])) {
		    $sessionQueryID = $decodedJsonFromTG["inline_query"]["id"];

            $queryText = $decodedJsonFromTG["inline_query"]["query"];
            $searchQueryTerm = $queryText;
            $inlineResults = array();

            // Split command and query
            if ($queryText[0] == '/' && $queryText[1] != ' ') {
                $searchQueryTerm = substr($queryText, 3);
                if ($queryText[1] == 'a') {
                    // search in people
                    $jsonFromETMDB = $requestHandler->search($searchQueryTerm, SEARCH_PEOPLE);
                    $inlineResults = $requestHandler->getInlinePeopleResult($jsonFromETMDB);
                } else
                    if ($queryText[1] == 'b') {
                        // search in cinemas
                        $jsonFromETMDB = $requestHandler->search($searchQueryTerm, SEARCH_CINEMA);
                        $inlineResults = $requestHandler->getInlineCinemasResult($jsonFromETMDB);
                    } else
                        if ($queryText[1] == 'c') {
                            // search film companies
                            $jsonFromETMDB = $requestHandler->search($searchQueryTerm, SEARCH_COMPANY);
                            $inlineResults = $requestHandler->getInlineCompaniesResult($jsonFromETMDB);
                        }
            } else {
                $jsonFromETMDB = $requestHandler->search($searchQueryTerm, SEARCH_MOVIE);
                $inlineResults = $requestHandler->getInlineMoviesResult($jsonFromETMDB);
            }
            $requestHandler->showInlineResults($sessionQueryID, $inlineResults);
        }
}