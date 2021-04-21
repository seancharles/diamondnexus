<?php

require_once $_SERVER['HOME'] . 'magento/shell/dnl/google_api/Google/Client.php';
require_once $_SERVER['HOME'] . 'magento/shell/dnl/google_api/Google/Service/Content.php';

abstract class BaseFeed
{
  protected $merchant_id;
  protected $service;
  protected $store_id;

  public function __construct($store_id = 1)
  {
    $this->setStoreId($store_id);

    $config_file = $_SERVER['HOME'] . '/' . $this->getFileNameShoppingContent();

    if (file_exists($config_file)) {
      $config = json_decode(file_get_contents($config_file));
    }

    if (!$config) {
      throw new Exception(sprintf('Could not find or read the config file at '
        . '%s. You can use the config.json file in the samples root as a '
        . 'template.', $config_file));
    }

    $client = new Google_Client();
    $client->setApplicationName($config->applicationName);
    $client->setClientId($config->clientId);
    $client->setClientSecret($config->clientSecret);

    try {
      $client->refreshToken($config->refreshToken);
    } catch (Google_Auth_Exception $exception) {
      print (str_repeat('*', 40));
      print ("Your refresh token was missing or invalid, fetching a new one\n");
      $refresh_token = $this->getRefreshToken($client);
      $config->refreshToken = $refresh_token;
      file_put_contents($config_file, json_encode($config));
      print ("Refresh token saved to your config file\n");
      print (str_repeat('*', 40));
    }

    $this->merchant_id = $config->merchantId;
    $this->service = new Google_Service_Content($client);
  }

  /**
   * @param $storeId
   */
  public function setStoreId($storeId)
  {
    $this->store_id = $storeId;
  }

  /**
   * @return int
   */
  public function getStoreId()
  {
    return $this->store_id;
  }

  /**
   * @return string
   */
  public function getFileNameShoppingContent()
  {
    return '.shopping-content-' . $this->getStoreId() . '.json';
  }

  /**
   * @return string
   */
  public function getWebUrl()
  {
    return Mage::app()->getStore($this->getStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
  }

  /**
   * @return string
   */
  public function getWebsiteName() {
    return Mage::app()->getStore($this->getStoreId())->getWebsite()->getName();
  }

  abstract public function run();

  private function getRefreshToken(Google_Client $client)
  {
    $client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
    $client->setScopes('https://www.googleapis.com/auth/content');
    $client->setAccessType('offline'); // So that we get a refresh token

    printf("Visit the following URL and log in:\n\n\t%s\n\n",
      $client->createAuthUrl());
    print ('Then type the resulting code here: ');
    $code = trim(fgets(STDIN));
    $client->authenticate($code);

    $token = $client->getAccessToken();
    $decoded_token = json_decode($token);

    return $decoded_token->refresh_token;
  }

  // Retry a function with back off
  protected function retry($function, $parameter, $max_attempts = 5)
  {
    $attempts = 1;

    while ($attempts <= $max_attempts) {
      try {
        return call_user_func(array($this, $function), $parameter);
      } catch (Google_Service_Exception $exception) {
        sleep($attempts * $attempts);
        $attempts++;
      }
    }
  }
}
