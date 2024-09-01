<?php

namespace App\Service;

use QuickChart;
use Knp\Snappy\Pdf;
use App\Entity\Report;
use App\Repository\ReportRepository;
use App\Repository\TemplateRepository;
use Twig\Environment as TwigEnvironment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ReportService
{
    public function __construct(
        private TemplateRepository $templateRepository,
        private ReportRepository $reportRepository,
        private TwigEnvironment $twig,
        private ParameterBagInterface $params,
        private Pdf $pdf
    ) {}

    public function generateReport(Request $request): array
    {
        $data = json_decode($request->getContent(), true);
        $templateId = $data['templateId'] ?? null;

        $template = $this->templateRepository->findOneBy(["id" => $templateId]);

        if (!$template) {
            return ['error' => 'Template not found', 'statusCode' => 404];
        }

        if (!isset($data['title'])) {
            return ['error' => 'Title is missing in the request data', 'statusCode' => 400];
        }

        $filePath = '';
        switch ($template->getName()) {
            case 'Facture':
                $html = $this->twig->render('invoice_template.html.twig', [
                    'invoiceNumber' => $data['invoiceNumber'],
                    'date' => $data['date'],
                    'clientName' => $data['clientName'],
                    'items' => $data['items'],
                    'total' => $data['total']
                ]);
                $filePath = $this->params->get('kernel.project_dir') . '/public/uploads/invoices/' . uniqid() . '.pdf';
                break;

            case 'Bulletin de Paie':
                $html = $this->twig->render('pay_slip_template.html.twig', [
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
                $filePath = $this->params->get('kernel.project_dir') . '/public/uploads/payslips/' . uniqid() . '.pdf';
                break;

            case 'Rapport de Ventes':
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
                $chartImagePath = $this->params->get('kernel.project_dir') . '/public/uploads/charts/' . uniqid() . '.png';
                $chart->toFile($chartImagePath);

                $html = $this->twig->render('sales_report_template.html.twig', [
                    'period' => $data['period'],
                    'sales' => $data['sales'],
                    'chartUrl' => $chartImagePath,
                    'totalSales' => $data['totalSales'],
                    'totalProducts' => $data['totalProducts']
                ]);
                $filePath = $this->params->get('kernel.project_dir') . '/public/uploads/salesReports/' . uniqid() . '.pdf';
                break;

            default:
                return ['error' => 'Invalid template type', 'statusCode' => 400];
        }

        $pdfContent = $this->pdf->getOutputFromHtml($html, [
            'disable-javascript' => true,
            'no-background' => true,
            'enable-local-file-access' => true,
        ]);

        file_put_contents($filePath, $pdfContent);

        $report = new Report();
        $report->setTitle($data['title']);
        $report->setFilePath($filePath);
        $this->reportRepository->save($report, true);

        return [
            'pdfContent' => $pdfContent,
            'templateName' => $template->getName(),
            'filePath' => $filePath
        ];
    }
}
