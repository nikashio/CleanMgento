<?php

namespace Snmportal\Pdfprint\Plugin;

use Magento\Framework\App\Action\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface;
use Snmportal\Pdfprint\Helper\Template;

class AbstractFrontendPlugin extends AbstractPlugin
{
    /**
     * @var OrderViewAuthorizationInterface
     */
    protected $orderAuthorization;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * AbstractFrontendPlugin constructor.
     *
     * @param Context                             $context
     * @param OrderViewAuthorizationInterface     $orderAuthorization
     * @param Template $pdfHelper
     */
    public function __construct(
        Context $context,
        OrderViewAuthorizationInterface $orderAuthorization,
        Template $pdfHelper
    ) {
        $this->orderAuthorization = $orderAuthorization;
        $this->_objectManager = $context->getObjectManager();
        parent::__construct($pdfHelper);
    }
}
