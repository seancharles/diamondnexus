<?php

namespace ForeverCompanies\Delighted\Model;

use ForeverCompanies\Delighted\Model\Resource;

class Unsubscribe extends Resource
{

    public static function create($params = [], Client $client = null)
    {
        if (is_null($client)) {
            $client = Client::getInstance();
        }

        return $client->post('unsubscribes', $params);
    }

    public static function all($params = [], Client $client = null)
    {
        if (is_null($client)) {
            $client = Client::getInstance();
        }

        $responses = $client->get('unsubscribes', $params);
        $r = [];
        foreach ($responses as $unsubscribe) {
            $r[] = new Unsubscribe($unsubscribe);
        }

        return $r;
    }
}
