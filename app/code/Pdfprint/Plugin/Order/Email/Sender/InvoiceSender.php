<?php

namespace Snmportal\Pdfprint\Plugin\Order\Email\Sender;

use Magento\Sales\Model\Order\Invoice;
use Snmportal\Pdfprint\Model\Template;
use Snmportal\Pdfprint\Plugin\AbstractPlugin;

class InvoiceSender extends AbstractPlugin
{
    /**
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $caller
     * @param Invoice                                               $invoice
     * @param bool                                                  $forceSyncMode
     */
    public function beforeSend(
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $caller,
        Invoice $invoice,
        $forceSyncMode = false
    ) {
        if (!$invoice) {
            return;
        }
        $store = $invoice->getOrder()
                         ->getStore();
        $this->attachEmails($caller, $invoice, Template::TYPE_INVOICE, $store);
    }
}
