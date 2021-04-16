<?php

namespace Snmportal\Pdfprint\Plugin\Order\Pdf;

use Closure;
use Magento\Sales\Model\Order\Pdf\Creditmemo as MCreditmemo;
use Snmportal\Pdfprint\Model\Template;
use Snmportal\Pdfprint\Plugin\AbstractPlugin;

class Creditmemo extends AbstractPlugin
{
    public function aroundGetPdf(
        MCreditmemo $subject,
        Closure $proceed,
        $documents = []
    ) {
        if (!$this->_pdfHelper->getIsDefaultPrint()) {
            $ball = true;
            foreach ($documents as $document) {
                if (!($document instanceof \Magento\Sales\Model\Order\Creditmemo)) {
                    $ball = false;
                }
            }
            if ($ball) {
                $engine = $this->getEngine(Template::TYPE_CREDITMEMO, null);
                if ($engine) {
                    return $engine->getPdf($documents);
                }
            }
        }

        return $proceed($documents);
    }
}
