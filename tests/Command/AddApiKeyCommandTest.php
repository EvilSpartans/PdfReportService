<?php

namespace App\Tests\Command;

use App\Command\AddApiKeyCommand;
use App\Entity\ApiKey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class AddApiKeyCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        self::bootKernel();
        $application = new Application(self::$kernel);
    
        $container = self::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
    
        $command = new AddApiKeyCommand($entityManager);
        $application->add($command);
    
        $fixedKey = 'test-key-12345';
    
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'clientName' => 'TestClient',
            'apiKey' => $fixedKey,
        ]);
    
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(sprintf('API Key %s added for client TestClient', $fixedKey), $output);
    
        $apiKey = $entityManager->getRepository(ApiKey::class)->findOneBy(['clientName' => 'TestClient']);
        $this->assertNotNull($apiKey);
        $this->assertEquals($fixedKey, $apiKey->getSecretKey());
    }
}
