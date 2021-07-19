<?php

namespace ForeverCompanies\Delighted\Model;

if (! interface_exists('JSONSerializable')) {
    interface JSONSerializable
    {
        public function jsonSerialize();
    }
} else {
    interface JSONSerializable extends \JSONSerializable
    {
    }
}