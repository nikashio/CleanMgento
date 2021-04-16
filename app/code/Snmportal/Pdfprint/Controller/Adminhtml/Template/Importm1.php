<?php

namespace Snmportal\Pdfprint\Controller\Adminhtml\Template;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Snmportal\Pdfprint\Model\Template;
use Snmportal\Pdfprint\Model\Template\SetupFactory;

/**
 * Class MassDelete
 */
class Importm1 extends \Snmportal\Pdfprint\Controller\Adminhtml\Template
{
    /**
     * @var Forward
     */
    protected $resultForwardFactory;

    protected $setupFactory;

    /**
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param SetupFactory $setupFactory
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        SetupFactory $setupFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->setupFactory = $setupFactory;
        parent::__construct($context);
    }

    /**
     * Forward to edit
     *
     * @return Forward
     */
    public function execute()
    {
        /** @var Template $model */
        $model = $this->setupFactory->create();
        try {
            if ($model->importM1Data()) {
                $this->messageManager->addSuccess(__('The Magento1 data has been imported.'));
            } else {
                $this->messageManager->addWarning(
                    __(
                        'No Magento1 data could be found. <a target="_blank" href="http://www.snm-portal.com/magento2_setup#importm1">Help</a>'
                    )
                );
            }
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
            //$this->messageManager->addException($e, __('Something went wrong while saving the template.'));
        }
        /** @var Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();

        return $resultForward->forward('index');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Snmportal_Pdfprint::template_save');
    }
}
