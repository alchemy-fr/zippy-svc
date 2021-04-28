<?php

declare(strict_types=1);

namespace App\Tests\Archive;

use App\Command\CleanOldArchivesCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ArchiveCleanTest extends KernelTestCase
{
    public function testCleanOldArchivesCommand(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find(CleanOldArchivesCommand::COMMAND_NAME);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Clean triggered!', $output);
    }
}
