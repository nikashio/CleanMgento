<?php
/*

* Copyright © 2016 SNM-Portal.com. All rights reserved.
* See LICENSE.txt for license details.

*/

namespace Snmportal\Pdfprint\Block\Adminhtml;

class PrintInvoiceButton extends SplitButton
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('snmpdf-print-button');
        $this->setLabel(__('Print'));
        $this->setOptions($this->getTemplateOptions());
        $entityId = $this->_getEntityId();
        if ($entityId) {
            $this->setOnclick(
                "setLocation('" . $this->getUrl('snmpdfprint/invoice/print', ['invoice_id' => $entityId]) . "')"
            );
        }
        //   $this->setHasSplit( count($this->getOptions()));
    }

    /**
     * @return array
     */
    protected function getTemplateOptions()
    {
        $document = $this->_coreRegistry->registry('current_invoice');
        $options = [];
        if ($document) {
            /**
             * @var $template \Snmportal\Pdfprint\Model\Template
             */
            $defaultTemplate = $this->_pdfHelper->getBestTemplateForDocument($document);
            $defaultId = $defaultTemplate ? $defaultTemplate->getId() : 0;
            foreach ($this->_pdfHelper->getTemplatesForDocument($document) as $template) {
                $options[] = [
                    'label' => $template->getTitle() . ($defaultId == $template->getId() ? ' (*)' : ''),
                    'onclick' => "setLocation('" . $this->getUrl(
                        'snmpdfprint/invoice/print',
                        [
                                'invoice_id' => $document->getEntityId(),
                                'tplid'      => $template->getId()
                            ]
                    ) . "')"
                ];
            }
        }

        return $options;
    }

    /**
     * @return int
     */
    protected function _getEntityId()
    {
        return $this->_coreRegistry->registry('current_invoice') ? $this->_coreRegistry->registry('current_invoice')
                                                                                       ->getEntityId() : 0;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_scopeConfig->isSetFlag('snmportal_pdfprint/invoice/enabled') || !$this->_scopeConfig->isSetFlag(
            'snmportal_pdfprint/general/enabled'
        )) {
            return '';
        }

        return $this->_getDefaultCss() . parent::_toHtml();
    }

    /**
     * @return string
     */
    protected function _getDefaultCss()
    {
        $style = '<style>';
        $style .= '.actions-split.pdfprint:hover {box-shadow:none;}';
        if (!$this->_scopeConfig->isSetFlag('snmportal_pdfprint/invoice/show_default_print_button')) {
            $style .= '.page-actions  button#print{display:none !important;}';
        }
        $style .= '</style>';

        return $style;
    }
}
