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

class Layout extends Generic implements TabInterface
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
        return __('Free Items');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Free Items');
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
        /*
        if ($this->_isAllowedAction('Snmportal_Pdfprint::template_save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('layout_');

        //$fieldset = $form->addFieldset('layout_preview', ['legend' => __('Preview')]);


        /* @var $layoutBlock \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main\Layout */

        /**
         * $layoutBlock = $this->getLayout()->createBlock(
         * 'Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab\Layout\Items'
         * )->setWidgetInstance(
         * $model
         * );
         * $fieldset = $form->addFieldset('layout_items_fieldset', ['legend' => __('Items')]);
         * $fieldset->addField('layout_items', 'note', []);
         * $form->getElement('layout_items_fieldset')->setRenderer($layoutBlock);
         */

        $fieldset = $form->addFieldset('layout_items', ['legend' => __('Items page 1'), 'collapsable' => true]);

        $field = $fieldset->addField(
            'free_items',
            'text',
            [
                'name' => 'free_items',
            ]
        );
        $field->setRenderer(
            $this->getLayout()
                 ->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab\Layout\Items')
        );

        $fieldset = $form->addFieldset('layout_items_p2', ['legend' => __('Items page 2-n'), 'collapsable' => true]);
        $field = $fieldset->addField(
            'free_items_p2',
            'text',
            [
                'name' => 'free_items_p2',
            ]
        );
        $field->setRenderer(
            $this->getLayout()
                 ->createBlock('Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab\Layout\Items')
        );


        /*
                $layoutBlock = $this->getLayout()->createBlock(
                    'Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Options'
                )->setWidgetInstance(
                    $model
                );
                $fieldset = $form->addFieldset('layout_items_fieldset', ['legend' => __('Items')]);
                $fieldset->addField('layout_items', 'note', []);
                $form->getElement('layout_items_fieldset')->setRenderer($layoutBlock);
        */
//

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
