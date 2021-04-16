<?php
/**
 *
 * Copyright © 2015 SNM-Portal.com. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Snmportal\Pdfprint\Controller\Adminhtml\Template;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Snmportal\Pdfprint\Model\Template;

class Export extends \Snmportal\Pdfprint\Controller\Adminhtml\Template
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        /** @var Template $model */
        $model = $this->_objectManager->create('Snmportal\Pdfprint\Model\Template');
        $id = $this->getRequest()
                   ->getParam('template_id');
        if ($id) {
            $model->load($id);
            $content = $model->export();
            $this->getResponse()
                 ->setHttpResponseCode(200)
                 ->setHeader('Pragma', 'public', true)
                 ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                 ->setHeader('Content-type', 'text/plain', true)
                 ->setHeader('Content-Length', strlen($content), true)
                 ->setHeader(
                     'Content-Disposition',
                     'attachment; filename="' . $model->getTitle() . '.snmportal-pdfprint-template"',
                     true
                 )
                 ->setHeader('Last-Modified', date('r'), true);
            $this->getResponse()
                 ->sendHeaders();
            $this->getResponse()
                 ->setBody($content);
            $this->getResponse()
                 ->sendResponse();
//            echo $content;
            //          flush();
            //        exit(0);

        }
    }
}
