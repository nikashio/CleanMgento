<?php
// @codingStandardsIgnoreFile

namespace Snmportal\Pdfprint\Block;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
class Helper extends Template
{
    protected $_evalTemplate;

    protected $blockTemplate = null;

    /**
     * @var WriteInterface
     */
    protected $_tmpDirectory;

    public function __construct(
        Context $context,
        Filesystem $filesystem,
        array $data = []
    ) {
        $this->_tmpDirectory = $filesystem->getDirectoryWrite(DirectoryList::TMP);

        parent::__construct($context, $data);
    }

    public function getTemplate()
    {
        return 'Snmportal_PdfPrint:muster.phtml';
    }

    public function fetchView($fileName)
    {
        $html = '';
        if ($this->blockTemplate) {
            $this->_evalTemplate = $this->blockTemplate;
            $fn = uniqid('snmpdf', true) . '.tmp';
            $this->_tmpDirectory->writeFile($fn, $this->blockTemplate['value']);
            $fileName = $this->_tmpDirectory->getAbsolutePath($fn);
            try {
                $templateEngine = $this->templateEnginePool->get('phtml');
                $html = $templateEngine->render(
                    $this->templateContext,
                    $fileName,
                    $this->getTemplateFilter()
                         ->getVariables()
                );//$this->_viewVars);
            } catch (Exception $e) {
                $html = $e->getMessage();
            }
            $this->_tmpDirectory->delete($fn);
        }

        return $html;
    }

    /**
     * @return Phrase|string
     */
    protected function _toHtml()
    {
        if (!$this->getBlockId()) {
            return '';
        }
        $blockId = $this->getBlockId();
        if ( $this->getTemplateFilter() &&
            $this->getTemplateFilter()->auitVariable('templateinfo'))
        {
            $template = $this->getTemplateFilter()->auitVariable('templateinfo');
            if ( is_array($template) && count($template) > 0 )
            {
                $template = $template[0];
                $this->blockTemplate = $template->getBlockTemplate($blockId);
                if ( $this->blockTemplate !== false ){
                    return parent::_toHtml();
                }
            }
        }
        return __('Block template not found: %1', $blockId);
    }
}
