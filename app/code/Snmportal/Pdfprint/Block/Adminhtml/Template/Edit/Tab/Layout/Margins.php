<?php

namespace Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab\Layout;

/**
 * Adminhtml tier price item renderer
 */

use Magento\Backend\Block\Widget;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class Margins extends Widget implements RendererInterface
{
    /**
     * Form element instance
     *
     * @var AbstractElement
     */
    protected $_element;

    /**
     * @var string
     */
    protected $_template = 'instance/edit/margins.phtml';

    /**
     * @return mixed
     */
    public function getJsName()
    {
        if (!$this->getData('js_name')) {
            $this->setData('js_name', uniqid('jsctrl_'));
        }

        return $this->getData('js_name');
    }

    /**
     * @return array
     */
    public function getValues()
    {
        $values = [];
        $data = $this->getElement()
                     ->getValue();
        if (!is_array($data)) {
            $data = [
                [
                    'left'   => '20',
                    'top'    => '110',
                    'right'  => '5',
                    'bottom' => '50'
                ]
            ];
        }
        if (is_array($data)) {
            $values = $this->_sortValues($data);
        }
        //     $values[] = array('x_pos'=>1,'y_pos'=>2);
        //   $values[] = array('x_pos'=>11,'y_pos'=>21,'width'=>1231,'height'=>2122);

        /*
                $currency = $this->_localeCurrency->getCurrency($this->_directoryHelper->getBaseCurrencyCode());

                foreach ($values as &$value) {
                    $value['readonly'] = $value['website_id'] == 0 &&
                        $this->isShowWebsiteColumn() &&
                        !$this->isAllowChangeWebsite();
                    $value['price'] =
                        $currency->toCurrency($value['price'], ['display' => \Magento\Framework\Currency::NO_SYMBOL]);
                }
        */

        return $values;
    }

    /**
     * Sort values
     *
     * @param array $data
     *
     * @return array
     */
    protected function _sortValues($data)
    {
        //usort($data, [$this, '_sortTierPrices']);
        return $data;
    }

    /**
     * Retrieve 'add group price item' button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * Render HTML
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {

        $this->setElement($element);

        return $this->toHtml();
    }

    /**
     * Prepare global layout
     * Add "Add " button to layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        /*
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            ['label' => __('Add Item'), 'onclick' => 'return '.$this->getJsName().'Control.addItem()', 'class' => 'add']
        );
        $button->setName('add_'.$this->getJsName().'_item_button');
        $this->setChild('add_button', $button);
        */
        return parent::_prepareLayout();
    }
}
