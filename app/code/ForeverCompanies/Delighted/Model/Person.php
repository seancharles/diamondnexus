<?php

namespace ForeverCompanies\Delighted\Model;

use ForeverCompanies\Delighted\Model\Resource;
use ForeverCompanies\Delighted\Model\Client;

class Person extends Resource
{
    protected static $path = 'people';
    
    protected $person;
    protected $client;
    
    public function __construct(
        Client $cli
    ) {
        $this->client = $cli;
    }

    public function create($props = [], Client $client = null)
    {
        $response = $this->client->post(self::$path, $props);
        return $response;
    }

    public static function delete($idAssoc = array(), Client $client = null) {
        if (is_null($client)) {
            $client = Client::getInstance();
        }

        $identifier = self::identifierString($idAssoc);
        $path = self::$path . '/' . urlencode($identifier);
        return $client->delete($path);
    }

    public static function all($params = [], Client $client = null)
    {
        if (is_null($client)) {
            $client = Client::getInstance();
        }

        $r = [];
        $responses = $client->get(self::$path, $params);
        foreach ($responses as $response) {
            $r[] = new Person($response);
        }

        return $r;
    }

    public static function list($params = [], Client $client = null)
    {
        if (is_null($client)) {
            $client = Client::getInstance();
        }
        return new ListResource(get_class(), self::$path, $params, $client);
    }
}
