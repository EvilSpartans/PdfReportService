<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReportControllerTest extends WebTestCase
{
    public function testGenerateInvoicePdf()
    {
        $client = static::createClient();

        try {
            $client->request('POST', '/report', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
                'templateId' => 1,
                'title' => 'Test Invoice',
                'invoiceNumber' => 'INV-12345',
                'date' => '2024-08-01',
                'clientName' => 'Client Name',
                'items' => [
                    ['name' => 'Product A', 'price' => 50],
                    ['name' => 'Product B', 'price' => 30],
                ],
                'total' => 80
            ]));

            $this->assertResponseIsSuccessful();
            $this->assertResponseHeaderSame('Content-Type', 'application/pdf');
        } finally {
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
