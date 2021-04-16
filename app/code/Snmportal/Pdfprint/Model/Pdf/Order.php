<?php

namespace Snmportal\Pdfprint\Model\Pdf;

use Magento\Framework\Exception\LocalizedException;

class Order extends Base
{
    /**
     * @param      $page
     * @param      $document
     * @param bool $boutput
     *
     * @return array
     * @throws LocalizedException
     */
    protected function loadlayout($page, $document, $boutput = true)
    {
        if ($this->registry->registry('current_order')) {
            $this->registry->unregister('current_order');
        }
        $this->registry->register('current_order', $document);

        // FIX 2.1.8 methode isPagerDisplayed not defined ...
        if ($this->productMetadata->getVersion() == '2.1.8') {
            $page->addHandle('sales_order_print_2_1_8');
        } else {
            $page->addHandle('sales_order_print');
        }
        $block = $page->getLayout()
                      ->getBlock('sales.order.print');
        if ($block) {
            $this->helperBlock = $page->getLayout()
                                      ->createBlock('Magento\Sales\Block\Adminhtml\Items\AbstractItems');
            $this->buildPaymentInfo($document, $page, $block);

            return $this->parseLayout($block->toHtml());
        }
        throw new LocalizedException(__('No layout "sales_order_print" block: "sales.order.print" found!'));
    }

    /**
     * @param $document
     * @param $page
     * @param $block
     */
    protected function buildPaymentInfo($document, $page, $block)
    {
        $this->_paymentInfo = ['info' => '', 'html' => ''];
        $this->_paymentInfo['html'] = $block->getPaymentInfoHtml();
        $this->_paymentInfo['info'] = strip_tags($block->getPaymentInfoHtml());
    }

    /**
     * @param $document
     *
     * @return null
     * @noinspection PhpExpressionAlwaysNullInspection
     */
    protected function getTemplateInfo($document)
    {
        //$this->currentTemplate=null;
        if (!$document) {
            return null;
        }
        if ($document->getAuItPrintTemplate()) {
            return $document->getAuItPrintTemplate();
        }
        $template = $this->_templateHelper->getBestTemplateForDocument($document, $this->_useTemplateId);
        $document->setAuItPrintTemplate($template);

        return $template;
    }
}
