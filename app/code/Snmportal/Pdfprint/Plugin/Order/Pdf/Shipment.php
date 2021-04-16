<?php

namespace Snmportal\Pdfprint\Plugin\Order\Pdf;

use Closure;
use Magento\Sales\Model\Order\Pdf\Shipment as MShipment;
use Snmportal\Pdfprint\Model\Template;
use Snmportal\Pdfprint\Plugin\AbstractPlugin;

class Shipment extends AbstractPlugin
{
    public function aroundGetPdf(
        MShipment $subject,
        Closure $proceed,
        $documents = []
    ) {
        if (!$this->_pdfHelper->getIsDefaultPrint()) {
            $ball = true;
            foreach ($documents as $document) {
                if (!($document instanceof \Magento\Sales\Model\Order\Shipment)) {
                    $ball = false;
                }
            }
            if ($ball) {
                $engine = $this->getEngine(Template::TYPE_SHIPPING, null);
                if ($engine) {
                    return $engine->getPdf($documents);
                }
            }
        }

        return $proceed($documents);
    }
}
