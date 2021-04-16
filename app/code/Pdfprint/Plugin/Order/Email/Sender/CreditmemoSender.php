<?php

namespace Snmportal\Pdfprint\Plugin\Order\Email\Sender;

use Magento\Sales\Model\Order\Creditmemo;
use Snmportal\Pdfprint\Model\Template;
use Snmportal\Pdfprint\Plugin\AbstractPlugin;

class CreditmemoSender extends AbstractPlugin
{
    /**
     * @param \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender $caller
     * @param Creditmemo                                               $creditmemo
     * @param bool                                                     $forceSyncMode
     */
    public function beforeSend(
        \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender $caller,
        Creditmemo $creditmemo,
        $forceSyncMode = false
    ) {
        if (!$creditmemo) {
            return;
        }
        $store = $creditmemo->getOrder()
                            ->getStore();
        $this->attachEmails($caller, $creditmemo, Template::TYPE_CREDITMEMO, $store);
    }
}
