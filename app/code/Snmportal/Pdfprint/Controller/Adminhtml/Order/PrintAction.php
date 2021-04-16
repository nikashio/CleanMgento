<?php

namespace Snmportal\Pdfprint\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\OrderRepositoryInterface;
use Snmportal\Pdfprint\Controller\Adminhtml\FileFactory;
use Snmportal\Pdfprint\Helper\Template;
use Snmportal\Pdfprint\Model\Pdf\OrderFactory;

class PrintAction extends \Snmportal\Pdfprint\Controller\Adminhtml\AbstractController\PrintAction
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var OrderFactory
     */
    protected $orderPdfFactory;

    /**
     * @var Template
     */
    protected $_pdfHelper;

    /**
     * @param Context                  $context
     * @param FileFactory              $fileFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderFactory             $orderPdfFactory
     * @param Template                 $pdfHelper
     * @param DateTime                 $date
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        OrderRepositoryInterface $orderRepository,
        OrderFactory $orderPdfFactory,
        Template $pdfHelper,
        DateTime $date
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->orderRepository = $orderRepository;
        $this->orderPdfFactory = $orderPdfFactory;
        $this->_pdfHelper = $pdfHelper;
        $this->date = $date;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $orderId = $this->getRequest()
                        ->getParam('order_id');
        $tplId = $this->getRequest()
                      ->getParam('tplid');
        if ($orderId) {
            $order = $this->orderRepository->get($orderId);
            if ($order) {
                return $this->_pdfHelper->downloadPDF(
                    $this->orderPdfFactory->create()
                                          ->setRenderTemplateId($tplId),
                    $order
                );
            }
        }

        return $this->resultRedirectFactory->create()
                                           ->setPath('sales/*/view');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_order');
    }
}
