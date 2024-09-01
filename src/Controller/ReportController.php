<?php

namespace App\Controller;

use App\Service\ReportService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReportController extends AbstractController
{
    #[Route('/report', name: 'app_report', methods: ["POST"], defaults: ['_secure_api' => true])]
    public function index(Request $request, ReportService $service): Response
    {
        $result = $service->generateReport($request);

        if (isset($result['error'])) {
            return new Response($result['error'], $result['statusCode']);
        }

        return new Response($result['pdfContent'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $result['templateName'] . '.pdf"',
        ]);
    }
}
