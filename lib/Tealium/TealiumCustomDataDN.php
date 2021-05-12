<?php
// TealiumExtendData use this to override or extend default values provided by the Tealium module

class TealiumExtendData
{
    private static $store;
    private static $page;

    public static function setStore($store)
    {
        TealiumExtendData::$store = $store;
    }

    public static function setPage($page)
    {
        TealiumExtendData::$page = $page;
    }

    public function getHome(): array
    {
        $store = TealiumExtendData::$store;
        $page = TealiumExtendData::$page;

        $outputArray = array();
        //$outputArray["custom_key"] = "value";

        return $outputArray;
    }

    public function getSearch(): array
    {
        $store = TealiumExtendData::$store;
        $page = TealiumExtendData::$page;

        $outputArray = array();
        //$outputArray["custom_key"] = "value";

        return $outputArray;
    }

    public function getCategory(): array
    {
        $store = TealiumExtendData::$store;
        $page = TealiumExtendData::$page;

        $outputArray = array();
        $outputArray['page_section_name'] = "Steve Test";

        return $outputArray;
    }

    public function getProductPage(): array
    {
        $store = TealiumExtendData::$store;
        $page = TealiumExtendData::$page;

        $outputArray = array();
        //$outputArray["custom_key"] = "value";
        // make sure any product values are in an array

        return $outputArray;
    }

    public function getCartPage(): array
    {
        $store = TealiumExtendData::$store;
        $page = TealiumExtendData::$page;

        $outputArray = array();
        //$outputArray["custom_key"] = "value";
        // make sure any product values are in an array

        return $outputArray;
    }

    public function getOrderConfirmation(): array
    {
        $store = TealiumExtendData::$store;
        $page = TealiumExtendData::$page;

        $outputArray = array();
        //$outputArray["custom_key"] = "value";
        // make sure any product values are in an array

        return $outputArray;
    }

    public function getCustomerData(): array
    {
        $store = TealiumExtendData::$store;
        $page = TealiumExtendData::$page;

        $outputArray = array();
        //$outputArray["custom_key"] = "value";

        return $outputArray;
    }

    public function getCmsPage(): array
    {
        $store = TealiumExtendData::$store;
        $page = TealiumExtendData::$page;


        $outputArray = array();
        $outputArray['site_section'] = 'testing';
        $outputArray['page_name'] = 'My page Name!!';

        return $outputArray;
    }
}


TealiumExtendData::setStore($this->get('store'));
TealiumExtendData::setPage($this->get('page'));


$udoElements = array(
    "Home" => function () {
        $tealiumData = new TealiumExtendData();
        return $tealiumData->getHome();
    },
    "Search" => function () {
        $tealiumData = new TealiumExtendData();
        return $tealiumData->getSearch();
    },
    "Category" => function () {
        $tealiumData = new TealiumExtendData();
        return $tealiumData->getCategory();
    },
    "ProductPage" => function () {
        $tealiumData = new TealiumExtendData();
        return $tealiumData->getProductPage();
    },
    "Cart" => function () {
        $tealiumData = new TealiumExtendData();
        return $tealiumData->getCartPage();
    },
    "Confirmation" => function () {
        $tealiumData = new TealiumExtendData();
        return $tealiumData->getOrderConfirmation();
    },
    "Customer" => function () {
        $tealiumData = new TealiumExtendData();
        return $tealiumData->getCustomerData();
    },
    'CmsPage' => function() {
        $tealiumData = new TealiumExtendData();
        return $tealiumData->getCmsPage();
    },
);

$data['udoElements'] = $udoElements;

$this->setVars($data);