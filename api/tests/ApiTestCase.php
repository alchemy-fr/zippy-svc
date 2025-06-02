<?php

declare(strict_types=1);

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\Response;

abstract class ApiTestCase extends WebTestCase
{
    protected ?AbstractBrowser $client = null;

    protected function request(
        string $method,
        string $uri,
        $params = [],
        array $files = [],
        array $server = [],
        ?string $content = null
    ): Response {
        $server['CONTENT_TYPE'] = $server['CONTENT_TYPE'] ?? 'application/json';
        $server['HTTP_ACCEPT'] = $server['HTTP_ACCEPT'] ?? 'application/json';
        $server['HTTP_AUTHORIZATION'] = 'client:secret';

        if (empty($content) && !empty($params) && in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            $content = json_encode($params);
        }

        $this->client->request($method, $uri, $params, $files, $server, $content);

        /** @var Response $response */
        $response = $this->client->getResponse();

        return $response;
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
        return self::$container->get(EntityManagerInterface::class);
    }

    protected function clearEmBeforeApiCall(): void
    {
        self::getEntityManager()->clear();
    }
}
