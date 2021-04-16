<?php

namespace Snmportal\Pdfprint\Controller\Adminhtml\Template;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use RuntimeException;
use Snmportal\Pdfprint\Logger\Logger;
use Snmportal\Pdfprint\Model\Template;

class Save extends Action
{
    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var Logger
     */
    protected $_snmLogger;

    /**
     * @param Logger            $snmLogger
     * @param Action\Context    $context
     * @param PostDataProcessor $dataProcessor
     */
    public function __construct(
        Logger $snmLogger,
        Action\Context $context,
        PostDataProcessor $dataProcessor
    ) {
        $this->_snmLogger = $snmLogger;
        $this->dataProcessor = $dataProcessor;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return ResultInterface
     */
    public function execute()
    {

        $data = $this->getRequest()
                     ->getPostValue();
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->dataProcessor->filter($data);
            /** @var Template $model */
            $model = $this->_objectManager->create('Snmportal\Pdfprint\Model\Template');

            $id = $this->getRequest()
                       ->getParam('template_id');
            if ($id) {
                $model->load($id);
            }
            //$this->_snmLogger->info('Current Object data before:',$model->getData());
            $model->setData($data);
            $bimported = false;
            if ($this->getRequest()
                     ->getParam('type', null) == 'import_file') {
                try {
                    $model->import();
                    $bimported = true;
                } catch (Exception $e) {
                    $this->messageManager->addErrorMessage(
                        __('Can\'t load import template. Please use "*.snmportal-pdfprint-template" file')
                    );

                    return $resultRedirect->setPath('*/*/');
                }
            }

            $this->_eventManager->dispatch(
                'snmportal_pdfprint_template_prepare_save',
                ['model' => $model, 'request' => $this->getRequest()]
            );

            if (!$this->dataProcessor->validate($data)) {
                return $resultRedirect->setPath('*/*/edit', ['template_id' => $model->getId(), '_current' => true]);
            }

            try {
                $model->uploadSource();
                if ($bimported || $this->getRequest()
                                       ->getParam('save') != 'no') {
                    $model->save();
                    $this->messageManager->addSuccessMessage(__('You saved this template.'));
                } else {
                    $this->_getSession()
                         ->setFormData($data);
                }
//                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($back = $this->getRequest()
                                 ->getParam('back')) {
                    $this->_snmLogger->info('Back to :' . $back);

                    return $resultRedirect->setPath(
                        '*/*/' . $back,
                        ['template_id' => $model->getId(), '_current' => true]
                    );
                }

                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the template.'));
            }

//            $this->_getSession()->setFormData($data);
            //return $resultRedirect->setPath('*/*/edit', ['page_id' => $this->getRequest()->getParam('template_id')]);
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Snmportal_Pdfprint::template_save');
    }
}
