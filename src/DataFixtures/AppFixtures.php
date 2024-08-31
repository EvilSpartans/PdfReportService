<?php

namespace App\DataFixtures;

use App\Entity\ApiKey;
use App\Entity\Template;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $filesystem = new Filesystem();

        $templates = [
            'Facture' => 'invoice_template.html.twig',
            'Bulletin de Paie' => 'pay_slip_template.html.twig',
            'Rapport de Ventes' => 'sales_report_template.html.twig',
        ];

        foreach ($templates as $name => $filename) {
            $filePath = __DIR__ . '/../../templates/' . $filename;

            try {
                if ($filesystem->exists($filePath)) {
                    $content = file_get_contents($filePath);

                    $template = new Template();
                    $template->setName($name);
                    $template->setTemplateContent($content);

                    $manager->persist($template);
                } else {
                    throw new \Exception(sprintf('Template file "%s" not found.', $filePath));
                }
            } catch (IOExceptionInterface $exception) {
                echo "An error occurred while reading the template file at " . $exception->getPath();
            }
        }

        $apiKey = new ApiKey();
        $apiKey->setClientName('EvilSpartans');
        $apiKey->setSecretKey("12345-abcde-67890-fghij");
        
        $manager->persist($apiKey);

        $manager->flush();
    }
}
