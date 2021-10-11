<?php

ini_set("display_errors", 1);

class IterableMulti {

    public $csv = [];
    public $queue = [];

    protected $timeout = 15;
    protected $threads;

    protected $mh;
    protected $ch = [];
    protected $batch = [];

    public function __construct($threads) {
        $this->threads = (int) $threads;
    }

    # map brand to url
    protected $brandUrlMap = [
        "dn" => "https://www-api.diamondnexus.com/fciterablefeeds/products/?pids=",
        "tf" => "https://www-api.1215diamonds.com/fciterablefeeds/products/?pids="
    ];

    public function getIterableCSV() {
        $handle = fopen('url.csv','r');

        # read past header line
        $headers = $data = fgetcsv($handle);

        # process csv into array
        while ( ($data = fgetcsv($handle) ) !== FALSE ) {
            # parse params
            $params = json_decode($data[1]);
            # get first five product ids
            $params = array_slice($params, -5);
            # shortened querystring [x,x,x]&brand=dn
            $querystring = "[" . implode(",", $params) ."]&brand=" . $data[2];
            # create unique key to de-dupe processes
            $key = implode("_", $params);

            $url = $this->brandUrlMap[$data[2]] . $querystring;

            # only add rows with products
            if (sizeof($params) > 0) {
                $this->csv[$key] = [
                    'url' => $url
                ];
            }
        }

        foreach($this->csv as $key => $value) {
            $this->queue[$key] = 0;
        }
    }

    protected function initializeCurlClients () {
        # initialize curl clients
        for($i=0; $i<=$this->threads; $i++) {
            $this->ch[$i] = curl_init();
        }
    }

    protected function getBatchList() {
        $this->batch = [];
        $i = 0;

        foreach($this->queue as $key => $value) {
            if ($i <= $this->threads) {
                $this->batch[$i] = $key;
                $i++;
            } else {
                break;
            }
        }
    }

    protected function removeBatchListFromQueue() {
        foreach($this->batch as $key => $value) {
            unset($this->queue[$value]);
        }
    }

    protected function setClientUrl () {
        for($i=0; $i<$this->threads; $i++) {
            # get the unique array key
            $queueKey = $this->batch[$i];
            # set the client url
            curl_setopt($this->ch[$i], CURLOPT_URL, $this->csv[$queueKey]['url']);

            echo "caching: " . $this->csv[$queueKey]['url'] . "\n";

            # set the multi handle
            curl_multi_add_handle($this->mh, $this->ch[$i]);
        }
    }

    protected function unsetClientHandles () {
        for($i=0; $i<$this->threads; $i++) {
            # unset the multi handle
            curl_multi_remove_handle($this->mh, $this->ch[$i]);
        }
    }

    protected function getClientContents() {
        $result = [];

        for($i=0; $i<$this->threads; $i++) {
            $result[$i] = curl_multi_getcontent($this->ch[$i]);
        }

        return $result;
    }

    public function warmCache()
    {
        $this->mh = curl_multi_init();

        $pages = ceil(count($this->queue) / $this->threads);

        # page through the queue with x number of threads
        for ($i=1; $i<=$pages; $i++) {

            $this->initializeCurlClients();

            $this->getBatchList();
            $this->setClientUrl();

            do {
                curl_multi_exec($this->mh, $running);
                curl_multi_select($this->mh, $this->timeout);
            } while ($running > 0);

            $this->unsetClientHandles();
            $this->removeBatchListFromQueue();

            echo "Processing next batch " . count($this->queue) . " URLs remaining\n";
        }

        curl_multi_close($this->mh);
    }

    public function saveTxt()
    {
        $filename = 'url-list-' . time() . '.txt';

        if (!$handle = fopen($filename, 'w+')) {
            echo "Cannot open file ($filename)";
            exit;
        }

        foreach($this->csv as $key => $line)
        if (fwrite($handle, $line['url'] . "\r\n") === FALSE) {
            echo "Cannot write to file ($filename)";
            exit;
        }

        fclose($handle);

        echo "Saved url list to " . $filename . "\n";
    }
}

# Number of concurrent threads running via CLI
# Use php curl.php -t 50
$options = getopt("t:");

if(isset($options['t']) === true) {
    $threads = (int) $options['t'];
} else {
    $threads = 10;
}

$iterableWarmer = new IterableMulti($threads);
$iterableWarmer->getIterableCSV();
$iterableWarmer->saveTxt();
$iterableWarmer->warmCache();

