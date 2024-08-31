<?php

namespace App\Controller;

use QuickChart;
use Knp\Snappy\Pdf;
use App\Entity\Report;
use App\Repository\ReportRepository;
use App\Repository\TemplateRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReportController extends AbstractController
{
    public function __construct(
        private TemplateRepository $templateRepository,
        private ReportRepository $reportRepository
        )
    {
    }

    #[Route('/report', name: 'app_report', methods: ["POST"], defaults: ['_secure_api' => true])]
    public function index(Request $request, Pdf $pdf): Response
    {
        // Récupération des données de la requête
        $data = json_decode($request->getContent(), true);
        $templateId = $data['templateId'] ?? null;

        // Vérification de l'existence du template et d'un titre
        $template = $this->templateRepository->findOneBy(["id" => $templateId]);

        if (!$template) {
            return new Response('Template not found', 404);
        }

        if (!isset($data['title'])) {
            return new Response('Title is missing in the request data', 400);
        }

        // Détermine le template et les données spécifiques à chaque type de document
        $filePath = '';
        switch ($template->getName()) {
            case 'Facture':
                $html = $this->renderView('invoice_template.html.twig', [
                    'invoiceNumber' => $data['invoiceNumber'],
                    'date' => $data['date'],
                    'clientName' => $data['clientName'],
                    'items' => $data['items'],
                    'total' => $data['total']
                ]);
                $filePath = './uploads/invoices/' . uniqid() . '.pdf';
                break;

            case 'Bulletin de Paie':
                $html = $this->renderView('pay_slip_template.html.twig', [
                    'employeeName' => $data['employeeName'],
                    'payPeriod' => $data['payPeriod'],
                    'employerName' => $data['employerName'],
                    'paymentDate' => $data['paymentDate'],
                    'baseSalary' => $data['baseSalary'],
                    'bonus' => $data['bonus'],
                    'socialSecurity' => $data['socialSecurity'],
                    'taxes' => $data['taxes'],
                    'netSalary' => $data['netSalary']
                ]);
                $filePath = './uploads/payslips/' . uniqid() . '.pdf';
                break;

            case 'Rapport de Ventes':

                // Générer le graphique
                $chart = new QuickChart([
                    'width' => 500,
                    'height' => 300,
                ]);
                $chart->setConfig([
                    'type' => 'bar',
                    'data' => [
                        'labels' => array_column($data['sales'], 'productName'),
                        'datasets' => [
                            [
                                'label' => 'Quantité Vendue',
                                'data' => array_column($data['sales'], 'quantity'),
                            ]
                        ]
                    ]
                ]);
                $chartImagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/charts/' . uniqid() . '.png';
                $chart->toFile($chartImagePath);

                $html = $this->renderView('sales_report_template.html.twig', [
                    'period' => $data['period'],
                    'sales' => $data['sales'],
                    'chartUrl' => $chartImagePath,
                    'totalSales' => $data['totalSales'],
                    'totalProducts' => $data['totalProducts']
                ]);
                $filePath = './uploads/salesReports/' . uniqid() . '.pdf';
                break;

            default:
                return new Response('Invalid template type', 400);
        }

        // Génération du PDF à partir du HTML
        $pdfContent = $pdf->getOutputFromHtml($html, [
            'disable-javascript' => true, 
            'no-background' => true,
            'enable-local-file-access' => true, 
        ]);

        // Enregistre le PDF dans un fichier
        file_put_contents($filePath, $pdfContent);

        // Sauvegarde des informations du rapport dans la base de données
        $report = new Report();
        $report->setTitle($data['title']);
        $report->setFilePath($filePath);
        $this->reportRepository->save($report, true);

        // Retourner le PDF en réponse
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $template->getName() . '.pdf"',
        ]);
    }
}
