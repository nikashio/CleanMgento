<?php
/*

* Copyright © 2016 SNM-Portal.com. All rights reserved.
* See LICENSE.txt for license details.

*/

namespace Snmportal\Pdfprint\Block\Adminhtml;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\AbstractBlock as AbstractBlockAlias;

class DropdownButton extends AbstractBlockAlias
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @param Context  $context
     * @param Registry $registry
     * @param array    $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {

        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (count($this->_getTemplatesOptions())) {
            return $this->_getDefaultCss() . $this->_dropdownButtonHTML();
        }

        return $this->_getDefaultCss() . '
        <button type="button" class="action-default scalable snmbutton" 
        onclick="setLocation(' . $this->getButtonUrl() . ')">
            <span >' . $this->getButtonLabel() . '</span>
        </button>';
    }

    /**
     * @return array
     */
    protected function _getTemplatesOptions()
    {
        return [];
    }

    /**
     * @return string
     */
    protected function _getDefaultCss()
    {
        return '';
    }

    /**
     * @return string
     */
    protected function _dropdownButtonHTML()
    {
        return '
    <div class="admin-snm-dropdown admin__action-dropdown-wrap">
    <button
        type="button"
        class="action-defaultxx admin__action-dropdown"
        data-mage-init=\'{"dropdown":{}}\'
        data-toggle="dropdown">
        <span class="admin__action-dropdown-text"><span class="xx">' . $this->getButtonLabel() . '</span></span>
    </button>
    <ul class="admin__action-dropdown-menu" >' . $this->_getTemplatesOptionsHtml() . '</ul></div>';
    }

    /**
     * @return string
     */
    public function getButtonLabel()
    {
        return 'ButtonLabel';
    }

    /**
     * @return string
     */
    protected function _getTemplatesOptionsHtml()
    {
        $html = '';
        foreach ($this->_getTemplatesOptions() as $option) {
            $html .= '<li>';
            $html .= '<a href="' . $option['url'] . '">' . $option['label'] . '</a>';
            $html .= '</li>';
        }

        return $html;
    }
}
