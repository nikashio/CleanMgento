<?php

namespace Snmportal\Pdfprint\Plugin\Order\Email\Sender;

use Magento\Sales\Model\Order;
use Snmportal\Pdfprint\Model\Template;
use Snmportal\Pdfprint\Plugin\AbstractPlugin;

class OrderSender extends AbstractPlugin
{
    /**
     * @param Order\Email\Sender\OrderSender $caller
     * @param Order                          $order
     * @param bool                           $forceSyncMode
     */
    public function beforeSend(
        Order\Email\Sender\OrderSender $caller,
        Order $order,
        $forceSyncMode = false
    ) {
        if (!$order) {
            return;
        }
        $store = $order->getStore();
        $this->attachEmails($caller, $order, Template::TYPE_ORDER, $store);
    }
}
