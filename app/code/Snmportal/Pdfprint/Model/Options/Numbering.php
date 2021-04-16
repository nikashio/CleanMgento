<?php

namespace Snmportal\Pdfprint\Model\Options;

use Magento\Framework\Option\ArrayInterface;

class Numbering implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Over all documents')],
            ['value' => 1, 'label' => __('Each document separately')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [1 => __('Each document separately'), 0 => __('Over all documents')];
    }
}
