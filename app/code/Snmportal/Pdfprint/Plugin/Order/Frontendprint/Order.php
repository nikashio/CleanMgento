<?php

namespace Snmportal\Pdfprint\Plugin\Order\Frontendprint;

use Closure;
use Magento\Framework\App\ResponseInterface;
use Magento\Sales\Controller\AbstractController\PrintAction;
use Snmportal\Pdfprint\Model\Template;
use Snmportal\Pdfprint\Plugin\AbstractFrontendPlugin;

class Order extends AbstractFrontendPlugin
{
    /**
     * @param PrintAction $subject
     * @param Closure     $proceed
     *
     * @return ResponseInterface
     */
    public function aroundExecute(
        PrintAction $subject,
        Closure $proceed
    ) {
        if ($this->_pdfHelper->isSetFlag('snmportal_pdfprint/order/use_pdf_frontend')) {
            $orderId = (int)$subject->getRequest()
                                    ->getParam('order_id');
            if ($orderId) {
                $order = $this->_pdfHelper->createObj('Magento\Sales\Api\OrderRepositoryInterface')
                                          ->get($orderId);

                if ($order && $this->orderAuthorization->canView($order)) {
                    $engine = $this->getEngine(Template::TYPE_ORDER, $order->getStore());
                    if ($engine) {
                        return $this->_pdfHelper->downloadPDF($engine, $order);
                    }
                }
            }
        }

        return $proceed();
    }
}
