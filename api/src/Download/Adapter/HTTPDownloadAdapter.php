<?php

declare(strict_types=1);

namespace App\Download\Adapter;

use App\Download\DownloadAdapterInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RedirectMiddleware;

class HTTPDownloadAdapter implements DownloadAdapterInterface
{
    protected ClientInterface $client;
    private int $concurrency;

    public function __construct(ClientInterface $client, int $concurrency = 3)
    {
        $this->client = $client;
        $this->concurrency = $concurrency;
        $this->client = $client;
    }

    public function supportsUri(string $uri): bool
    {
        return 1 === preg_match('#^https?://#', $uri);
    }

    /**
     * {@inheritDoc}
     */
    public function downloadFiles(iterable $files, string $dest): void
    {
        $requests = function () use ($files, $dest): \Iterator {
            foreach ($files as $file) {
                yield function ($poolOpts) use ($file, $dest) {
                    $reqOpts = array_merge($poolOpts, [
                        'sink' => $dest.DIRECTORY_SEPARATOR.$file->getPath(),
                    ]);

                    return $this->client->getAsync($file->getUri(), $reqOpts);
                };
            }
        };

        $pool = new Pool($this->client, $requests(), [
            'concurrency' => $this->concurrency,
            'fulfilled' => function (Response $response, $index) {
                // Grab the URLs this file redirected through to download in chronological order.
                $urls = $response->getHeader(RedirectMiddleware::HISTORY_HEADER);
            },
            'rejected' => function (\Exception $reason, $index) use (&$import_errors) {
                $uri = (string) $reason->getRequest()->getUri();

                throw new \Exception(sprintf('Failed to download "%s": %s', $uri, $reason->getMessage()), 0, $reason);
            },
        ]);

        $pool->promise()->wait();
    }
}
