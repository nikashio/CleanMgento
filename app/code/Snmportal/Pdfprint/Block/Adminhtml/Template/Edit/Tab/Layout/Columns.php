<?php

namespace Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab\Layout;

/**
 * Adminhtml tier price item renderer
 */

use Magento\Backend\Block\Widget;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Exception\LocalizedException;
use Snmportal\Pdfprint\Model\Template;

class Columns extends Widget implements RendererInterface
{
    /**
     * Form element instance
     *
     * @var AbstractElement
     */
    protected $_element;

    /**
     * Form element instance
     *
     * @var Template
     */
    protected $_modelTemplate;

    /**
     * @var string
     */
    protected $_template = 'instance/edit/columns.phtml';

    //protected $_template = 'instance/edit/options.phtml';

    /**
     * @return bool
     */
    public function getReadOnly()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function canManageOptionDefaultOnly()
    {
        return false;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        $values = [];
        $data = $this->getElement()
                     ->getValue();

        if (is_array($data)) {
            $values = $this->_sortValues($data);
        }

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
     * @param $template
     *
     * @return $this
     */
    public function setModelTemplate($template)
    {
        $this->_modelTemplate = $template;

        return $this;
    }

    /**
     * @return array
     */
    public function getTypeOption()
    {

        $values = [];
        if ($this->_modelTemplate->isTemplate(Template::TYPE_SHIPPING)) {
            $values[] = ['label' => __('Position'), 'value' => 'position'];
            $values[] = ['label' => __('Product Info'), 'value' => 'fr_product'];
            $values[] = ['label' => __('Product Info and SKU'), 'value' => 'fr_product_sku'];
            $values[] = ['label' => __('SKU'), 'value' => 'fr_sku'];
            $values[] = ['label' => __('Qty'), 'value' => 'fr_qty'];
            $values[] = ['label' => __('Qty Number'), 'value' => 'qty'];
            $values[] = ['label' => __('Image'), 'value' => 'image'];
            $values[] = ['label' => __('Custom'), 'value' => 'custom'];
        } else {
            $values[] = ['label' => __('Position'), 'value' => 'position'];
            $values[] = ['label' => __('Product Info'), 'value' => 'fr_product'];
            $values[] = ['label' => __('Product Info and SKU'), 'value' => 'fr_product_sku'];
            $values[] = ['label' => __('SKU'), 'value' => 'fr_sku'];
            $values[] = ['label' => __('Price'), 'value' => 'fr_price'];
            $values[] = ['label' => __('Price Excl.Tax'), 'value' => 'price_excl_tax'];
            $values[] = ['label' => __('Price Incl.Tax'), 'value' => 'price_incl_tax'];
            if ($this->_modelTemplate->isTemplate(Template::TYPE_ORDER)) {
                $values[] = ['label' => __('Original Price'), 'value' => 'price_original'];

                $values[] = ['label' => __('Qty'), 'value' => 'fr_qty'];
                $values[] = ['label' => __('Qty Ordered'), 'value' => 'qty_ordered'];
                $values[] = ['label' => __('Qty Invoiced'), 'value' => 'qty_invoiced'];
                $values[] = ['label' => __('Qty Shipped'), 'value' => 'qty_shipped'];
                $values[] = ['label' => __('Qty Canceled'), 'value' => 'qty_canceled'];
                $values[] = ['label' => __('Qty Refunded'), 'value' => 'qty_refunded'];
            } else {
                $values[] = ['label' => __('Qty'), 'value' => 'fr_qty'];
                $values[] = ['label' => __('Qty Number'), 'value' => 'qty'];
            }

            //$values[]=array('label'=>__('Subtotal'),'value'=>'fr_subtotal');
            //$values[]=array('label'=>__('Subtotal Excl. Tax'),'value'=>'subtotal_excl_tax');
            $values[] = ['label' => __('Subtotal'), 'value' => 'subtotal_incl_tax'];

            $values[] = ['label' => __('Tax Amount'), 'value' => 'tax_amount'];
            $values[] = ['label' => __('Tax Percent'), 'value' => 'tax_percent'];

            $values[] = ['label' => __('Discount Amount'), 'value' => 'discount_amount'];

            $values[] = ['label' => __('Row Total'), 'value' => 'fr_row_total'];
            $values[] = ['label' => __('Row Total Excl. Tax'), 'value' => 'row_total_excl_tax'];
            $values[] = ['label' => __('Row Total Incl. Tax'), 'value' => 'row_total_incl_tax'];


            $values[] = ['label' => __('Status'), 'value' => 'status'];
            $values[] = ['label' => __('Image'), 'value' => 'image'];
            $values[] = ['label' => __('Custom'), 'value' => 'custom'];
        }

        return $values;
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
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()
                       ->createBlock(
                           'Magento\Backend\Block\Widget\Button'
                       )
                       ->setData(
                           ['label'   => __('Add Column'),
                            'onclick' => 'return ' . $this->getJsName() . 'Control.addItem()',
                            'class'   => 'add'
                           ]
                       );
        $button->setName('add_' . $this->getJsName() . '_item_button');
        $this->setChild('add_button', $button);

        return parent::_prepareLayout();
    }

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
}
