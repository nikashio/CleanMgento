<?php

namespace Snmportal\Pdfprint\Controller\Adminhtml\Template;

use Exception;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\Layout;
use Magento\ImportExport\Block\Adminhtml\Import\Frame\Result as ImportResultBlock;
use Magento\ImportExport\Controller\Adminhtml\Import as ImportController;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Adapter as ImportAdapter;

class Validate extends ImportController
{
    /**
     * Process validation results
     *
     * @param Import            $import
     * @param ImportResultBlock $resultBlock
     *
     * @return void
     * @throws LocalizedException
     */
    protected function processValidationError(
        Import $import,
        ImportResultBlock $resultBlock
    ) {
        if ($import->getProcessedRowsCount() == $import->getInvalidRowsCount()) {
            $resultBlock->addNotice(__('This file is invalid. Please fix errors and re-upload the file.'));
        } elseif ($import->getErrorsCount() >= $import->getErrorsLimit()) {
            $resultBlock->addNotice(
                __(
                    'You\'ve reached an error limit (%1). Please fix errors and re-upload the file.',
                    $import->getErrorsLimit()
                )
            );
        } else {
            if ($import->isImportAllowed()) {
                $resultBlock->addNotice(
                    __(
                        'Please fix errors and re-upload the file. Or press "Import" to skip rows with errors.'
                    ),
                    true
                );
            } else {
                $resultBlock->addNotice(
                    __('The file is partially valid, but we can\'t import it for some reason.'),
                    false
                );
            }
        }
        // errors info
        foreach ($import->getErrors() as $errorCode => $rows) {
            $error = $errorCode . ' ' . __('in rows:') . ' ' . implode(', ', $rows);
            $resultBlock->addError($error);
        }
    }

    /**
     * Validate uploaded files action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()
                     ->getPostValue();
        /** @var Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        /** @var $resultBlock ImportResultBlock */
        $resultBlock = $resultLayout->getLayout()
                                    ->getBlock('import.frame.result');
        if ($data) {
            // common actions
            $resultBlock->addAction(
                'show',
                'import_validation_container'
            );

            try {
                /** @var $import Import */
                $import = $this->_objectManager->create('Magento\ImportExport\Model\Import')
                                               ->setData($data);
                $source = ImportAdapter::findAdapterFor(
                    $import->uploadSource(),
                    $this->_objectManager->create('Magento\Framework\Filesystem')
                                         ->getDirectoryWrite(DirectoryList::ROOT),
                    $data[$import::FIELD_FIELD_SEPARATOR]
                );
                $validationResult = $import->validateSource($source);

                if (!$import->getProcessedRowsCount()) {
                    $resultBlock->addError(__('This file is empty. Please try another one.'));
                } else {
                    if (!$validationResult) {
                        $this->processValidationError($import, $resultBlock);
                    } else {
                        if ($import->isImportAllowed()) {
                            $resultBlock->addSuccess(
                                __('File is valid! To start import process press "Import" button'),
                                true
                            );
                        } else {
                            $resultBlock->addError(
                                __('The file is valid, but we can\'t import it for some reason.')
                            );
                        }
                    }
                    $resultBlock->addNotice($import->getNotices());
                    $resultBlock->addNotice(
                        __(
                            'Checked rows: %1, checked entities: %2, invalid rows: %3, total errors: %4',
                            $import->getProcessedRowsCount(),
                            $import->getProcessedEntitiesCount(),
                            $import->getInvalidRowsCount(),
                            $import->getErrorsCount()
                        )
                    );
                }
            } catch (Exception $e) {
                $resultBlock->addNotice(__('Please fix errors and re-upload the file.'))
                            ->addError($e->getMessage());
            }

            return $resultLayout;
//        } elseif ($this->getRequest()->isPost() && empty($_FILES)) {
        } elseif ($this->getRequest()
                       ->isPost()) {
            $resultBlock->addError(__('The file was not uploaded.'));

            return $resultLayout;
        }
        $this->messageManager->addError(__('Sorry, but the data is invalid or the file is not uploaded.'));
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('adminhtml/*/index');

        return $resultRedirect;
    }
}
