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

class ExportImport extends Generic implements TabInterface
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
        return __('Export Template');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Export Template');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return (bool)$this->getModelInstance()
                          ->isCompleteToCreate() && $this->_isAllowedAction('Snmportal_Pdfprint::template_save');
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
        /*
        if ($this->_isAllowedAction('Snmportal_Pdfprint::template_save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('layout_');
        if ($model->getId()) {
            $fieldset = $form->addFieldset('expimp_export', ['legend' => __('Export')]);
            $fontButton = $this->getLayout()
                               ->createBlock(
                                   'Magento\Backend\Block\Widget\Button'
                               )
                               ->setData(
                                   [
                                       'id'      => 'export_manager',
                                       'label'   => __('Export Template'),
                                       'class'   => 'button',
                                       'onclick' => "setLocation('" . $this->getUrl(
                                           'snmpdfprint/template/export',
                                           ['template_id' => $model->getId()]
                                       ) . "');",
                                   ]
                               );
            $fieldset->addField(
                'export_button',
                'note',
                ['label' => '', 'text' => $fontButton->toHtml()]
            );
        }

        /*
                $fieldset = $form->addFieldset('expimp_import', ['legend' => __('Import')]);

                $field = $fieldset->addField(
                    'file_pdf_import',
                    'file',
                    [
                        'referenz_field'=>'import_file',
                        'referenz_value'=>'',
                        'delete_field'=>'import_file_delete',
                        'delete_label'=>__('Delete File'),
                        'name' => 'import_file',
                        'label' => __('Import File'),
                        'title' => __('Import File'),
                        'class' => 'input-file',
                        'note' => __('Please use (*.snmportal-pdfprint-template) File'),
                    ]
                );
                $field->setRenderer($this->getLayout()->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Renderer\File'));
        */

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
