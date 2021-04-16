<?php

namespace Snmportal\Pdfprint\Plugin\Order\Frontendprint;

use Closure;
use Magento\Framework\App\ResponseInterface;
use Magento\Sales\Controller\AbstractController\PrintCreditmemo;
use Snmportal\Pdfprint\Model\Template;
use Snmportal\Pdfprint\Plugin\AbstractFrontendPlugin;

class Creditmemo extends AbstractFrontendPlugin
{
    /**
     * @param PrintCreditmemo $subject
     * @param Closure         $proceed
     *
     * @return ResponseInterface
     */
    public function aroundExecute(
        PrintCreditmemo $subject,
        Closure $proceed
    ) {
        if ($this->_pdfHelper->isSetFlag('snmportal_pdfprint/creditmemo/use_pdf_frontend')) {
            $creditmemoId = (int)$subject->getRequest()
                                         ->getParam('creditmemo_id');
            if ($creditmemoId) {
                $creditmemo = $this->_pdfHelper->createObj('Magento\Sales\Api\CreditmemoRepositoryInterface')
                                               ->get($creditmemoId);
                $order = $creditmemo->getOrder();
                if ($this->orderAuthorization->canView($order)) {
                    $engine = $this->getEngine(Template::TYPE_CREDITMEMO, $order->getStore());
                    if ($engine) {
                        return $this->_pdfHelper->downloadPDF($engine, $creditmemo);
                    }
                }
            }
        }

        return $proceed();
    }
}
