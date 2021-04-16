<?php

namespace Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Snmportal\Pdfprint\Model\Template;

class Main extends Generic implements TabInterface
{
    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @param Context     $context
     * @param Registry    $registry
     * @param FormFactory $formFactory
     * @param Store       $systemStore
     * @param array       $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare label for tab
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('General');
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
     * {@inheritdoc}
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
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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

        $form->setHtmlIdPrefix('template_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Template Information')]);

        if ($model->getId()) {
            $fieldset->addField('template_id', 'hidden', ['name' => 'template_id']);
        }
        //if ($model->getType()) {
        // $fieldset->addField('type', 'hidden', ['name' => 'type']);
        //}

        $fieldset->addField(
            'title',
            'text',
            [
                'name'     => 'title',
                'label'    => __('Template Name'),
                'title'    => __('Template Name'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'type',
            'select',
            [
                'label'    => __('Type'),
                'title'    => __('Type'),
                'name'     => 'type',
                'required' => true,
                'options'  => $model->getAvailableTypes(),
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'is_active',
            'select',
            [
                'label'    => __('Status'),
                'title'    => __('Status'),
                'name'     => 'is_active',
                'required' => true,
                'options'  => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'is_default',
            'select',
            [
                'label'    => __('Default Template'),
                'title'    => __('Default Template'),
                'name'     => 'is_default',
                'required' => true,
                'note'     => __('Yes, for automatic generation.'),
                'options'  => $model->getOptionYesNo(),
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'is_massaction',
            'select',
            [
                'label'    => __('MassAction Template'),
                'title'    => __('MassAction Template'),
                'name'     => 'is_massaction',
                'required' => true,
                'note'     => __('Yes, for mass action.'),
                'options'  => $model->getOptionYesNo(),
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'pdf_download_name',
            'text',
            [
                'label'    => __('Download Name'),
                'title'    => __('Download Name'),
                'name'     => 'pdf_download_name',
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name'     => 'stores[]',
                    'label'    => __('Store View'),
                    'title'    => __('Store View'),
                    'required' => true,
                    'values'   => $this->_systemStore->getStoreValuesForForm(false, true),
                    'disabled' => $isElementDisabled
                ]
            );
            $renderer = $this->getLayout()
                             ->createBlock(
                                 'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
                             );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name'  => 'stores[]',
                 'value' => $this->_storeManager->getStore(true)
                                                ->getId()
                ]
            );
            $model->setStoreId(
                $this->_storeManager->getStore(true)
                                    ->getId()
            );
        }

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }


        $this->_eventManager->dispatch('snmportal_pdfprint_template_edit_tab_main_prepare_form', ['form' => $form]);

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
