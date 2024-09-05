<?php

namespace App;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeHelper
{
    public static function fetchDocument(string $url): Crawler
    {
        $client = new Client();
        $response = $client->get($url);
        //var_dump($response->getBody()->getContents());
        return new Crawler($response->getBody()->getContents(), $url);
    }
}
