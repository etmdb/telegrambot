<?php
/**
 * @author      EtMDB Devs (developers@etmdb.com)
 * @copyright   15/08/2017
 * @license     https://www.etmdb.com/license
 * @version     1.1.0
 */

namespace EtMDB\TelegramBot;


class RequestHandler
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * RequestHandler constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @var array
     */
    private $movieInlineResults = [];

    /**
     * @var array
     */
    private $cinemaInlineResults = [];

    /**
     * @var array
     */
    private $peopleInlineResults = [];

    /**
     * @var array
     */
    private $companyInlineResults = [];


    /**
     * #TODO add links to the movie and add movie casts, genre of the movie
     * @param $jsonApiResponse
     * @return array
     */
    public function getInlineMoviesResult($jsonApiResponse)
    {
        $decodedJSONFromETMDB = json_decode($jsonApiResponse, true);
        foreach ($decodedJSONFromETMDB as $singleResult) {
            $MovieTitle = $singleResult['movie_title'];
            $MoviePlot = $singleResult['plot'];
            $MovieLength = $singleResult['duration_minutes'];
            $MoviePoster = $singleResult['poster_image'];
            $MovieYear = explode("-", $singleResult['release_date'])[0];
            $SomeVoodoo = "\r\n\r\n <a href='{$MoviePoster}' >-</a> <a href='https://etmdb.com' >ETMDB</a>";
            $MovieSummaryInHTMLFormat = "<b>{$MovieTitle} ({$MovieYear})</b>\r\n️🍿 <i>
                                        Duration: {$MovieLength} min</i>\r\n\r\n" . strip_tags($MoviePlot) . $SomeVoodoo;
            $SingleResultPhotoURL = $MoviePoster;
            $SingleResultThumbnailURL = $MoviePoster;
            $SingleResultTitle = $MovieTitle;
            $SingleResultDescription = strip_tags($MoviePlot);

            $inlineEntry = array(
                "type" => "article",
                "id" => "" . rand(),
                "thumb_url" => $SingleResultThumbnailURL,
                "title" => $SingleResultTitle,
                "description" => $SingleResultDescription,
                "input_message_content" => array(
                    "message_text" => $MovieSummaryInHTMLFormat,
                    "parse_mode" => "HTML"
                )
            );
            array_push($this->movieInlineResults, $inlineEntry);
            $temp = json_encode($inlineEntry);
            SystemLog::addLog('Stored temp response : ' . $temp);
        }

        return $this->movieInlineResults;
    }

    /**
     * #TODO add links to the cast, add filmographies and awards of the cast
     * @param $jsonApiResponse
     * @return array
     */
    public function getInlinePeopleResult($jsonApiResponse)
    {
        $decodedJSONFromETMDB = json_decode($jsonApiResponse, true);
        foreach ($decodedJSONFromETMDB as $singleResult) {
            $PersonFirstName = $singleResult['user']['first_name'];
            $PersonLastName = $singleResult['user']['last_name'];
            $PersonEmail = $singleResult['user']['email'];
            $PersonGender = $singleResult['gender_MF'];
            $PersonBirthDate = $singleResult['date_of_birth'];
            $PersonBirthPlace = $singleResult['birth_place'];
            $PersonHeight = $singleResult['height'];
            $PersonSpouse = $singleResult['spouse'];
            $PersonNickName = $singleResult['nickname'];

            //Capitalize first letter
            $PersonGender[0] = strtoupper($PersonGender[0]);

            if ($PersonHeight === '') {
                $PersonHeight = '';
            }
            if ($PersonBirthDate === '') {
                $PersonBirthDate = '';
            }
            if ($PersonBirthPlace === '') {
                $PersonBirthPlace = '';
            }
            if ($PersonEmail === '') {
                $PersonEmail = '';
            }

            $PersonSummaryInHTMLFormat = "🎭 \r\n<b>{$PersonFirstName}{$PersonLastName}</b> \r\n
                                          Gender:$PersonGender \r\n
                                          Height:{$PersonHeight}\r\n
                                          Date Of Birth: {$PersonBirthDate}\r\n
                                          Birth Place: {$PersonBirthPlace} \r\n\r\n
                                          Email: ({$PersonEmail})\r\n\r\n";

            $SingleResultTitle = $PersonFirstName . " " . $PersonLastName;
            $SingleResultDescription = strip_tags($PersonGender);

            $inlineEntry = array(
                "type" => "article",
                "id" => "" . rand(),
                "title" => $SingleResultTitle,
                "description" => $SingleResultDescription,
                "input_message_content" => array(
                    "message_text" => $PersonSummaryInHTMLFormat,
                    "parse_mode" => "HTML"
                )
            );
            array_push($this->peopleInlineResults, $inlineEntry);
            $temp = json_encode($inlineEntry);
            SystemLog::addLog('Stored temp response : ' . $temp);
        }

        return $this->peopleInlineResults;
    }

    /**
     * #TODO add links to the cinema and cinema details
     * @param $jsonApiResponse
     * @return array
     */
    public function getInlineCinemasResult($jsonApiResponse)
    {
        $decodedJSONFromETMDB = json_decode($jsonApiResponse, true);
        foreach ($decodedJSONFromETMDB as $singleResult) {
            $CinemaName = $singleResult['cinema_name'];
            $CinemaEstablishedDate = $singleResult['established_in'];
            $CinemaDescription = $singleResult['description'];
            $CinemaOpenTime = $singleResult['opens_at'];
            $CinemaCloseTime = $singleResult['closes_at'];
            $CinemaPoster = $singleResult['cinema_poster_image'];
            $SomeVoodoo = "\r\n\r\n <a href='{$CinemaPoster}' >-</a> <a href='https://etmdb.com' >ETMDB</a>";
            $CinemaSummaryInHTMLFormat = "<b>{$CinemaName}</b> \r\n📽️ <i>Open From: {$CinemaOpenTime}-
                        {$CinemaCloseTime}</i>\r\n\r\n" . strip_tags($CinemaDescription) . "\r\n\r\n
                        Established In: {$CinemaEstablishedDate}" . $SomeVoodoo;
            $SingleResultTitle = $CinemaName;
            $SingleResultDescription = strip_tags("Open From: {$CinemaOpenTime} - {$CinemaCloseTime}");
            $SingleResultThumbnailURL = $CinemaPoster;

            $inlineEntry = array(
                "type" => "article",
                "id" => "" . rand(),
                "title" => $SingleResultTitle,
                "thumb_url" => $SingleResultThumbnailURL,
                "description" => $SingleResultDescription,
                "input_message_content" => array(
                    "message_text" => $CinemaSummaryInHTMLFormat,
                    "parse_mode" => "HTML"
                )
            );
            array_push($this->cinemaInlineResults, $inlineEntry);
            $temp = json_encode($inlineEntry);
            SystemLog::addLog('Stored temp response : ' . $temp);
        }

        return $this->cinemaInlineResults;
    }

    /**
     * #TODO add links to the company and company credits
     * @param $jsonApiResponse
     * @return array
     */
    public function getInlineCompaniesResult($jsonApiResponse)
    {
        $decodedJSONFromETMDB = json_decode($jsonApiResponse, true);
        foreach ($decodedJSONFromETMDB as $singleResult) {
            $CompanyName = $singleResult['company_name'];
            $CompanyEstablishedDate = $singleResult['established_in'];
            $CompanyDescription = $singleResult['description'];
            $CompanyOpenTime = $singleResult['opens_at'];
            $CompanyCloseTime = $singleResult['closes_at'];
            $CompanyPoster = $singleResult['company_poster_image'];
            $SomeVoodoo = "\r\n\r\n <a href='{$CompanyPoster}' >-</a> <a href='https://etmdb.com' >ETMDB</a>";
            $CompanySummaryInHTMLFormat = "<b>{$CompanyName}</b>\r\n🎬 <i>Open From: {$CompanyOpenTime}-
                        {$CompanyCloseTime}</i>\r\n\r\n" . strip_tags($CompanyDescription) . "\r\n\r\n
                        Established In: {$CompanyEstablishedDate}" . $SomeVoodoo;
            $SingleResultTitle = $CompanyName;
            $SingleResultDescription = strip_tags("Open From: {$CompanyOpenTime} - {$CompanyCloseTime}");
            $SingleResultThumbnailURL = $CompanyPoster;

            $inlineEntry = array(
                "type" => "article",
                "id" => "" . rand(),
                "title" => $SingleResultTitle,
                "thumb_url" => $SingleResultThumbnailURL,
                "description" => $SingleResultDescription,
                "input_message_content" => array(
                    "message_text" => $CompanySummaryInHTMLFormat,
                    "parse_mode" => "HTML"
                )
            );
            array_push($this->companyInlineResults, $inlineEntry);
            $temp = json_encode($inlineEntry);
            SystemLog::addLog('Stored temp response : ' . $temp);
        }
        return $this->companyInlineResults;
    }

    /**
     * @param $queryID
     * @param $resultsToShow
     * @return mixed
     */
    public function showInlineResults($queryID, $resultsToShow)
    {
        $postField = array(
            "inline_query_id" => $queryID,
            "results" => json_encode($resultsToShow)
        );

        $apiAccessHandler = new ApiAccessManager();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiAccessHandler->setTelegramApiUrl() . "answerInlineQuery");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postField);
        $chResult = curl_exec($ch);
        curl_close($ch);

        return $chResult;
    }

    /**
     * @param $searchQueryTerm
     * @param $searchType
     * @return mixed
     */
    public function search($searchQueryTerm, $searchType)
    {
        //A pretty self-explanatory switch statement
        switch ($searchType) {

            case SEARCH_MOVIE:
                $searchURL = "https://etmdb.com/api/v1/movie/search/$searchQueryTerm";
                break;

            case SEARCH_PEOPLE:
                $searchURL = "https://etmdb.com/api/v1/people/search/$searchQueryTerm";
                break;

            case SEARCH_CINEMA:
                $searchURL = "https://etmdb.com/api/v1/cinema/search/$searchQueryTerm";
                break;

            case SEARCH_COMPANY:
                $searchURL = "https://etmdb.com/api/v1/company/search/$searchQueryTerm";
                break;

            default:
                $searchURL = "https://etmdb.com/api/v1/movie/search/$searchQueryTerm";
        }

        $apiAccessManager = new ApiAccessManager();

        SystemLog::addLog('Searching with access token: ' . $apiAccessManager->getAccessToken());

        $searchCurl = curl_init();
        curl_setopt($searchCurl, CURLOPT_URL, $searchURL);
        curl_setopt($searchCurl, CURLOPT_HTTPHEADER, $apiAccessManager->setHeader());
        curl_setopt($searchCurl, CURLOPT_RETURNTRANSFER, true);
        $FoundSearchResultsJSON = curl_exec($searchCurl);
        SystemLog::addLog('Found search results JSON: ' . $FoundSearchResultsJSON);

        // Don't forget to tidy up
        curl_close($searchCurl);

        // Result is a JSON file
        return $FoundSearchResultsJSON;
    }

    /**
     * @param $chatID
     * @param $textMessageToSend
     * @param $parseMode
     * @param $ReplyMarkup
     * @return bool|string
     */
    public function sendTextMessage($chatID, $textMessageToSend, $parseMode, $ReplyMarkup)
    {
        // Don't forget to URL encode the Message
        $textMessageToSend = urlencode($textMessageToSend);
        $apiAccessHandler = new ApiAccessManager();

        // Prepare URL
        $TGSendMessageURL = "";
        if ($ReplyMarkup != null) {
            $TGSendMessageURL = $apiAccessHandler->setTelegramApiUrl() . "sendmessage?chat_id=" . $chatID . "&text=" . $textMessageToSend . "&parse_mode=" . $parseMode . "&reply_markup=" . $ReplyMarkup;
        } else {
            $TGSendMessageURL = $apiAccessHandler->setTelegramApiUrl() . "sendmessage?chat_id=" . $chatID . "&parse_mode=" . $parseMode . "&text=" . $textMessageToSend;
        }

        $TGMessageReceivedResponse = file_get_contents($TGSendMessageURL);
        return $TGMessageReceivedResponse;
    }
}