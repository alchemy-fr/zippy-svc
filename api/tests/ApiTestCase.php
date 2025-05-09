<?php

declare(strict_types=1);

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Contracts\HttpClient\ResponseInterface;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase as TestCase;

abstract class ApiTestCase extends TestCase
{

    protected ?Client $client;

    protected function request(
        string $method,
        string $uri,
        $params = [],
        array $files = [],
        array $server = [],
        ?string $content = null
    ): ResponseInterface {
        
        if (empty($content) && !empty($params) && in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            $content = json_encode($params);
        }

        $headers['Authorization'] = 'client:secret';
        $headers['Content-Type'] = $server['CONTENT_TYPE'] ?? 'application/json';

        return $this->client->request($method, $uri, [
            'headers' => $headers, 
            'json' => $params
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->disableReboot();
    }

    protected function tearDown(): void
    {
        $em = self::getEntityManager();
        $em->close();
        $this->client = null;

        parent::tearDown();

        gc_collect_cycles();
    }

    protected static function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }

    protected function clearEmBeforeApiCall(): void
    {
        self::getEntityManager()->clear();
    }
}
