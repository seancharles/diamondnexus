<?php

namespace ForeverCompanies\Delighted\Model;

use ForeverCompanies\Delighted\Model\Resource;

class Bounce extends Resource
{
    public static function all($params = [], Client $client = null)
    {
        if (is_null($client)) {
            $client = Client::getInstance();
        }
        $responses = $client->get('bounces', $params);

        $r = [];
        foreach ($responses as $bounce) {
            $r[] = new Bounce($bounce);
        }

        return $r;
    }
}
