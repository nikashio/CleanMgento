<?php

namespace Snmportal\Pdfprint\Controller\Adminhtml\Template;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Snmportal\Pdfprint\Controller\Adminhtml\Template;

class Edit extends Template
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param PageFactory    $resultPageFactory
     * @param Registry       $registry
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * Edit Template
     *
     * @return Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()
                   ->getParam('template_id');
        $model = $this->_initModelInstance();

        // 2. Initial checking
        if (!$model) {
            $this->messageManager->addError(__('This template no longer exists.'));
            /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }


        // 4. Register model to use later in blocks
//        $this->_coreRegistry->register('snmportal_pdfprint_template', $model);

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

    /**
     * Init actions
     *
     * @return Page
     */
    protected function _initAction()
    {

        // load layout, set active menu and breadcrumbs
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('snmportal_pdfprint')
                   ->addBreadcrumb(__('SNM-Portal'), __('SNM-Portal'))
                   ->addBreadcrumb(__('Manage Templates'), __('Manage Templates'));

        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Snmportal_Pdfprint::template');
    }
}
