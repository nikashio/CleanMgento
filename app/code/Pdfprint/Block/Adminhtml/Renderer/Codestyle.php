<?php

namespace Snmportal\Pdfprint\Block\Adminhtml\Renderer;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Form fieldset renderer
 */
class Codestyle extends Template implements RendererInterface
{
    /**
     * Form element which re-rendering
     *
     * @var Fieldset
     */
    protected $_element;

    /**
     * @var string
     */
    protected $_template = 'renderer/codestyle.phtml';

    /**
     * Retrieve an element
     *
     * @return Fieldset
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Render element
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->_element = $element;

        return $this->toHtml();
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->addPageAsset('Snmportal_Pdfprint/cm/lib/codemirror.css');

        return parent::_prepareLayout();
    }

    /**
     * Return html for store switcher hint
     *
     * @return string
     */

//    public function getHintHtml()
//    {
//        /** @var $storeSwitcher \Magento\Backend\Block\Store\Switcher */
//        $storeSwitcher = $this->_layout->getBlockSingleton('Magento\Backend\Block\Store\Switcher');
//        return $storeSwitcher->getHintHtml();
//    }
}
