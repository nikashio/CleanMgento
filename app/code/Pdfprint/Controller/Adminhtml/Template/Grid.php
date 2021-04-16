<?php

namespace Snmportal\Pdfprint\Controller\Adminhtml\Template;

use Snmportal\Pdfprint\Controller\Adminhtml\Template;

class Grid extends Template
{
    /**
     * Managing newsletter grid
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
