<?php

namespace App\Service;

use Illuminate\Support\Facades\Http;

class CountryAndTimzoneService
{
    /**
     * @return mixed
     */
    public static function getContinents(){
        $data = [];
        $response = Http::get('http://country.io/continent.json');
        if($response){
            foreach (json_decode($response) as $value){
                $data[] = $value;
            }
            return $data;
        }
    }

    /**
     * @return mixed
     */
    public static function getTimzone(){
        $response = Http::get('http://worldtimeapi.org/api/timezone');
        if($response){
            $data =  json_decode($response);
            return $data;
        }
    }
}
