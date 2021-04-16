<?php
// @codingStandardsIgnoreFile

namespace Snmportal\Pdfprint\Helper;

use Laminas\Mime\Mime;
use Laminas\Mime\Part;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Mail\MimeMessage;
use Magento\Framework\Mail\TransportInterface;
use Zend_Mime_Part;
class Email extends AbstractHelper
{
    /**
     * @var []
     */
    private $attachments = [];

    public function addAttachment($attachment)
    {
        $this->attachments[] = $attachment;
    }

    public function addAttachments(TransportInterface $transport)
    {

        if (empty($this->attachments)) {
            return;
        }
        $body = $transport->getMessage()
                          ->getBody();


        // >= M2.3.3
        if (0 && class_exists('\Magento\Framework\Mail\MimeMessage', false) && $body instanceof MimeMessage) {
            foreach ($this->attachments as $attachment) {
                $attachmentPart = new Zend_Mime_Part($attachment->getContent());
                $attachmentPart->filename = $this->_encodedFileName($attachment->getFilename());
                $attachmentPart->type = $attachment->getType();
                $attachmentPart->encoding = $attachment->getEncoding();
                $attachmentPart->disposition = $attachment->getDisposition();
                $transport->getMessage()
                          ->addPart($attachmentPart);
            }
            $transport->getMessage()
                      ->setMessageType('multipart/related');
        } else {
            if (get_class($body) == 'Zend_Mime_Part') {
                foreach ($this->attachments as $attachment) {
                    $attachmentPart = new Zend_Mime_Part($attachment->getContent());
                    $attachmentPart->filename = $this->_encodedFileName($attachment->getFilename());
                    $attachmentPart->type = $attachment->getType();
                    $attachmentPart->encoding = $attachment->getEncoding();
                    $attachmentPart->disposition = $attachment->getDisposition();
                    $transport->getMessage()
                              ->addPart($attachmentPart);
                }
                $transport->getMessage()
                          ->setMessageType('multipart/related');
            } else {
                if (get_class($body) == 'Zend\Mime\Message') {
                    foreach ($this->attachments as $attachment) {
                        $attachmentPart = new \Zend\Mime\Part($attachment->getContent());
                        $attachmentPart->filename = $this->_encodedFileName($attachment->getFilename());
                        $attachmentPart->type = $attachment->getType();
                        $attachmentPart->encoding = $attachment->getEncoding();
                        $attachmentPart->disposition = $attachment->getDisposition();
                        $body->addPart($attachmentPart);
                    }
                    $transport->getMessage()
                              ->setBody($body);
                    $transport->getMessage()
                              ->setMessageType(\Zend\Mime\Mime::MULTIPART_RELATED);
                } // 2.3.5
                else {
                    if (get_class($body) == 'Laminas\Mime\Message') {
                        foreach ($this->attachments as $attachment) {
                            $attachmentPart = new Part($attachment->getContent());
                            $attachmentPart->filename = $this->_encodedFileName($attachment->getFilename());
                            $attachmentPart->type = $attachment->getType();
                            $attachmentPart->encoding = $attachment->getEncoding();
                            $attachmentPart->disposition = $attachment->getDisposition();
                            $body->addPart($attachmentPart);
                        }
                        $transport->getMessage()
                                  ->setBody($body);
                        $transport->getMessage()
                                  ->setMessageType(Mime::MULTIPART_RELATED);
                    }
                }
            }
        }
        $this->attachments = [];
    }

    private function _encodedFileName($subject)
    {
        if (strpos($subject, '=?utf-8') !== false) {
            return $subject;
        }

        return sprintf('=?utf-8?B?%s?=', base64_encode($subject));
    }
}
