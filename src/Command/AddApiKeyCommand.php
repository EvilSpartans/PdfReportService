<?php

namespace App\Command;

use App\Entity\ApiKey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:add-api-key')]
class AddApiKeyCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a new API key')
            ->addArgument('clientName', InputArgument::REQUIRED, 'The name of the client')
            ->addArgument('apiKey', InputArgument::REQUIRED, 'The API key');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $clientName = $input->getArgument('clientName');
        $apiKey = $input->getArgument('apiKey');

        $apiKeyEntity = new ApiKey();
        $apiKeyEntity->setClientName($clientName);
        $apiKeyEntity->setSecretKey($apiKey);
        $apiKeyEntity->setCreatedAt(new \DateTime());

        $this->em->persist($apiKeyEntity);
        $this->em->flush();

        $io->success(sprintf('API Key %s added for client %s', $apiKey, $clientName));

        return Command::SUCCESS;
    }
}
