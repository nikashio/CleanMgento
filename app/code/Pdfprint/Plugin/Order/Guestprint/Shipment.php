<?php

namespace Snmportal\Pdfprint\Plugin\Order\Guestprint;

use Closure;
use Magento\Framework\App\ResponseInterface;
use Magento\Sales\Controller\AbstractController\PrintInvoice;
use Snmportal\Pdfprint\Model\Template;
use Snmportal\Pdfprint\Plugin\AbstractFrontendPlugin;

class Shipment extends AbstractFrontendPlugin
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
        if ($this->_pdfHelper->isSetFlag('snmportal_pdfprint/creditmemo/use_pdf_frontend')) {
            $shipmentId = (int)$subject->getRequest()
                                       ->getParam('shipment_id');
            if ($shipmentId) {
                $shipment = $this->_pdfHelper->createObj('Magento\Sales\Api\ShipmentRepositoryInterface')
                                             ->get($shipmentId);
                $order = $shipment->getOrder();
                if ($this->orderAuthorization->canView($order)) {
                    $engine = $this->getEngine(Template::TYPE_SHIPPING, $order->getStore());
                    if ($engine) {
                        return $this->_pdfHelper->downloadPDF($engine, $shipment);
                    }
                }
            }
        }

        return $proceed();
    }
}
