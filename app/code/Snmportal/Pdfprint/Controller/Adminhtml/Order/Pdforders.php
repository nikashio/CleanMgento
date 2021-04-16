<?php

namespace Snmportal\Pdfprint\Controller\Adminhtml\Order;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Snmportal\Pdfprint\Controller\Adminhtml\FileFactory;
use Snmportal\Pdfprint\Helper\Template;
use Snmportal\Pdfprint\Model\Pdf\OrderFactory;

//class Pdforders extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
class Pdforders extends Action
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var DateTime
     */
    protected $date;

    protected $filter;

    /**
     * @var Template
     */
    protected $_pdfHelper;

    protected $orderPdfFactory;

    /**
     * @param Context           $context
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     * @param FileFactory       $fileFactory
     * @param OrderFactory      $orderPdfFactory
     * @param Template          $pdfHelper
     * @param DateTime          $date
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        FileFactory $fileFactory,
        OrderFactory $orderPdfFactory,
        Template $pdfHelper,
        DateTime $date
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->fileFactory = $fileFactory;
        $this->orderPdfFactory = $orderPdfFactory;
        $this->date = $date;
        $this->_pdfHelper = $pdfHelper;
        parent::__construct($context);//, $filter);
        $this->filter = $filter;
    }

    /**
     * @return ResponseInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $tplId = $this->getRequest()
                      ->getParam('tplid');
        $pdfCreator = $this->orderPdfFactory->create();
        if ($tplId) {
            $pdfCreator->setRenderTemplateId($tplId);
        }

        $cc = $this->collectionFactory->create();
        $collection = $this->filter->getCollection($cc);
        //$this->context->getPageLayout();
        $pdf = $pdfCreator->getPdf($collection);
        $date = $this->date->date('Y-m-d_H-i-s');

        return $this->fileFactory->create(
            'orders' . $date . '.pdf',
            $pdf->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_order');
    }

    /**
     * Print selected orders
     *
     * @param AbstractCollection $collection
     *
     * @return ResponseInterface
     * @throws Exception
     */
    protected function massAction(AbstractCollection $collection)
    {
        $tplId = $this->getRequest()
                      ->getParam('tplid');
        $pdf = $this->orderPdfFactory->create();
        if ($tplId) {
            $pdf->setRenderTemplateId($tplId);
        }
        $pdf = $pdf->getPdf($collection);

        /*
        if (!isset($pdf)) {
            $pdf = $this->orderPdfFactory->create()->getPdf($collection);
        } else {
            $pages = $this->orderPdfFactory->create()->getPdf($collection);
            $pdf->pages = array_merge($pdf->pages, $pages->pages);
        }
        */
        $date = $this->date->date('Y-m-d_H-i-s');

        return $this->fileFactory->create(
            'orders' . $date . '.pdf',
            $pdf->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
