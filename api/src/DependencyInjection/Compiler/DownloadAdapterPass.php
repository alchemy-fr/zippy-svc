<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Download\DownloadAdapterInterface;
use App\Download\DownloadManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DownloadAdapterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(DownloadManager::class)) {
            return;
        }

        $definition = $container->getDefinition(DownloadManager::class);

        foreach ($container->findTaggedServiceIds('app.download_adapter') as $id => $tag) {
            /* @var DownloadAdapterInterface|string $id */
            $definition->addMethodCall('addAdapter', [new Reference($id)]);
        }
    }
}
