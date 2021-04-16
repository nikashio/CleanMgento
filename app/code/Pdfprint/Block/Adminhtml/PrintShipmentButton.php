<?php
/*

* Copyright © 2016 SNM-Portal.com. All rights reserved.
* See LICENSE.txt for license details.

*/

namespace Snmportal\Pdfprint\Block\Adminhtml;

class PrintShipmentButton extends SplitButton
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
                "setLocation('" . $this->getUrl('snmpdfprint/shipment/print', ['shipment_id' => $entityId]) . "')"
            );
        }
        //   $this->setHasSplit( count($this->getOptions()));
    }

    /**
     * @return array
     */
    protected function getTemplateOptions()
    {
        $document = $this->_coreRegistry->registry('current_shipment');
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
                        'snmpdfprint/shipment/print',
                        [
                                'shipment_id' => $document->getEntityId(),
                                'tplid'       => $template->getId()
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
        return $this->_coreRegistry->registry('current_shipment') ? $this->_coreRegistry->registry('current_shipment')
                                                                                        ->getEntityId() : 0;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_scopeConfig->isSetFlag('snmportal_pdfprint/shipment/enabled')) {
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
        if (!$this->_scopeConfig->isSetFlag(
            'snmportal_pdfprint/shipment/show_default_print_button'
        ) || !$this->_scopeConfig->isSetFlag('snmportal_pdfprint/general/enabled')) {
            $style .= '.page-actions  button#print{display:none !important;}';
        }
        $style .= '</style>';

        return $style;
    }
}
