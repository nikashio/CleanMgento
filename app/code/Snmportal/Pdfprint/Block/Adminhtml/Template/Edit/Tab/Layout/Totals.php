<?php

namespace Snmportal\Pdfprint\Block\Adminhtml\Template\Edit\Tab\Layout;

/**
 * Adminhtml tier price item renderer
 */

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Pdf\Config;
use Snmportal\Pdfprint\Model\Template;

class Totals extends Widget implements RendererInterface
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
    protected $_template = 'instance/edit/totals.phtml';

    /**
     * @var Config
     */
    protected $_pdfConfig;

    /**
     * Totals constructor.
     *
     * @param Context $context
     * @param Config  $config
     * @param array   $data
     */
    public function __construct(
        Context $context,
        Config $config,
        array $data = []
    ) {
        $this->_pdfConfig = $config;
        parent::__construct($context, $data);
    }

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
    public function getOptions()
    {
        $values = [];
        $values[] = ['label' => __('Only if useful'), 'value' => 'candisplay'];
        $values[] = ['label' => __('Always'), 'value' => 'always'];

        return $values;
    }

    /**
     * @return array
     */
    public function getTypeOption()
    {
        $values = [];
        $totals = $this->_pdfConfig->getTotals();
        usort($totals, [$this, '_sortTotalsList']);
        foreach ($totals as $totalInfo) {

            $values[] = ['label' => $totalInfo['title'], 'value' => $totalInfo['source_field']];
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

    /**
     * @param $a
     * @param $b
     *
     * @return int
     */
    protected function _sortTotalsList($a, $b)
    {
        if (!isset($a['sort_order']) || !isset($b['sort_order'])) {
            return 0;
        }

        if ($a['sort_order'] == $b['sort_order']) {
            return 0;
        }

        return $a['sort_order'] > $b['sort_order'] ? 1 : -1;
    }
}
