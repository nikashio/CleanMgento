<?php

namespace Snmportal\Pdfprint\Controller\Adminhtml\AbstractController;

use Magento\Backend\App\Action;

abstract class PrintAction extends Action
{
    /*
    public function downloadPDF(\Snmportal\Pdfprint\Model\Pdf\Base $engine,$document)
    {
        $content = $engine->getPdf([$document])->render();
        $contentLength = null;
        $fileName = $engine->getEmailFilename();
        $contentType = 'application/pdf';

        $this->_response->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true)
            ->setHeader('Content-Length', $contentLength === null ? strlen($content) : $contentLength, true)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"', true)
            ->setHeader('Last-Modified', date('r'), true);

        $this->_response->sendHeaders();
        $this->getResponse()->setBody($content);
        $this->getResponse()->sendResponse();

//        echo $content;
  //      flush();
    //    exit();

     //   $this->downloadPDF($this->orderPdfFactory->create());


    }
    */
}
