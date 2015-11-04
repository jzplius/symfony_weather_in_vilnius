<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Monolog\Handler\Curl;

class WeatherController extends Controller
{
    /**
     * @Route("/weather")
     */
    public function weatherAction()
    {
        // Get API response
        $response = $this->getApiResponseAsArray(
            "https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20weather.forecast%20where%20woeid%20in%20(select%20woeid%20from%20geo.places(1)%20where%20text%3D%22vilnius%2C%20lt%22)&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=",
            array(),
            '');

        $temp_from = $this->farenheitToCelcius($response['query']['results']['channel']['item']['forecast']['0']['low']);
        $temp_to = $this->farenheitToCelcius($response['query']['results']['channel']['item']['forecast']['0']['high']);

        return $this->render('default/weather.html.twig', array(
            'temp_from' => $temp_from,
            'temp_to' => $temp_to,
        ));
    }

    /**
     * Make a post request and return JSON response
     * @param $url
     * @param $fields
     * @param $post_vars
     * @return array containing JSON response elements
     */
    protected function getApiResponseAsArray($url, $fields, $post_vars){

        $ch = curl_init();
        foreach($fields as $key=>$value) {
            $post_vars .= $key . "=" . $value . "&";
        }
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$post_vars);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,3);
        curl_setopt($ch,CURLOPT_TIMEOUT, 20);
        $response = curl_exec($ch);

        return json_decode($response, true);
    }

    protected function farenheitToCelcius($value) {
        return number_format(((intval($value) - 32) * 5 / 9), 1);
    }
}
