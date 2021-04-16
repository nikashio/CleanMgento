<?php

namespace Snmportal\Pdfprint\Plugin\Mail;

use Closure;
use Magento\Framework\Mail\TransportInterface;
use Snmportal\Pdfprint\Helper\Email;

class TransportInterfacePlugin
{
    /**
     * @var Email
     */
    private $helper;

    public function __construct(
        Email $helper
    ) {


        $this->helper = $helper;
    }

    public function beforeSendMessageXXX(
        TransportInterface $subject
    ) {
        $this->helper->addAttachments($subject);
    }

//Problem mit Mageplaza/Smtp muss vorher aufgerufen werden
    public function aroundSendMessage(
        TransportInterface $subject,
        Closure $proceed
    ) {
        $this->helper->addAttachments($subject);
        $proceed();
    }
}
