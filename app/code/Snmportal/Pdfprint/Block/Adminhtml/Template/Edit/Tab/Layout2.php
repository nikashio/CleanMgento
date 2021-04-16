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

class Layout2 extends Generic implements TabInterface
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
        return __('Table and Text');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Table and Text');
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


        $fieldset = $form->addFieldset(
            'layout2_text_before',
            ['legend' => __('Text Before Table'), 'collapsable' => true]
        );
        $field = $fieldset->addField(
            'text_before_table',
            'textarea',
            [
                'name'     => 'text_before_table',
                'label'    => __('Before table'),
                'title'    => __('Before table'),
                'required' => false,
                'disabled' => $isElementDisabled,
                'codemode' => 'magento'
            ]
        );
        $field->setRenderer(
            $this->getLayout()
                 ->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Renderer\Codestyle')
        );
        $fieldset = $form->addFieldset(
            'layout2_text_after',
            ['legend' => __('Text After Table'), 'collapsable' => true]
        );
        $field = $fieldset->addField(
            'text_after_table',
            'textarea',
            [
                'name'     => 'text_after_table',
                'label'    => __('After table'),
                'title'    => __('After table'),
                'required' => false,
                'disabled' => $isElementDisabled,
                'codemode' => 'magento'
            ]
        );
        $field->setRenderer(
            $this->getLayout()
                 ->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Renderer\Codestyle')
        );


        $fieldset = $form->addFieldset(
            'layout_table_columns',
            ['legend' => __('Table columns'), 'collapsable' => true]
        );

        $fieldset->addField(
            'table_columns_use_default',
            'select',
            [
                'label'    => __('Use Custom Columns'),
                'title'    => __('Use Custom Columns'),
                'name'     => 'table_columns_use_default',
                'onchange' => 'hideShowPDFTemplate(this);',
                'required' => false,
                'options'  => $model->getOptionYesNo(),
                'disabled' => $isElementDisabled
            ]
        );

        $field = $fieldset->addField(
            'table_columns',
            'text',
            [
                'name' => 'table_columns',
            ]
        );
        $field->setRenderer(
            $this->getLayout()
                 ->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab\Layout\Columns')
                 ->setModelTemplate($model)
        );

        if ($model && !$model->isTemplate(Template::TYPE_SHIPPING)) {

            $fieldset = $form->addFieldset(
                'layout_table_totals',
                ['legend' => __('Table Totals'), 'collapsable' => true]
            );
            $fieldset->addField(
                'table_totals_use_custom',
                'select',
                [
                    'label'    => __('Use Custom Totals'),
                    'title'    => __('Use Custom Totals'),
                    'name'     => 'table_totals_use_custom',
                    'onchange' => 'hideShowPDFTemplate(this);',
                    'required' => false,
                    'options'  => $model->getOptionYesNo(),
                    'disabled' => $isElementDisabled
                ]
            );
            $fieldset->addField(
                'table_taxrenderer_default',
                'select',
                [
                    'label'    => __('Use Default Tax Renderer'),
                    'title'    => __('Use Default Tax Renderer'),
                    'name'     => 'table_taxrenderer_default',
                    'onchange' => 'hideShowPDFTemplate(this);',
                    'required' => false,
                    'options'  => $model->getOptionYesNo(),
                    'disabled' => $isElementDisabled
                ]
            );
            $fieldset->addField(
                'table_tax_full_summary',
                'select',
                [
                    'label'    => __('Display Full Tax Summary'),
                    'title'    => __('Display Full Tax Summary'),
                    'name'     => 'table_tax_full_summary',
                    'required' => false,
                    'options'  => $model->getOptionYesNo(),
                    'disabled' => $isElementDisabled
                ]
            );
            $fieldset->addField(
                'table_tax_all',
                'select',
                [
                    'label'    => __('Display Tax Total'),
                    'title'    => __('Display Tax Total'),
                    'name'     => 'table_tax_all',
                    'required' => false,
                    'options'  => $model->getOptionDisplayTaxTotal(),
                    'disabled' => $isElementDisabled
                ]
            );

            $field = $fieldset->addField(
                'table_totals',
                'text',
                [
                    'name' => 'table_totals',
                ]
            );
            $field->setRenderer(
                $this->getLayout()
                     ->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab\Layout\Totals')
                     ->setModelTemplate($model)
            );
        }

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
