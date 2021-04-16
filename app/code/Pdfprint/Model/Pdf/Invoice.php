<?php

namespace Snmportal\Pdfprint\Model\Pdf;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Block\Adminhtml\Items\AbstractItems;

class Invoice extends Base
{
    /**
     * @var AbstractItems
     */
    protected $helperBlock;

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
        $this->registry->register('current_order', $document->getOrder());
        if ($this->registry->registry('current_invoice')) {
            $this->registry->unregister('current_invoice');
        }
        $this->registry->register('current_invoice', $document);


        $page->addHandle('sales_order_printinvoice');
        try {
            $block = $page->getLayout()
                          ->getBlock('sales.order.print.invoice');
            if ($block) {
                $this->helperBlock = $page->getLayout()
                                          ->createBlock('Magento\Sales\Block\Adminhtml\Items\AbstractItems');
                $this->buildPaymentInfo($document, $page, $block);

                return $this->parseLayout($block->toHtml());
            }
        } catch (Exception $e) {
// FALLBACK
        }
        $page->setBlankTheme();
        $page->addHandle('sales_order_printinvoice');
        $block = $page->getLayout()
                      ->getBlock('sales.order.print.invoice');
        if ($block) {
            $this->helperBlock = $page->getLayout()
                                      ->createBlock('Magento\Sales\Block\Adminhtml\Items\AbstractItems');
            $this->buildPaymentInfo($document, $page, $block);

            return $this->parseLayout($block->toHtml());
        }
        throw new LocalizedException(
            __('No layout "sales_order_printinvoice" block: "sales.order.print.invoice" found!')
        );
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
