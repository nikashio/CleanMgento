<?php

namespace Snmportal\Pdfprint\Plugin;

use Magento\Sales\Model\Order\Email\Sender;
use Snmportal\Pdfprint\Helper\Template;
use Snmportal\Pdfprint\Model\Pdf\Base;

class AbstractPlugin
{
    /**
     * @var Template
     */
    protected $_pdfHelper;

    /**
     * AbstractPlugin constructor.
     *
     * @param Template $pdfHelper
     */
    public function __construct(
        Template $pdfHelper
    ) {
        $this->_pdfHelper = $pdfHelper;
    }

    /**
     * @param Sender $caller
     * @param        $document
     * @param        $typ
     * @param        $store
     */
    protected function attachEmails(Sender $caller, $document, $typ, $store)
    {
        $this->_pdfHelper->attachEmails($caller, $document, $typ, $store);
    }

    /**
     * @param $typ
     * @param $store
     *
     * @return null|Base
     */
    protected function getEngine($typ, $store)
    {
        return $this->_pdfHelper->getEngine($typ, $store);
    }
}
