<?php

namespace Snmportal\Pdfprint\Plugin\Order\Frontendprint;

use Closure;
use Magento\Framework\App\ResponseInterface;
use Magento\Sales\Controller\AbstractController\PrintInvoice;
use Snmportal\Pdfprint\Model\Template;
use Snmportal\Pdfprint\Plugin\AbstractFrontendPlugin;

class Invoice extends AbstractFrontendPlugin
{
    /**
     * @param PrintInvoice $subject
     * @param Closure      $proceed
     *
     * @return ResponseInterface
     */
    public function aroundExecute(
        PrintInvoice $subject,
        Closure $proceed
    ) {
        if ($this->_pdfHelper->isSetFlag('snmportal_pdfprint/invoice/use_pdf_frontend')) {
            $invoiceId = (int)$subject->getRequest()
                                      ->getParam('invoice_id');
            if ($invoiceId) {
                $invoice = $this->_pdfHelper->createObj('Magento\Sales\Api\InvoiceRepositoryInterface')
                                            ->get($invoiceId);
                $order = $invoice->getOrder();

                if ($this->orderAuthorization->canView($order)) {
                    $engine = $this->getEngine(Template::TYPE_INVOICE, $order->getStore());
                    if ($engine) {
                        return $this->_pdfHelper->downloadPDF($engine, $invoice);
                    }
                }
            } else {
                $orderId = (int)$subject->getRequest()
                                        ->getParam('order_id');
                if ($orderId) {
                    $order = $this->_objectManager->create('Magento\Sales\Model\Order')
                                                  ->load($orderId);
                    if ($this->orderAuthorization->canView($order)) {

                        $engine = $this->getEngine(Template::TYPE_INVOICE, $order->getStore());
                        if ($engine) {
                            $docs = [];
                            foreach ($order->getInvoiceCollection() as $invoice) {
                                $docs[] = $invoice;
                            }

                            return $this->_pdfHelper->downloadPDF($engine, $docs);
                        }
                    }
                }
            }
        }

        return $proceed();
    }
}
