<?php

namespace Snmportal\Pdfprint\Controller\Adminhtml\Template;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;

class Delete extends Action
{
    /**
     * Delete action
     *
     * @return Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()
                   ->getParam('template_id');
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                // init model and delete
                $model = $this->_objectManager->create('Snmportal\Pdfprint\Model\Template');
                $model->load($id);
                $title = $model->getTitle();
                $model->delete();
                // display success message
                $this->messageManager->addSuccess(__('The template has been deleted.'));
                // go to grid
                $this->_eventManager->dispatch(
                    'adminhtml_snmportal_pdfprint_template_on_delete',
                    ['title' => $title, 'status' => 'success']
                );

                return $resultRedirect->setPath('*/*/');
            } catch (Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_snmportal_pdfprint_template_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());

                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['template_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a template to delete.'));

        // go to grid
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Snmportal_Pdfprint::template_delete');
    }
}
