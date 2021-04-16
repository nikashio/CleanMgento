<?php

namespace Snmportal\Pdfprint\Plugin\Order\Pdf;

use Closure;
use Magento\Sales\Model\Order\Pdf\Invoice as MInvoice;
use Snmportal\Pdfprint\Model\Template;
use Snmportal\Pdfprint\Plugin\AbstractPlugin;

class Invoice extends AbstractPlugin
{
    public function aroundGetPdf(
        MInvoice $subject,
        Closure $proceed,
        $documents = []
    ) {
        if (!$this->_pdfHelper->getIsDefaultPrint()) {
            $ballInvoice = true;
            foreach ($documents as $document) {
                if (!($document instanceof \Magento\Sales\Model\Order\Invoice)) {
                    $ballInvoice = false;
                }
            }
            if ($ballInvoice) {
                $engine = $this->getEngine(Template::TYPE_INVOICE, null);
                if ($engine) {
                    return $engine->getPdf($documents);
                }
            }
        }

        return $proceed($documents);
    }
}
