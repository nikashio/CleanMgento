<?php
/*

* Copyright © 2016 SNM-Portal.com. All rights reserved.
* See LICENSE.txt for license details.

*/

namespace Snmportal\Pdfprint\Block\Adminhtml\Template\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * Tabs constructor.
     *
     * @param Context          $context
     * @param EncoderInterface $jsonEncoder
     * @param Session          $authSession
     * @param Registry         $registry
     * @param array            $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $jsonEncoder, $authSession);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('template_record');
        $this->setDestElementId('pdfprint_template_edit_form');
        $model = $this->getModelInstance();
        if ($model) {
            $this->setTitle(__('Template: %1', '' . $model->getTypeName()));
        } else {
            $this->setTitle(__('Template'));
        }
    }

    /**
     * @return mixed
     */
    public function getModelInstance()
    {
        return $this->_coreRegistry->registry('snmportal_pdfprint_template');
    }
}
