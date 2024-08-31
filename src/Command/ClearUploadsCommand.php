<?php

namespace App\Command;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:clear-uploads')]
class ClearUploadsCommand extends Command
{
    private $filesystem;
    private $uploadsDirs;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
        $baseUploadsDir = __DIR__ . '/../../public/uploads';

        $this->uploadsDirs = [
            $baseUploadsDir . '/charts',
            $baseUploadsDir . '/invoices',
            $baseUploadsDir . '/payslips',
            $baseUploadsDir . '/salesReports',
        ];

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Clear all files in the specified uploads subdirectories');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->uploadsDirs as $dir) {
            if ($this->filesystem->exists($dir)) {
                $finder = new Finder();
                $finder->files()->in($dir);

                foreach ($finder as $file) {
                    $this->filesystem->remove($file->getRealPath());
                }

                $io->success('All files in the directory ' . $dir . ' have been deleted.');
            } else {
                $io->error('The directory ' . $dir . ' does not exist.');
            }
        }

        return Command::SUCCESS;
    }
}
