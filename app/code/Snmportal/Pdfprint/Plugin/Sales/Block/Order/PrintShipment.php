<?php

namespace Snmportal\Pdfprint\Plugin\Sales\Block\Order;

use Snmportal\Pdfprint\Plugin\AbstractPlugin;

class PrintShipment extends AbstractPlugin
{
    /*
        public function around__call(\Magento\Sales\Block\Order\PrintShipment $caller,\Closure $proceed,$method, $args)
        {
            if ( $method == 'isPagerDisplayed')
            {
                return false;
            }
            return $proceed($method, $args);
            return false;
        }
    */
}
