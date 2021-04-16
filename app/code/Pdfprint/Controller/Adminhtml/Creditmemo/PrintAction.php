<?php

namespace Snmportal\Pdfprint\Controller\Adminhtml\Creditmemo;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Snmportal\Pdfprint\Controller\Adminhtml\FileFactory;
use Snmportal\Pdfprint\Helper\Template;
use Snmportal\Pdfprint\Model\Pdf\CreditmemoFactory;

class PrintAction extends \Snmportal\Pdfprint\Controller\Adminhtml\AbstractController\PrintAction
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var CreditmemoFactory
     */
    protected $creditmemoPdfFactory;

    /**
     * @var Template
     */
    protected $_pdfHelper;

    /**
     * @param Context                       $context
     * @param FileFactory                   $fileFactory
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param CreditmemoFactory             $creditmemoPdfFactory
     * @param Template                      $pdfHelper
     * @param DateTime                      $date
     *
     * @internal param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        CreditmemoRepositoryInterface $creditmemoRepository,
        CreditmemoFactory $creditmemoPdfFactory,
        Template $pdfHelper,
        DateTime $date
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->creditmemoPdfFactory = $creditmemoPdfFactory;
        $this->_pdfHelper = $pdfHelper;
        $this->date = $date;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $Id = $this->getRequest()
                   ->getParam('creditmemo_id');
        $tplId = $this->getRequest()
                      ->getParam('tplid');
        if ($Id) {
            $creditmemo = $this->creditmemoRepository->get($Id);
            if ($creditmemo) {
                return $this->_pdfHelper->downloadPDF(
                    $this->creditmemoPdfFactory->create()
                                               ->setRenderTemplateId($tplId),
                    $creditmemo
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
        return $this->_authorization->isAllowed('Magento_Sales::sales_creditmemo');
    }
}
