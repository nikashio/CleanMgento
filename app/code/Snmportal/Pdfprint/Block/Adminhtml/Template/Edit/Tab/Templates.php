<?php
/*

* Copyright © 2016 SNM-Portal.com. All rights reserved.
* See LICENSE.txt for license details.

*/

namespace Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Snmportal\Pdfprint\Model\Template;

class Templates extends Generic implements TabInterface
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
        return __('Block Templates');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Block Templates');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return (bool)$this->getWidgetInstance()
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
        /** @var $model Template */
        $model = $this->_coreRegistry->registry('snmportal_pdfprint_template');

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

        $form->setHtmlIdPrefix('blocktemplates_');

        $fieldset = $form->addFieldset('layout_block_templates', ['legend' => __('PHTML Templates')]);
        $field = $fieldset->addField(
            'block_templates',
            'text',
            [
                'name' => 'block_templates',
            ]
        );
        $field->setRenderer(
            $this->getLayout()
                 ->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab\Layout\Templates')
        );


        /*

                $fieldsets['upload']->addField(
                    'template_name',
                    'label',
                    [
                        'name' => 'template_name',
                        'label' => __('File name'),
                        'title' => __('File name'),

                    ]
                );
                if ( $model->getTemplateName() )
                {
                    $fieldsets['upload']->addField(
                        'template_name_delete',
                        'checkbox',
                        [
                            'name' => 'template_name_delete',
                            'label' => __('File delete'),
                            'title' => __('File delete'),

                        ]
                    );
                }
                $fieldsets['upload']->addField(
                    'import_file',
                    'file',
                    [
                        'name' => 'import_file',
                        'label' => __('Select File to Import'),
                        'title' => __('Select File to Import'),
                        //'required' => true,
                        'class' => 'input-file'
                    ]
                );*/

        // $model->setData('import_file', 'test...pdf');
        //     $form->setUseContainer(true);
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
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
