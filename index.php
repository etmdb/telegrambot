<?php
/**
 *
 * @author      EtMDB Devs>
 * @copyright   15/08/2017
 * @license     https://www.etmdb.com/license
 * @version     1.1.0
 */

include_once('config.inc.php');

//Constants

//TG Constants
define('BOT_TOKEN', $config['TG_TOKEN']);

define('API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');

//ETMDB Constants
define("ETMDB_API_ACCESS_TOKEN", "Bearer " . $config['ETMDB_TOKEN'] . "");
//Some more constants
define("ETMDB_SEARCH_MOVIES", 0);
define("ETMDB_SEARCH_PEOPLE", 1);
define("ETMDB_SEARCH_CINEMA", 2);
define("ETMDB_SEARCH_FILM_COMPANY", 3);

/**
 * Get access token 
 * @return mixed
 */
function getAccessToken()
{
    $accCurl = curl_init();
    curl_setopt($accCurl, CURLOPT_URL, "https://".$config['CLIENT_ID'].":".$config['CLIENT_SECRET']."@etmdb.com/api/oauth/token/");
    curl_setopt($accCurl, CURLOPT_POST, true);
    curl_setopt($accCurl, CURLOPT_POSTFIELDS, array(
        "grant_type"    => "password",
        "username"      => $config['USERNAME'],
        "password"      => $config['PASSWORD'],
        "scope"         => "write groups read"
    ));

    curl_setopt($accCurl, CURLOPT_RETURNTRANSFER, true);
    $jsonFirst = curl_exec($accCurl);
    $jsond = json_decode($jsonf, true);
    curl_close($accCurl);
    error_log($jsond['access_token']);
    return $jsond['access_token'];
}

/**
 * @param $SearchTerm
 * @param $WhereToSearch
 * @return mixed
 */
function Search($SearchTerm, $WhereToSearch)
{

//A pretty self-explanatory switch statement
    switch ($WhereToSearch) {

        case ETMDB_SEARCH_MOVIES:
            $MoviesSearchURL = "https://etmdb.com/api/v1/movie/search/$SearchTerm";
            $SearchURL = $MoviesSearchURL;
            break;

        case ETMDB_SEARCH_PEOPLE:
            $PeopleSearchURL = "https://etmdb.com/api/v1/people/search/$SearchTerm";
            $SearchURL = $PeopleSearchURL;
            break;

        case ETMDB_SEARCH_CINEMA:
            $CinemaSearchURL = "https://etmdb.com/api/v1/cinema/search/$SearchTerm";
            $SearchURL = $CinemaSearchURL;
            break;

        case ETMDB_SEARCH_FILM_COMPANY:
            $FilmCompanySearchURL = "https://etmdb.com/api/v1/company/search/$SearchTerm";
            $SearchURL = $FilmCompanySearchURL;
            break;

        default:
            $DefaultSearchURL = "https://etmdb.com/api/v1/movie/search/$SearchTerm";
            $SearchURL = $DefaultSearchURL;
    }

    $header = array('Accept: application/json',
        'Authorization: Bearer ' . getAccessToken());

    $SearchCurl = curl_init();


    curl_setopt($SearchCurl, CURLOPT_URL, $SearchURL);
    curl_setopt($SearchCurl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($SearchCurl, CURLOPT_RETURNTRANSFER, true);


    $FoundSearchResultsJSON = curl_exec($SearchCurl);
//Don't forget to tidy up
    curl_close($SearchCurl);

//Result is a JSON file
    return $FoundSearchResultsJSON;
}

/**
 * @param $ChatID
 * @param $TextMessageToSend
 * @return bool|string
 */
function SendTextMessage($ChatID, $TextMessageToSend)
{
//Dont forget to URL encode the Message
    $TextMessageToSend = urlencode($TextMessageToSend);
//Prepare URL
    $TGSendMessageURL = API_URL . "sendmessage?chat_id=" . $ChatID . "&text=" . $TextMessageToSend;

    $TGMessageReceivedResponse = file_get_contents($TGSendMessageURL);

    return $TGMessageReceivedResponse;
}

/**
 * @param $queryID
 * @param $ResultsToShow
 * @return mixed
 */
function ShowInlineResults($queryID, $ResultsToShow)
{

    $POSTField = array(
        "inline_query_id" => $queryID,
        "results" => json_encode($ResultsToShow)
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, API_URL . "answerInlineQuery");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTField);
    $chResult = curl_exec($ch);
    curl_close($ch);
	
    return $chResult;
}

//check if anything is received
$JSONFromTG = file_get_contents("php://input");

if (isset($JSONFromTG)) {
//decode JSON to array
    $DecodedJSONFromTG = json_decode($JSONFromTG, true);

    $SessionChatID = $DecodedJSONFromTG["message"]["chat"]["id"];
    $SessionQueryID = $DecodedJSONFromTG["inline_query"]["id"];

//if received a PM
    if (isset($SessionChatID)) {
//SendTextMessage($SessionChatID,$JSONFromTG);
        $MessageText = $DecodedJSONFromTG['message']['text'];

        $ReplyMakup = "['inline_keyboard':['text':'hi']]";


        $TGSendMessageURL = API_URL . "sendmessage?chat_id=" . $SessionChatID . "&text=" . "TEST" . "&reply_markup=" . $ReplyMakup;

        $TGMessageReceivedResponse = file_get_contents($TGSendMessageURL);

        SendTextMessage($SessionChatID, "Response received: " . $TGMessageReceivedResponse);


//SendTextMessage($SessionChatID,"Where should I search for: ".$MessageText);


    } //if received an inline query
    else if (isset($SessionQueryID)) {
        $queryText = $DecodedJSONFromTG["inline_query"]["query"];

        $WhatToSearchFor = $queryText;

        $InlineResults = array();

        $JSONFromETMDB = Search(
            $WhatToSearchFor, ETMDB_SEARCH_MOVIES);

        $DecodedJSONFromETMDB = json_decode($JSONFromETMDB, true);


        foreach ($DecodedJSONFromETMDB as $SingleResult) {
            $MovieTitle = $SingleResult['movie_title'];
            $MoviePlot = $SingleResult['plot'];
            $MoviePoster = $SingleResult['poster_image'];


            $SomeVoodoo = "\r\n\r\n <a href='$MoviePoster' >-</a> <a href='https://etmdb.com' >ETMDB</a>";

            $MovieSummaryInHTMLFormat =
                "<b>$MovieTitle </b>\r\n\r\n" .
                strip_tags($MoviePlot) . $SomeVoodoo;


            $SingleResultPhotoURL = $MoviePoster;
            $SingleResultThumbnailURL = $MoviePoster;

            $SingleResultTitle = $MovieTitle;
            $SingleResultDescription = strip_tags($MoviePlot);

            $InlineEntry = array(
                "type"          => "article",
                "id"            => "" . rand(),
                "thumb_url"     => $SingleResultThumbnailURL,
                "title"         => $SingleResultTitle,
                "description"   => $SingleResultDescription,
                "input_message_content" => array(
                                        "message_text"     => $MovieSummaryInHTMLFormat,
                                        "parse_mode" => "HTML")
            );

            array_push($InlineResults, $InlineEntry);

            $Temp = json_encode($InlineEntry);

            error_log("________________________________");
            error_log($Temp);


        }

        error_log(ShowInlineResults($SessionQueryID, $InlineResults));

    }
}

?>
