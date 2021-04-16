<?php

namespace Snmportal\Pdfprint\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Widget\Model\Widget\Instance;

abstract class Template extends Action
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Snmportal_Pdfprint::template');
    }

    /**
     * Init widget instance object and set it to registry
     *
     * @return Instance|boolean
     */
    protected function _initModelInstance()
    {
        /** @var $widgetInstance Instance */
        $model = $this->_objectManager->create('Snmportal\Pdfprint\Model\Template');
        $id = $this->getRequest()
                   ->getParam('template_id');
        // $type = $this->getRequest()->getParam('type', null);
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('Please specify a correct template.'));

                return false;
            }
        } else {
            $data = $this->_getSession()
                         ->getFormData();
            if ($data) {
                $model->setData($data);
            }
        }
        $this->_getSession()
             ->setFormData(null);
        $this->_coreRegistry->register('snmportal_pdfprint_template', $model);

        return $model;
    }
}
