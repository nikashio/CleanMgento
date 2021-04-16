<?php

namespace Snmportal\Pdfprint\Plugin\App\Response\Http;

use Closure;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory as MFileFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;

class FileFactory
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Filesystem           $filesystem
     * @param ScopeConfigInterface $scopeConfig
     *
     * @internal param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        Filesystem $filesystem,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->filesystem = $filesystem;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param MFileFactory $subject
     * @param Closure      $proceed
     * @param              $fileName
     * @param              $content
     * @param string       $baseDir
     * @param string       $contentType
     * @param null         $contentLength
     *
     * @return mixed
     * @throws FileSystemException
     */
    public function aroundCreate(
        MFileFactory $subject,
        Closure $proceed,
        $fileName,
        $content,
        $baseDir = DirectoryList::ROOT,
        $contentType = 'application/octet-stream',
        $contentLength = null
    ) {
        $result = $proceed($fileName, $content, $baseDir, $contentType, $contentLength);
        if (!is_array($content) && $baseDir == DirectoryList::VAR_DIR) {
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            if ($ext == 'pdf') {
                if ($this->scopeConfig->getValue('snmportal_pdfprint/general/deletevarfile')) {
                    $dir = $this->filesystem->getDirectoryWrite($baseDir);
                    if ($dir->isFile($fileName)) {
                        $dir->delete($fileName);
                    }
                }
            }
        }

        return $result;
    }
}
