<?php

namespace Snmportal\Pdfprint\Plugin\Order\Guestprint;

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
            }
        }

        return $proceed();
    }
}
