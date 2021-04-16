<?php
/*

* Copyright © 2016 SNM-Portal.com. All rights reserved.
* See LICENSE.txt for license details.

*/

namespace Snmportal\Pdfprint\Block\Button;

use Magento\Framework\App\Http\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

abstract class AbstractButton extends Template
{
    /**
     * @var string
     */
    protected $_template = 'print/button.phtml';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var Context
     */
    protected $httpContext;

    /**
     * @param Template\Context $context
     * @param Registry         $registry
     * @param Context          $httpContext
     * @param array            $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        Context $httpContext,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * @return string
     */
    public function _getDefaultCss()
    {
        $style = '<style>';
        $style .= 'body .order-details-items .action.print{display:block;}';
        //      $style .= '.actions .action.print{display:none;}';
        //    $style .= '.actions .action.print.snmportal{display:block;}';
        $style .= '</style>';

        return $style;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        return '';
    }
}
