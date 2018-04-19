<?php
namespace common\components;

use linslin\yii2\curl;
use yii\base\Component;

class TMDb extends Component
{
    public $api_key;
    public $language;
    public $region;

    /*public function init(){

    }*/

    public function getTitleData($id_tmdb, $type, $ext = [], $decode = true)
    {
        
        $curl = new curl\Curl();
        $append = implode(',', $ext);
        $data = $curl->setHeaders([
            'content-type' => 'application/json',
            'charset' => 'utf-8',
        ])->get("https://api.themoviedb.org/3/$type/$id_tmdb?api_key=$this->api_key&language=$this->language&append_to_response=$append");
        
        $show = $decode ? json_decode($data, true) : $data;
        
        return $show;
    }

    public function getSeasonData($id_tmdb, $season)
    {
        $curl = new curl\Curl();
        $data = $curl->get("https://api.themoviedb.org/3/tv/$id_tmdb/season/$season?api_key=$this->api_key&language=$this->language");
        $season = json_decode($data, true);

        return $season;
    }

    public function getEpisodeData($id_tmdb, $season, $episode)
    {
        $curl = new curl\Curl();
        $data = $curl->get("https://api.themoviedb.org/3/tv/$id_tmdb/season/$season/episode/$episode?api_key=$this->api_key&language=$this->language");
        $chapter = json_decode($data, true);

        return $chapter;
    }

    public function rateMovie($id_tmdb, $guest_session_id, $value)
    {
        $curl = new curl\Curl();

        $response = $curl->setRequestBody(json_encode(['value' => $value]))
            ->setHeaders([
                'content-type' => 'application/json',
                'charset' => 'utf-8',
            ])
            ->post("https://api.themoviedb.org/3/movie/$id_tmdb/rating?api_key=$this->api_key&guest_session_id=$guest_session_id");

        $response = json_decode($response, true);

        if ($response['status_code'] === 1 || $response['status_code'] === 12) {
            return true;
        } else {
            return false;
        }
    }

    public function rateEpisode($id_tmdb, $season, $episode, $guest_session_id, $value)
    {
        $curl = new curl\Curl();

        $url = "https://api.themoviedb.org/3/tv/$id_tmdb/season/$season/episode/$episode/rating?" .
            "api_key=$this->api_key&guest_session_id=$guest_session_id";

        $response = $curl->setRequestBody(json_encode(['value' => $value]))
            ->setHeaders([
                'content-type' => 'application/json',
                'charset' => 'utf-8',
            ])
            ->post($url);

        $response = json_decode($response, true);

        if ($response['status_code'] === 1 || $response['status_code'] === 12) {
            return true;
        } else {
            return false;
        }

    }

    public function generateGuestSessionId()
    {
        $curl = new curl\Curl();
        $data = $curl->get("https://api.themoviedb.org/3/authentication/guest_session/new?api_key=$this->api_key");
        $res = json_decode($data, true);

        if (isset($res['success'])) {
            return $res['guest_session_id'];
        } else {
            return false;
        }
    }

    public function checkGuestSessionId($guest_session_id)
    {
        $curl = new curl\Curl();
        $id_tmdb = '637';
        $value = 5;

        $response = $curl->setRequestBody(json_encode(['value' => $value]))
            ->setHeaders([
                'content-type' => 'application/json',
                'charset' => 'utf-8',
            ])
            ->post("https://api.themoviedb.org/3/movie/$id_tmdb/rating?api_key=$this->api_key&guest_session_id=$guest_session_id");

        $response = json_decode($response, true);

        if ($response['status_code'] === 3) {
            return false;
        } else if ($response['status_code'] === 1 || $response['status_code'] === 12) {
            $response = $curl->setHeaders(['content-type' => 'application/json', 'charset' => 'utf-8'])
                ->delete("https://api.themoviedb.org/3/movie/$id_tmdb/rating?api_key=$this->api_key&guest_session_id=$guest_session_id");
        }
        return true;
        /*
    If returns this error is expired
    {
    "status_code": 3,
    "status_message": "Authentication failed: You do not have permissions to access the service."
    }
     */
    }

    public function search($query, $type = "multi", $page = 1)
    {
        $curl = new curl\Curl();
        $query = urlencode($query);
        $url = "https://api.themoviedb.org/3/search/$type?api_key=$this->api_key&language=$this->language&query=$query&page=$page";
        $data = $curl->get($url);
        $results = json_decode($data, true);

        return $results;

    }

}
