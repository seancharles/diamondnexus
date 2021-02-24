<?php

namespace ForeverCompanies\CustomAttributes\Api;

interface KeysAndValuesInterface
{
    /**
     * @param string $attribute
     * @return array
     */
    public function getKeysAndValues(string $attribute);
}
