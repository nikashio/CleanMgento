<?php

namespace Snmportal\Pdfprint\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class Setup extends Action
{
    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Snmportal_Pdfprint::setup');
    }
}
