<?php

namespace Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Snmportal\Pdfprint\Model\Template;

class Style extends Generic implements TabInterface
{
    /**
     * @param Context     $context
     * @param Registry    $registry
     * @param FormFactory $formFactory
     * @param array       $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare label for tab
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Style');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Style');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return (bool)$this->getModelInstance()
                          ->isCompleteToCreate();
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->getModelInstance();

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Snmportal_Pdfprint::template_save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('layout_');


        $fieldset = $form->addFieldset('layout2_margins1', ['legend' => __('Margins Page 1')]);
        $field = $fieldset->addField(
            'margins1',
            'text',
            [
                'name' => 'margins1',
            ]
        );
        $field->setRenderer(
            $this->getLayout()
                 ->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab\Layout\Margins')
        );
        $fieldset = $form->addFieldset('layout2_margins2', ['legend' => __('Margins Page 2-n')]);
        $field = $fieldset->addField(
            'margins2',
            'text',
            [
                'name' => 'margins2',
            ]
        );
        $field->setRenderer(
            $this->getLayout()
                 ->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab\Layout\Margins')
        );

        $fieldset = $form->addFieldset('layout2_text_before', ['legend' => __('CSS Style')]);
        $field = $fieldset->addField(
            'css',
            'textarea',
            [
                'name'     => 'css',
                //'style'=>'height:502px;',
                'label'    => __('CSS Style'),
                'title'    => __('CSS Style'),
                'required' => false,
                'disabled' => $isElementDisabled,
                'codemode' => 'css'
            ]
        );
        $field->setRenderer(
            $this->getLayout()
                 ->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Renderer\Codestyle')
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Getter
     *
     * @return Template
     */
    public function getModelInstance()
    {
        return $this->_coreRegistry->registry('snmportal_pdfprint_template');
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     *
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
