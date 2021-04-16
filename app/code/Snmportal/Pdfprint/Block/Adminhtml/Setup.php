<?php
/*

* Copyright © 2016 SNM-Portal.com. All rights reserved.
* See LICENSE.txt for license details.

*/

namespace Snmportal\Pdfprint\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;
use Magento\Framework\Phrase;

class Setup extends Container
{
    /**
     * Get the url for create
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }

    /**
     * Get header text
     *
     * @return Phrase
     */
    public function getHeaderText()
    {
        return __('Template');
    }

    protected function _construct()
    {
        $this->_blockGroup = 'Snmportal_Pdfprint';
        $this->_controller = 'adminhtml_template';

        if ($this->hasMagento1data()) {
            $this->buttonList->add(
                'importfrommagento1',
                [
                    'label' => __('Import from Magento1 data'),
                    'class' => 'save',
                    'onclick' => 'setLocation(\'' . $this->getImportUrl() . '\')'
                ]
            );
        }
        parent::_construct();
        if ($this->_isAllowedAction('Snmportal_Pdfprint::template_new')) {
            $this->buttonList->update('add', 'label', __('Add New Template'));
        } else {
            $this->buttonList->remove('add');
        }
    }

    /**
     * @return bool
     */
    protected function hasMagento1data()
    {
        if ($this->_scopeConfig->getValue('auit_pdf/general/license') && !$this->_scopeConfig->isSetFlag(
            'auit_pdf/general/importedmagento2'
        )) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getImportUrl()
    {
        return $this->getUrl('*/*/importm1');
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     *
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
