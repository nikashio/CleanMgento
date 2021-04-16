<?php
/**
 *
 * Copyright © 2015 SNM-Portal.com. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Snmportal\Pdfprint\Controller\Adminhtml\Template;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Snmportal\Pdfprint\Controller\Adminhtml\Template;

class Index extends Template
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context     $context
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
        if ($this->getRequest()
                 ->getParam('ajax')) {
            $this->_forward('grid');

            return;
        }

        $this->_view->loadLayout();

        $this->_setActiveMenu('Snmportal_Pdfprint::template');
        $this->_view->getPage()
                    ->getConfig()
                    ->getTitle()
                    ->prepend(__('Template'));

        $this->_addBreadcrumb(__('PDFPRINT'), __('PDFPRINT'));
        $this->_addBreadcrumb(__('Templates'), __('Templates'));

        $this->_view->renderLayout();
//        echo $this->getRequest()->getFullActionName();
        //      exit();

        /** @var Page $resultPage */
        /**
         * $resultPage = $this->resultPageFactory->create();
         * $resultPage->setActiveMenu('Snmportal_Pdfprint::template');
         * $resultPage->addBreadcrumb(__('PDFPRINT'), __('PDFPRINT'));
         * $resultPage->addBreadcrumb(__('Manage Template'), __('Manage Template'));
         * $resultPage->getConfig()->getTitle()->prepend(__('Template'));
         *
         * return $resultPage;
         **/
    }
}
