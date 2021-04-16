<?php

namespace Snmportal\Pdfprint\Controller\Adminhtml\Invoice;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Snmportal\Pdfprint\Controller\Adminhtml\FileFactory;
use Snmportal\Pdfprint\Helper\Template;
use Snmportal\Pdfprint\Model\Pdf\InvoiceFactory;

class PrintAction extends \Snmportal\Pdfprint\Controller\Adminhtml\AbstractController\PrintAction
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var InvoiceFactory
     */
    protected $invoicePdfFactory;

    /**
     * @var Template
     */
    protected $_pdfHelper;

    /**
     * @param Context                    $context
     * @param FileFactory                $fileFactory
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param InvoiceFactory             $invoicePdfFactory
     * @param Template                   $pdfHelper
     * @param DateTime                   $date
     *
     * @internal param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        InvoiceRepositoryInterface $invoiceRepository,
        InvoiceFactory $invoicePdfFactory,
        Template $pdfHelper,
        DateTime $date
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoicePdfFactory = $invoicePdfFactory;
        $this->_pdfHelper = $pdfHelper;
        $this->date = $date;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $Id = $this->getRequest()
                   ->getParam('invoice_id');
        $tplId = $this->getRequest()
                      ->getParam('tplid');
        if ($Id) {
            $invoice = $this->invoiceRepository->get($Id);
            if ($invoice) {
                return $this->_pdfHelper->downloadPDF(
                    $this->invoicePdfFactory->create()
                                            ->setRenderTemplateId($tplId),
                    $invoice
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
        return $this->_authorization->isAllowed('Magento_Sales::sales_invoice');
    }
}
