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

#[AsCommand(name: 'app:delete-api-key')]
class DeleteApiKeyCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Delete an API key by client name')
            ->addArgument('clientName', InputArgument::REQUIRED, 'The client name associated with the API key to delete');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $clientName = $input->getArgument('clientName');

        $apiKey = $this->em->getRepository(ApiKey::class)->findOneBy(['clientName' => $clientName]);

        if (!$apiKey) {
            $io->error(sprintf('No API Key found for client "%s".', $clientName));
            return Command::FAILURE;
        }

        $this->em->remove($apiKey);
        $this->em->flush();

        $io->success(sprintf('API Key for client "%s" has been deleted.', $clientName));

        return Command::SUCCESS;
    }
}
