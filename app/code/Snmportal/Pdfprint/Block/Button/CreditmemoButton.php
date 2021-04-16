<?php
/*

* Copyright © 2016 SNM-Portal.com. All rights reserved.
* See LICENSE.txt for license details.

*/

namespace Snmportal\Pdfprint\Block\Button;

class CreditmemoButton extends AbstractButton
{
    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_scopeConfig->isSetFlag('snmportal_pdfprint/general/enabled') || !$this->_scopeConfig->isSetFlag(
            'snmportal_pdfprint/creditmemo/enabled'
        ) || !$this->_scopeConfig->isSetFlag('snmportal_pdfprint/creditmemo/use_pdf_frontend')) {
            return '';
        }

        return $this->_getDefaultCss();
    }

    /**
     * @return string
     */
    public function _getDefaultCss()
    {
        $style = '<style>';
        $style .= 'body .order-details-items .action.print{display:block;}';
        $style .= '</style>';

        return $style;
    }
}
