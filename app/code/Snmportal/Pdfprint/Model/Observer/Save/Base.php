<?php

namespace Snmportal\Pdfprint\Model\Observer\Save;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Model\AbstractModel;
use Snmportal\Pdfprint\Helper\Template;

class Base
{
    /**
     *
     * @var Template
     */
    private $templateHelper;

    /**
     *
     * @var \Snmportal\Pdfprint\Helper\Base
     */
    private $baseHelper;

    /**
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Base constructor.
     *
     * @param Template $templateHelper
     * @param \Snmportal\Pdfprint\Helper\Base $baseHelper
     * @param Filesystem $filesystem
     */
    public function __construct(
        Template $templateHelper,
        \Snmportal\Pdfprint\Helper\Base $baseHelper,
        Filesystem $filesystem
    ) {
        $this->templateHelper = $templateHelper;
        $this->baseHelper = $baseHelper;
        $this->filesystem = $filesystem;
    }

    /**
     * @param AbstractModel $document
     *
     * @throws FileSystemException
     */
    protected function createAndSaveDocument(AbstractModel $document)
    {

        $typ = $this->templateHelper->mapDocToTyp($document);
        $typ = $this->templateHelper->getTypinfo($typ);
        $saveFile = trim(
            $this->baseHelper->getStoreConfig('snmportal_pdfprint/' . $typ[0] . '/save_to', $document->getStore())
        );
        if ($saveFile) {
            $baseDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
            try {
                $filename = $saveFile . '.pdf';
                $engine = $this->templateHelper->getEngineForDocument($document);
                if ($engine) {
                    $pdf = $engine->getPdf([$document]);
                    $filename = $engine->filterValue($filename);
                    $pfad = dirname($filename) . '/';
                    if (!$baseDirectory->isWritable($pfad)) {
                        $baseDirectory->create($pfad);
                    }
                    $baseDirectory->writeFile($filename, $pdf->render());
                }
            } catch (Exception $e) {
                //error_log("\n" . print_r($e->getMessage(), true), 3, 'auit.log');
            }
        }
    }
}
