<?php

namespace Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Snmportal\Pdfprint\Model\Options\TypeHash;
use Snmportal\Pdfprint\Model\Template;

class Settings extends Generic implements TabInterface
{
    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @var TypeHash
     */
    protected $typeOptions;

    /**
     * @param Context     $context
     * @param Registry    $registry
     * @param FormFactory $formFactory
     * @param Store       $systemStore
     * @param TypeHash    $typeOptions
     * @param array       $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        TypeHash $typeOptions,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->typeOptions = $typeOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare label for tab
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Settings');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return !(bool)$this->getWidgetInstance()
                           ->isCompleteToCreate();
    }

    /**
     * Getter
     *
     * @return Template
     */
    public function getWidgetInstance()
    {
        return $this->_coreRegistry->registry('snmportal_pdfprint_template');
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Return url for continue button
     *
     * @return string
     */
    public function getContinueUrl()
    {
        return $this->getUrl(
            'snmpdfprint/*/*',
            ['_current' => true, 'type' => '<%- data.type %>']
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setActive(true);
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
        //  ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        /*


        */

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Settings')]);

        $this->_addElementTypes($fieldset);
        $fieldset->addField(
            'type',
            'select',
            [
                'name'     => 'type',
                'label'    => __('Type'),
                'title'    => __('Type'),
                'required' => true,
                'values'   => $this->getTypesOptionsArray()
            ]
        );

        $field = $fieldset->addField(
            'file_pdf_import',
            'file',
            [
                'name'     => 'file_pdf_import',
                'label'    => __('Import File'),
                'title'    => __('Import File'),
                'display'  => 'none',
                'required' => true,
                'note'     => __(
                    'Please use (*.snmportal-pdfprint-template) File'
                ) . '<br/><a target="_blank" href="https://snm-portal.com/pdfprint-m2-example-templates">' . __(
                    'Download Sample Templates from SNM-Portal'
                ) . '</a>'
            ]
        );

        //$field->setRenderer($this->getLayout()->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Renderer\File'));
        $continueButton = $this->getLayout()
                               ->createBlock(
                                   'Magento\Backend\Block\Widget\Button'
                               )
                               ->setData(
                                   [
                                       'label'          => __('Continue'),
                                   //                'onclick' => "setSettings('" . $this->getContinueUrl() . "', 'type')",
                                   //                'onclick' => "this.form.submit()",
                                       'class'          => 'save',
                                       'data_attribute' => [
                                           'mage-init' => [
                                               'button' => [
                                                   'event'  => 'newContinue',
                                                   'target' => '#pdfprint_template_edit_form'
                                               ]
                                           ],
                                       ]

                                   ]
                               );
        $fieldset->addField('continue_button', 'note', ['text' => $continueButton->toHtml()]);

        $this->setForm($form);
        $htmlIdPrefix = $form->getHtmlIdPrefix();


        $this->setChild(
            'form_after',
            $this->getLayout()
                 ->createBlock(
                     'Magento\Backend\Block\Widget\Form\Element\Dependence'
                 )
                 ->addFieldMap(
                     "{$htmlIdPrefix}type",
                     'type'
                 )
                 ->addFieldMap(
                     "{$htmlIdPrefix}file_pdf_import",
                     'file_pdf_import'
                 )
                 ->addFieldDependence(
                     'file_pdf_import',
                     'type',
                     'import_file'
                 )
        );

        return parent::_prepareForm();
    }

    /**
     * Retrieve array (widget_type => widget_name) of available widgets
     *
     * @return array
     */

    public function getTypesOptionsArray()
    {
        $options = $this->typeOptions->toOptionArray();
        array_unshift($options, ['value' => '', 'label' => __('-- Please Select --')]);
        $options[] = ['value' => 'import_file', 'label' => __('Import from File')];

        return $options;
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
