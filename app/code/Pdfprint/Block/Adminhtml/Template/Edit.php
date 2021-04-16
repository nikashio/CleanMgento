<?php
/*

* Copyright © 2016 SNM-Portal.com. All rights reserved.
* See LICENSE.txt for license details.

*/

namespace Snmportal\Pdfprint\Block\Adminhtml\Template;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\AbstractBlock;
use Snmportal\Pdfprint\Helper\Data;

class Edit extends Container
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @param Context  $context
     * @param Registry $registry
     * @param Data     $helper
     * @param array    $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve text for header element depending on loaded post
     *
     * @return Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('snmportal_pdfprint_template')
                                ->getId()) {
            return __(
                "Edit Template '%1'",
                $this->escapeHtml(
                    $this->_coreRegistry->registry('snmportal_pdfprint_template')
                                        ->getTitle()
                )
            );
        } else {
            return __('New Template');
        }
    }

    /**
     * Initialize blog post edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'template_id';
        $this->_blockGroup = 'Snmportal_Pdfprint';
        $this->_controller = 'adminhtml_template';

        parent::_construct();

        if ($this->_isAllowedAction('Snmportal_Pdfprint::template_save')) {

            if ($this->getToolbar()) {
                if ($this->_coreRegistry->registry('snmportal_pdfprint_template')
                                        ->getId() || $this->_coreRegistry->registry('snmportal_pdfprint_template')
                                                                         ->getType()

                ) {
                    $this->getToolbar()
                         ->addChild(
                             'save-split-button',
                             'Magento\Backend\Block\Widget\Button\SplitButton',
                             [
                                 'id'           => 'save-split-button',
                                 'label'        => __('Save'),
                                 'class_name'   => 'Magento\Backend\Block\Widget\Button\SplitButton',
                                 'button_class' => 'widget-button-save',
                                 'options'      => $this->_getSaveSplitButtonOptions()
                             ]
                         );
                }
            }
            $this->buttonList->remove('save');
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('Snmportal_Pdfprint::template_delete')) {
            //$this->buttonList->update('delete', 'label', __('Delete Grid'));
            $this->buttonList->update('delete', 'label', __('Delete'));
        } else {
            $this->buttonList->remove('delete');
        }
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

    /**
     * @return array
     */
    protected function _getSaveSplitButtonOptions()
    {
        $options = [];
        $options[] = [
            'id'             => 'edit-button',
            'label'          => __('Save & Edit'),
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'saveAndContinueEdit', 'target' => '#pdfprint_template_edit_form'],
                ],
            ],
            'default'        => true,
        ];
        if ($this->_coreRegistry->registry('snmportal_pdfprint_template')
                                ->getId()) {
            $options[] = [
                'id'             => 'duplicate-button',
                'label'          => __('Save & Duplicate'),
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndDuplicate', 'target' => '#pdfprint_template_edit_form'],
                    ],
                ],
            ];
        }
        $options[] = [
            'id'             => 'close-button',
            'label'          => __('Save & Close'),
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save', 'target' => '#pdfprint_template_edit_form']],
            ],
        ];

        $this->helper->Info();

        return $options;
    }

    /**
     * Prepare layout
     *
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'content');
                }
            };
        ";
        /*
        $this->_formScripts[] = "
require([
    'jquery',
    'mage/backend/form',
    'mage/backend/validation'
], function($){
        var f = $('#edit_form').form();
        var fm = f.mage('form');
        $('#edit_form').form().mage('form', {
        handlersData: {
        save: {},
        saveAndContinueEdit: {
            action: {
                args: {back: 'edit'}
            }
        },
        saveAndNew: {
            action: {
                args: {back: 'new'}
            }
        },
        saveAndDuplicate: {
            action: {
                args: {back: 'duplicate'}
            }
        }
    }
    });
});        ";
        */
        $this->_formScripts[] = "
        require([
            'jquery',
     //       'mage/backend/form',
            'mage/backend/validation'
        ], function($){

        $('#pdfprint_template_edit_form').form()
        .validation({
            //onsubmit:false,
            //validationUrl: '0',
            submitHandler:function(form,event){
                console.log('validate...');
                event.preventDefault();
                window.setTimeout(function(){
                    form.submit();
                    },0);
                return true;
            },
            highlight: function(element) {
            var detailsElement = $(element).closest('details');
            if (detailsElement.length && detailsElement.is('.details')) {
                var summaryElement = detailsElement.find('summary');
                if (summaryElement.length && summaryElement.attr('aria-expanded') === 'false') {
                    summaryElement.trigger('click');
                }
            }
            $(element).trigger('highlight.validate');
        }
        });

});";
        $this->_formScripts[] = "
require([
    'jquery',
    'mage/mage',
    'domReady!'
], function($){
    $('#pdfprint_template_edit_form').mage('form', {
        handlersData: {
            save: {},
            saveAndContinueEdit: {
                action: {
                    args: {back: 'edit'}
                }
            },
            newContinue: {
                action: {
                    args: {back: 'edit',save:'no'}
                }
            },
            saveAndNew: {
                action: {
                    args: {back: 'new'}
                }
            },
            saveAndDuplicate: {
                action: {
                    args: {back: 'duplicate'}
                }
            }
        }
    });

});        ";

        return parent::_prepareLayout();
    }
}
