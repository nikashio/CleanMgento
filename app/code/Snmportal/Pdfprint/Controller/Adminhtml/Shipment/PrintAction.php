<?php

namespace Snmportal\Pdfprint\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Snmportal\Pdfprint\Controller\Adminhtml\FileFactory;
use Snmportal\Pdfprint\Helper\Template;
use Snmportal\Pdfprint\Model\Pdf\ShipmentFactory;

class PrintAction extends \Snmportal\Pdfprint\Controller\Adminhtml\AbstractController\PrintAction
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var ShipmentFactory
     */
    protected $shipmentPdfFactory;

    /**
     * @var Template
     */
    protected $_pdfHelper;

    /**
     * @param Context                     $context
     * @param FileFactory                 $fileFactory
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ShipmentFactory             $shipmentPdfFactory
     * @param Template                    $pdfHelper
     * @param DateTime                    $date
     *
     * @internal param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        ShipmentRepositoryInterface $shipmentRepository,
        ShipmentFactory $shipmentPdfFactory,
        Template $pdfHelper,
        DateTime $date
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->shipmentRepository = $shipmentRepository;
        $this->shipmentPdfFactory = $shipmentPdfFactory;
        $this->_pdfHelper = $pdfHelper;
        $this->date = $date;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $Id = $this->getRequest()
                   ->getParam('shipment_id');
        $tplId = $this->getRequest()
                      ->getParam('tplid');
        if ($Id) {
            $shipment = $this->shipmentRepository->get($Id);
            if ($shipment) {
                return $this->_pdfHelper->downloadPDF(
                    $this->shipmentPdfFactory->create()
                                             ->setRenderTemplateId($tplId),
                    $shipment
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
