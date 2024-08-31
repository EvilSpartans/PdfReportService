<?php

namespace App\Command;

use App\Entity\ApiKey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:list-api-keys')]
class ListApiKeysCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('List all API keys');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $apiKeys = $this->em->getRepository(ApiKey::class)->findAll();

        if (empty($apiKeys)) {
            $io->warning('No API keys found.');
            return Command::SUCCESS;
        }

        $io->title('List of API Keys');
        $tableRows = [];
        foreach ($apiKeys as $apiKey) {
            $tableRows[] = [
                'Client Name' => $apiKey->getClientName(),
                'API Key' => $apiKey->getSecretKey(),
                'Created At' => $apiKey->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }
        $io->table(['Client Name', 'API Key', 'Created At'], $tableRows);

        return Command::SUCCESS;
    }
}
