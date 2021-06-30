<?php

namespace ForeverCompanies\Delighted\Model;

class ListResource
{
    private $klass;
    private $path;
    private $opts;
    private $client;
    private $iteration_count;
    private $done;
    private $next_link;

    public function __construct($klass, $path, $opts, $client)
    {
        $this->klass = $klass;
        $this->path = $path;
        $this->opts = $opts;
        $this->client = $client;
        $this->iteration_count = 0;
        $this->done = false;
    }

    public function autoPagingIterator($opts = []) {
        if ($this->done) {
            throw new PaginationException("pagination completed");
        }

        $auto_handle_rate_limits = $opts['auto_handle_rate_limits'] ?? false;

        while (true) {
            try {
                // Get first (or next) page
                if ($this->iteration_count == 0) {
                    $response = $this->client->getRequest($this->path, $this->opts);
                } else {
                    $response = $this->client->getRequest($this->next_link);
                }
            } catch (\Delighted\RateLimitedException $e) {
                if ($auto_handle_rate_limits) {
                    // Sleep and retry call
                    sleep($e->getRetryAfter());
                    continue;
                } else {
                    throw $e;
                }
            }

            $this->iteration_count++;
            $this->next_link = Utils::parse_link_header($response['headers']['Link'] ?? null)['next'] ?? null;

            foreach ($response['body'] as $json) {
                yield new $this->klass($json);
            }

            if (empty($this->next_link)) {
                break;
            }
        }
        $this->done = true;
    }
}
