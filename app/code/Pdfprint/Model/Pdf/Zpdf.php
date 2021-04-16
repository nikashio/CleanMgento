<?php

namespace Snmportal\Pdfprint\Model\Pdf;

use Zend_Pdf;

class Zpdf extends Zend_Pdf
{
    protected $stream;

    /**
     * @param $stream
     */
    public function setTCPFStream($stream)
    {
        $this->stream = $stream;
    }

    /**
     * @param bool $newSegmentOnly
     * @param null $outputStream
     *
     * @return string
     */
    public function render($newSegmentOnly = false, $outputStream = null)
    {
        if ($outputStream === null) {
            return $this->stream;
        }
        $pdfData = $this->stream;
        while (strlen($pdfData) > 0 && ($byteCount = fwrite($outputStream, $pdfData)) != false) {
            $pdfData = substr($pdfData, $byteCount);
        }

        return '';
    }
}
