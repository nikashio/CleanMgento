<?php

namespace Snmportal\Pdfprint\Controller\Adminhtml;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

class FileFactory extends \Magento\Framework\App\Response\Http\FileFactory
{
    /**
     * @param string       $fileName
     * @param array|string $content
     * @param string       $baseDir
     * @param string       $contentType
     * @param null         $contentLength
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function create(
        $fileName,
        $content,
        $baseDir = DirectoryList::ROOT,
        $contentType = 'application/octet-stream',
        $contentLength = null
    ) {
        return parent::create($fileName, $content, $baseDir, $contentType, $contentLength);
    }
}
