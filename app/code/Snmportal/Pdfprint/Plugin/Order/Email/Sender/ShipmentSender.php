<?php

namespace Snmportal\Pdfprint\Plugin\Order\Email\Sender;

use Magento\Sales\Model\Order\Shipment;
use Snmportal\Pdfprint\Model\Template;
use Snmportal\Pdfprint\Plugin\AbstractPlugin;

class ShipmentSender extends AbstractPlugin
{
    /**
     * @param \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $caller
     * @param Shipment                                               $shipment
     * @param bool                                                   $forceSyncMode
     */
    public function beforeSend(
        \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $caller,
        Shipment $shipment,
        $forceSyncMode = false
    ) {
        if (!$shipment) {
            return;
        }
        $store = $shipment->getOrder()
                          ->getStore();
        $this->attachEmails($caller, $shipment, Template::TYPE_SHIPPING, $store);
    }
}
