<?php

namespace Snmportal\Pdfprint\Model\Options;

use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Model\System\Store;
use Snmportal\Pdfprint\Model\Template;

class TypeHash implements ArrayInterface
{
    /**
     * System Store Model
     *
     * @var Store
     */
    protected $_systemStore;

    /**
     * @param Store $systemStore
     */
    public function __construct(Store $systemStore)
    {
        $this->_systemStore = $systemStore;
    }

    /**
     * Return store group array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => Template::TYPE_ORDER, 'label' => __('Order')],
            ['value' => Template::TYPE_INVOICE, 'label' => __('Invoice')],
            ['value' => Template::TYPE_SHIPPING, 'label' => __('Shipping')],
            ['value' => Template::TYPE_CREDITMEMO, 'label' => __('Credit Memo')]
        ];
    }

    /**
     * @return array
     */
    public function toHashArray()
    {
        return [
            Template::TYPE_ORDER      => __('Order'),
            Template::TYPE_INVOICE    => __('Invoice'),
            Template::TYPE_SHIPPING   => __('Shipping'),
            Template::TYPE_CREDITMEMO => __('Credit Memo')
        ];
    }
}
