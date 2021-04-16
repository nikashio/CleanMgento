<?php

namespace Snmportal\Pdfprint\Controller\Adminhtml\Template;

use Exception;
use Magento\Framework\Controller\Result\Redirect;
use Snmportal\Pdfprint\Model\Template;

class Duplicate extends Edit
{
    /**
     * Edit Template
     *
     * @return Redirect
     * @throws Exception
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        /** @var Template $model */
        $model = $this->_objectManager->create('Snmportal\Pdfprint\Model\Template');

        $id = $this->getRequest()
                   ->getParam('template_id');
        if ($id) {
            $model->load($id);
        }
        if (!$model->getId()) {
            return $resultRedirect->setPath('*/*/');
        }


        $model->setId(null);
        $model->setTitle(__('Copy ') . $model->getTitle());

        // 3. Set entered data if was error when we do save
//        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        //      if (!empty($data)) {
        //        $model->setData($data);
        //  }

        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('snmportal_pdfprint_template', $model);

        $model->save();
        // 5. Build edit form
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Template') : __('New Template'),
            $id ? __('Edit Template') : __('New Template')
        );
        $resultPage->getConfig()
                   ->getTitle()
                   ->prepend(__('Templates'));
        $resultPage->getConfig()
                   ->getTitle()
                   ->prepend($model->getId() ? $model->getTitle() : __('New Template'));

        return $resultPage;
    }
}
