<?php

namespace Snmportal\Pdfprint\Model\Pdf;

use DOMDocument;
use DOMXpath;
use Exception;
use Magento\Checkout\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\GiftMessage\Helper\MessageFactory;
use Magento\Sales\Block\Adminhtml\Items\AbstractItems;
use Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Pdf\AbstractPdf;
use Magento\Sales\Model\Order\Pdf\Config;
use Magento\Sales\Model\Order\Pdf\ItemsFactory;
use Magento\Sales\Model\Order\Pdf\Total\Factory;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Snmportal\Pdfbase\Fpdi\FPDIProtection;
use Snmportal\Pdfbase\I18N\Arabic\Glyphs;
use Snmportal\Pdfprint\Logger\Logger;
use Snmportal\Pdfprint\Model\Pdf\Filter\Helper;
use Snmportal\Pdfprint\Model\Pdf\Filter\HelperFactory;
use Snmportal\Pdfprint\Model\ResourceModel\Template\CollectionFactory;
use Snmportal\Pdfprint\Model\Template;
use Zend_Pdf;
use Zend_Pdf_Page;

//Autoloader::register();


abstract class Base extends AbstractPdf
{
    const ALIAS_NUM_PAGE = '{:pnp:}';

    const ALIAS_TOT_PAGES = '{:ptp:}';

    /**
     * @var AbstractItems
     */
    protected $helperBlock;

    /**
     * @var Renderer
     */
    protected $_pdfRenderer;

    protected $HTMLDocuemnts = [];

    protected $_page_nr;

    protected $reflowDocumentId;

    protected $docPageCount = 0;

    /**
     * @var Emulation
     */
    protected $_appEmulation;

    protected $document;

    protected $_paymentInfo;

    protected $_paymentDocInfo = [];

    /**
     * @var Logger
     */
    protected $_snmLogger;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
//    protected $_layoutFactory;

    /**
     * @var LayoutFactory
     */
    //protected $resultLayoutFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var PageFactory
     */
    //protected $resultPageFactory;
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var DesignInterface
     */
    protected $design;

    /**
     * @var State
     */
    protected $appState;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var FilterFactory
     */
    protected $filterFactory;

    /**
     * @var HelperFactory
     */
    protected $helperFactory;

    /**
     * @var Filter
     */
    protected $_processor;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * Customer repository
     *
     * @var CustomerRepositoryInterface
     */
//    protected $customerRepository;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var CountryFactory
     */
    protected $_countryFactory;

    protected $_documents;

    /**
     * @var WriteInterface
     */
    protected $systemTmpDirectory;

    /**
     * @var WriteInterface
     */
    protected $varDirectory;


    protected $pageBackgrounds = [];

    protected $pageNumbering = [];

    /**
     *
     * @var \Snmportal\Pdfprint\Helper\Template
     */
    protected $_templateHelper;

    /**
     * Checkout helper
     *
     * @var Data
     */
    protected $_checkoutHelper;

    protected $_useTemplateId;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     *
     * @var \Snmportal\Pdfprint\Helper\Base
     */
    private $baseHelper;

    private $paymenInfoBlock;

    /**
     * Base constructor.
     *
     * @param \Magento\Payment\Helper\Data        $paymentData
     * @param StringUtils                         $string
     * @param ScopeConfigInterface                $scopeConfig
     * @param Filesystem                          $filesystem
     * @param Config                              $pdfConfig
     * @param Factory                             $pdfTotalFactory
     * @param ItemsFactory                        $pdfItemsFactory
     * @param TimezoneInterface                   $localeDate
     * @param StateInterface                      $inlineTranslation
     * @param Address\Renderer                    $addressRenderer
     * @param StoreManagerInterface               $storeManager
     * @param ResolverInterface                   $localeResolver
     * @param ResultFactory                       $resultFactory
     * @param Logger                              $snmLogger
     * @param Registry                            $registry
     * @param MessageFactory                      $messageFactory
     * @param FilterFactory                       $filterFactory
     * @param HelperFactory                       $helperFactory
     * @param CustomerFactory                     $customerFactory
     * @param CountryFactory                      $countryFactory
     * @param Emulation                           $appEmulation
     * @param State                               $appState
     * @param DesignInterface                     $design
     * @param CollectionFactory                   $collectionFactory
     * @param \Snmportal\Pdfprint\Helper\Template $templateHelper
     * @param \Snmportal\Pdfprint\Helper\Base     $baseHelper
     * @param Data                                $checkoutHelper
     * @param ProductMetadataInterface            $productMetadata
     * @param array                               $data
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem,
        Config $pdfConfig,
        Factory $pdfTotalFactory,
        ItemsFactory $pdfItemsFactory,
        TimezoneInterface $localeDate,
        StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        StoreManagerInterface $storeManager,
        ResolverInterface $localeResolver,
        //        \Magento\Framework\App\ViewInterface $view,
        //\Magento\Framework\View\LayoutFactory $layoutFactory,
        //\Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        ResultFactory $resultFactory,
        Logger $snmLogger,
        //\Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Registry $registry,
        MessageFactory $messageFactory,
        FilterFactory $filterFactory,
        HelperFactory $helperFactory,
        //\Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        CustomerFactory $customerFactory,
        CountryFactory $countryFactory,
        Emulation $appEmulation,
        State $appState,
        DesignInterface $design,
        CollectionFactory $collectionFactory,
        \Snmportal\Pdfprint\Helper\Template $templateHelper,
        \Snmportal\Pdfprint\Helper\Base $baseHelper,
        Data $checkoutHelper,
        ProductMetadataInterface $productMetadata,
        array $data = []
    ) {
        $this->productMetadata = $productMetadata;
        $this->_templateHelper = $templateHelper;
        $this->baseHelper = $baseHelper;
        $this->_checkoutHelper = $checkoutHelper;
        $this->_snmLogger = $snmLogger;
        $this->registry = $registry;
        $this->resultFactory = $resultFactory;
        $this->_storeManager = $storeManager;
        $this->_localeResolver = $localeResolver;
        $this->_countryFactory = $countryFactory;
        $this->_appEmulation = $appEmulation;
        //$this->_layoutFactory = $layoutFactory;
        //$this->resultLayoutFactory = $resultLayoutFactory;
        //$this->resultPageFactory = $resultPageFactory;
        $this->collectionFactory = $collectionFactory;
        $this->messageFactory = $messageFactory;
        //$this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->design = $design;
        $this->appState = $appState;
        $this->filterFactory = $filterFactory;
        $this->helperFactory = $helperFactory;
        $this->systemTmpDirectory = $filesystem->getDirectoryWrite(DirectoryList::TMP);
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::CACHE);


        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $data
        );
        if (!defined("SNMDOMPDF_MEDIA_DIR")) {
            define("SNMDOMPDF_MEDIA_DIR", __DIR__ . '/../../media');
        }
    }

    /**
     * @param $document
     *
     * @return array
     * @throws LocalizedException
     */
    public function getEmailAttachments($document)
    {
        $templateInfo = $this->getTemplateInfo($document);
        if ($templateInfo) {
            return $templateInfo->getEmailAttachments();
        }

        return [];
    }


    //abstract protected function parseLayout($html);

    /**
     * @param $document
     *
     * @return Template
     */
    abstract protected function getTemplateInfo($document);

    /**
     * @return string
     */
    public function getEmailFilename()
    {
        $fileName = '';
        if ($this->HTMLDocuemnts && count($this->HTMLDocuemnts)) {
            foreach ($this->HTMLDocuemnts as $doc) {
                if ($fileName) {
                    $fileName .= '-';
                }
                $fileName .= $doc['pdfname'];
            }
        }

        if (!$fileName) {
            $fileName = 'unknown';
        }

        return $fileName . '.pdf';
    }

    /**
     * @param $templateId
     *
     * @return $this
     */
    public function setRenderTemplateId($templateId)
    {
        $this->_useTemplateId = $templateId;

        return $this;
    }

    public function drawLineBlocks(Zend_Pdf_Page $page, array $draw, array $pageSettings = [])
    {
        return $page;
    }

    /**
     * @param $info
     */
    public function _endPage($info)
    {
    }

    /**
     * @param $info
     *
     * @throws LocalizedException
     */
    public function _beginPageReflow($info)
    {
        $canvas = $info['canvas'];
        $frame = $info['frame'];
        switch ($frame->get_node()->tagName) {
            case 'body':
                $docId = $frame->get_node()
                               ->getAttribute('data-id');
                if ($docId != $this->reflowDocumentId) {
                    $this->reflowDocumentId = $docId;
                    $this->_page_nr = 0;
                    $this->document = $this->getPrintDocument($docId);

                    $this->_appEmulation->stopEnvironmentEmulation();
                    $this->_appEmulation->startEnvironmentEmulation(
                        $this->document->getStoreId(),
                        Area::AREA_FRONTEND,
                        true
                    );

                    $this->_processor = null;
                    if (!$this->getTemplateInfo($this->document)) {
                        throw new LocalizedException(
                            __('No template found! Please add a pdfPRINT template for this document/store.')
                        );
                    }
                }
                break;
            default:
                break;
        }
        $pn = $this->_page_nr + 1;
        if ($template = $this->getTemplateInfo($this->document)) {
            $x = $this->getPDFRenderer()
                      ->mm2pt($template->getMargin($pn, 'left'));
            $y = $this->getPDFRenderer()
                      ->mm2pt($template->getMargin($pn, 'top'));
            $w = $this->getPDFRenderer()
                      ->mm2pt($template->getMargin($pn, 'right'));
            $h = $this->getPDFRenderer()
                      ->mm2pt($template->getMargin($pn, 'bottom'));
            $w = $canvas->get_width() - $w - $x;
            $h = $canvas->get_height() - $h;
            if ($frame->get_root()) {
                $frame->set_containing_block($x, $y, $w, null);
                $frame->get_root()
                      ->set_containing_block($x, $y, $w, $h);
            }
        }
    }

    /**
     * @param $docId
     *
     * @return null
     */
    protected function getPrintDocument($docId)
    {
        foreach ($this->_documents as $document) {
            if ($document->getId() == $docId) {
                return $document;
            }
        }

        return null;
    }

    /**
     * @return Renderer
     * @throws LocalizedException
     */
    protected function getPDFRenderer()
    {
        if (!$this->_pdfRenderer) {
            //$this->logMessage("+++getPDFRenderer");
            $bgTemplate = $this->getPdfTemplate($this->document);
            $options = [];
            $options['defaultPaperSize'] = 'a4';

            //$options['enable_php']=true;
            $options['enable_remote'] = true;
            $options['enable_font_subsetting'] = true;
            $options['temp_dir'] = $this->systemTmpDirectory->getAbsolutePath();
            //font_dir
            $options['font_cache'] = $this->varDirectory->getAbsolutePath('snm-pdfprintfont');
            $options['log_output_file'] = $this->varDirectory->getAbsolutePath('snm-pdfprintfont/log.htm');
            $this->varDirectory->create('snm-pdfprintfont');



            if ($bgTemplate) {
                $fpdf = new FPDIProtection('P', 'pt');
                $fpdf->setSourceFile($bgTemplate);
                $tplidx = $fpdf->importPage(1);
                if ($tplidx) {
                    $size = $fpdf->getTemplateSize($tplidx, 0, 0);
                    $options['defaultPaperSize'] = [0, 0, $size['w'], $size['h']];
                }
            }
            $this->_pdfRenderer = new Renderer($options);//$this);
            $this->_pdfRenderer->setCallbacks(
                [

                    ['event' => 'begin_page_reflow', 'f' => [$this, '_beginPageReflow']],
                    //array('event'=>'end_page_render','f'=>array($this,'_beginPage')),
                    ['event' => 'begin_page_render', 'f' => [$this, '_beginPage']],
                ]
            );
        }

        return $this->_pdfRenderer;
    }

    /**
     * @param $document
     *
     * @return bool|string
     * @throws LocalizedException
     */
    protected function getPdfTemplate($document)
    {
        if ($this->getTemplateInfo($document)) {
            return $this->getTemplateInfo($document)
                        ->getFullPath('pdf_background');
        }

        return false;
    }

    /**
     * @param $info
     *
     * @throws Exception
     */
    public function _beginPage($info)
    {
        $canvas = $info['canvas'];
        $this->_page_nr++;
        $pageNumber = $this->_page_nr;
        $allpageNumber = $canvas->get_page_number();
        if (!$this->docPageCount) {
            if (!isset($this->pageNumbering[$this->document->getId()])) {
                $this->pageNumbering[$this->document->getId()] = [
                    'allpages'  => $pageNumber,
                    'startpage' => $allpageNumber
                ];
            } else {
                $this->pageNumbering[$this->document->getId()]['allpages'] = $pageNumber;
            }
        }
        $this->pageBackgrounds[$allpageNumber] = [];
        if ($this->getPdfTemplate($this->document)) {
            $this->pageBackgrounds[$allpageNumber] = [
                'source' => $this->getPdfTemplate($this->document),
                'page'   => $pageNumber == 1 ? 1 : 2
            ];
        }
        $this->insertFreeItems($info, $this->document);
    }

    /**
     * @param $info
     * @param $document
     *
     * @throws Exception
     */
    protected function insertFreeItems($info, $document)
    {
        $canvas = $info['canvas'];
        $docPageNumber = $canvas->get_page_number();
        $docPageCount = ($this->docPageCount) ? $this->docPageCount : $canvas->get_page_number();
        if ($this->docPageCount && $this->getStoreConfig(
            'snmportal_pdfprint/general/pagenumbering',
            $document->getStoreId()
        ) == 1) {
            $docPageCount = $this->pageNumbering[$document->getId()]['allpages'];
            $docPageNumber = $docPageNumber - $this->pageNumbering[$document->getId()]['startpage'] + 1;
        }
        $pageNumber = $this->_page_nr;


        $items = $this->getFreeItems($pageNumber <= 1 ? 1 : 2, $document);
        $docCSS = '';
        if ($this->getTemplateInfo($document)) {
            $docCSS = $this->getTemplateInfo($document)
                           ->getCss();
        }
        if ($items && is_array($items)) {
            foreach ($items as $item) {
                $blockInfo = $this->getStyleItem($item);
                if (!$blockInfo) {
                    continue;
                }
                $v = $blockInfo['value'];
                if (strpos($v, '{{') !== false) {
                    $v = $this->getProcessor()
                              ->filter($v);
                }
                $v = str_replace(
                    [self::ALIAS_NUM_PAGE, self::ALIAS_TOT_PAGES],
                    [$docPageNumber, $docPageCount],
                    $v
                );
                //$v = $this->checkArabic($v);
                // $v = $this->getPDFRenderer()->checkRTL($v);
                $this->drawBlock($v, $blockInfo, $docCSS);
            }
        }
    }

    /**
     * @param      $pfad
     * @param null $store
     *
     * @return mixed
     */
    protected function getStoreConfig($pfad, $store = null)
    {
        return $this->_scopeConfig->getValue(
            $pfad,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param $page
     * @param $document
     *
     * @return array|mixed
     */
    protected function getFreeItems($page, $document)
    {
        $result = [];
        if ($this->getTemplateInfo($document)) {
            $result = $this->getTemplateInfo($document)
                           ->getFreePrintItems($page);
        }

        return $result;
    }

    /**
     * @param $items
     *
     * @return mixed
     */
    protected function getStyleItem($items)
    {
        $blockInfo = [
            'x_pos'  => 10,
            'y_pos'  => 10,
            'width'  => 60,
            'height' => 40,
            'style'  => 'default',
            'script' => 'markup',
            'value'  => ''
        ];
        $items = $this->setArrayDefault($items, $blockInfo);

        return $items;//Mage::getModel('auit_pdf/pdf_style',$items);
    }

    /**
     * @param $x
     * @param $defaults
     *
     * @return mixed
     */
    protected function setArrayDefault($x, $defaults)
    {
        foreach ($defaults as $key => $v) {
            if (!isset($x[$key]) || trim($x[$key]) === '') {
                $x[$key] = $v;
            }
        }

        return $x;
    }

    /**
     * @return Filter
     */
    protected function getProcessor()
    {
        if (!$this->_processor) {
            //$this->logMessage("+++getProcessor");
            $order = $this->getCurrentOrder();
            $processor = $this->filterFactory->create();
            if ( method_exists($processor,'setStrictMode') ) {
                $processor->setStrictMode(false);
            }


            /**
             * $tracksCollection = $order->getTracksCollection();
             * $trackingInfo = '';
             * foreach($tracksCollection->getItems() as $track) {
             * error_log("\n" . print_r($track->debug(), true), 3, 'auit.log');
             * if ( empty($trackingInfo))
             * $trackingInfo='<table class="trackinginfo" cellpadding="0" cellspacing="0">';
             * $trackingInfo .= '<tr><td class="title">'.$track->getData('title').'</td>';
             * $trackingInfo .= '<td class="number">'.$track->getData('track_number').'</td>';
             * $trackingInfo .= '</tr>';
             * }
             *
             *
             * if ( !empty($trackingInfo))
             * $trackingInfo.='</table>';
             *
             * $_giftMessage = null;
             * if ( $order->getGiftMessageId() )
             * {
             * $giftMsg = $this->messageFactory->create();
             * $_giftMessage = $giftMsg->getGiftMessage($order->getGiftMessageId() );
             *
             * }
             * */
            /**
             * @var Helper $filterhelper
             */
            $filterhelper = $this->helperFactory->create();
            $filterhelper->setProcessor($processor)
                         ->setOrder($order);


            /***
             *
             */

            // \Magento\Sales\Model\Order\Address
            $this->_paymentInfo = [];
            if (isset($this->_paymentDocInfo[$this->document->getId()])) {
                $this->_paymentInfo = $this->_paymentDocInfo[$this->document->getId()];
            }
            $data = [
                'order'           => $order,
                'payment_info'    => $this->_paymentInfo ? $this->_paymentInfo['info'] : '',
                'payment_html'    => $this->_paymentInfo ? $this->_paymentInfo['html'] : '',
                'customer'        => $this->getOrderCustomer($order),
                'helper'          => $filterhelper,
                'addressRenderer' => $this->addressRenderer,
                'billingaddress'  => $this->getBillingAddress($this->document),
                'shippingaddress' => $this->getShippingAddress($this->document),
//                'tracking_info'=>$trackingInfo,
                'payment_method'  => $order->getPayment()
                                           ->getMethod(),
                'templateinfo'    => [$this->getTemplateInfo($this->document)],
                'entity'          => $this->document,
                'page_current'    => self::ALIAS_NUM_PAGE,
                'page_count'      => self::ALIAS_TOT_PAGES,
                'order_date'      => $filterhelper->formatDate($order->getStore(), $order->getCreatedAt()),
                //    'invoice_date' => Mage::helper('core')->formatDate($invoice->getCreatedAtDate(), 'medium', false),
                'entity_date'     => $filterhelper->formatDate($order->getStore(), $this->document->getCreatedAt())
            ];

            if ($this->document instanceof \Magento\Sales\Model\Order\Shipment) {
                $data['shipment'] = $this->document;
                $data['shipment_date'] = $filterhelper->formatDate($order->getStore(), $this->document->getCreatedAt());
            }
            if ($this->document instanceof \Magento\Sales\Model\Order\Creditmemo) {
                $data['creditmemo'] = $this->document;
                $data['creditmemo_date'] = $filterhelper->formatDate(
                    $order->getStore(),
                    $this->document->getCreatedAt()
                );
            }
            $processor->setVariables($data);
            $this->_processor = $processor;
        }

        return $this->_processor;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    protected function getCurrentOrder()
    {
        return $this->getDocumentOrder($this->document);
    }

    /**
     * @param $document
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function getDocumentOrder($document)
    {
        if ($document instanceof \Magento\Sales\Model\Order) {
            return $document;
        }
        if ($document) {
            return $document->getOrder();
        }

        return null;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return Customer
     */
    protected function getOrderCustomer(\Magento\Sales\Model\Order $order)
    {
        if ($order->getCustomer()) {
            return $order->getCustomer();
        }
        $customer = $this->customerFactory->create()
                                          ->load($order->getCustomerId());
        $order->setCustomer($customer);

        return $customer;
    }

    /**
     * @param $order
     *
     * @return null
     */
    protected function getBillingAddress($order)
    {
        if ($order->getBillingAddress()) {
            return $this->preFormatAddress($order->getBillingAddress());
        }

        return null;
    }

    /**
     * @param $address
     *
     * @return mixed
     */
    protected function preFormatAddress($address)
    {
        $address->explodeStreetAddress();
        if ($address->getCountryId() && !$address->getCountry()) {
            $address->setCountry(
                $this->_countryFactory->create()
                                      ->loadByCode($address->getCountryId())
                                      ->getName()
            );
        }

        return $address;
    }

    /**
     * @param $order
     *
     * @return Address
     */
    protected function getShippingAddress($order)
    {
        if ($order->getShippingAddress()) {
            return $this->preFormatAddress($order->getShippingAddress());
        }

        return null;
    }

    /**
     * @param $txt
     * @param $blockInfo
     * @param $docCSS
     */
    public function drawBlock($txt, $blockInfo, $docCSS)
    {
        $w = $blockInfo['width'];
        $h = $blockInfo['height'];
        $x = $blockInfo['x_pos'];
        $y = $blockInfo['y_pos'];

        $style = "position:absolute;left:{$x}mm;top:{$y}mm;width:{$w}mm;height:{$h}mm;";
        $html = '<div class="default" style="margin:0;padding:0;' . $style . '">';
        $html .= '<div style="margin:0;padding:0;" class="' . $blockInfo['style'] . '">' . $txt . '</div></div>';
        $this->logMessage("DRAW FREE ITEM\n" . $html);
        try {
            $this->getPDFRenderer()
                 ->writeHTML($html, $docCSS);
        } catch (Exception $e) {
            $this->logMessage("DRAW FREE ITEM can't draw block \n" . $e->getMessage());
        }
    }

    /**
     * @param $msg
     */
    protected function logMessage($msg)
    {
        $this->_snmLogger->info($msg);
    }

    public function getPdf($documents = [])
    {
        $this->_beforeGetPdf();
        $level = error_reporting();
        try {
            error_reporting(E_ERROR | E_WARNING | E_PARSE);
            $this->_documents = $documents;
            //$lastStoreId = 0;
            foreach ($documents as $document) {
                $this->_processor = null;
                $this->paymenInfoBlock = null;
                $this->document = $document;
                $this->logMessage(__("BUILD Info for document(%2):%1\n", $document->getId(), get_class($document)));
                $this->_appEmulation->startEnvironmentEmulation($document->getStoreId(), Area::AREA_FRONTEND, true);
                $this->appState->emulateAreaCode('frontend', [$this, 'renderDocument']);
                $this->_appEmulation->stopEnvironmentEmulation();
                $this->logMessage(__("BUILD End for document(%2):%1\n", $document->getId(), get_class($document)));
                //   $lastStoreId = $document->getStoreId();
            }
            $this->baseHelper->Log();
            $this->document = null;

            $allHTML = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html>';
            foreach ($this->HTMLDocuemnts as $htmlInfo) {
                $allHTML .= $htmlInfo['html'];
            }
            $allHTML .= '</html>';

            // STep 1
            $this->getPDFRenderer()
                 ->loadHtml($allHTML, 'UTF-8');
            //$this->_appEmulation->startEnvironmentEmulation($lastStoreId,\Magento\Framework\App\Area::AREA_FRONTEND,true);
            $this->getPDFRenderer()
                 ->render();
            $this->_appEmulation->stopEnvironmentEmulation();
            $this->logMessage("PAGE HTML\n" . $allHTML);
            // STep 2
            $this->docPageCount = $this->getPDFRenderer()
                                       ->getCanvas()
                                       ->get_page_count();
            $stringSubsetsText = $this->_pdfRenderer->getCanvas()
                                                    ->get_cpdf()->stringSubsetsText;
            if (1) {
                $this->_pdfRenderer = null;
                $this->pageBackgrounds = [];
                $this->reflowDocumentId = 0;
                $this->getPDFRenderer()
                     ->loadHtml($allHTML, 'UTF-8');
                $this->getPDFRenderer()->stringSubsetsText = $stringSubsetsText;
                $this->getPDFRenderer()
                     ->render();
                $this->_appEmulation->stopEnvironmentEmulation();
            }

            $contentPdfFilename = $this->getTmpFileName();
            $this->systemTmpDirectory->writeFile(
                $contentPdfFilename,
                $this->getPDFRenderer()
                     ->getStream()
            );
            $contentPdf = $this->systemTmpDirectory->getAbsolutePath($contentPdfFilename);

            // file_put_contents('test.pdf',file_get_contents($contentPdf));

            $fpdf = new FPDIProtection();
            $pageCount = $fpdf->setSourceFile($contentPdf);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {

                $fpdf->setSourceFile($contentPdf);
                $templateId = $fpdf->importPage($pageNo);
                $size = $fpdf->getTemplateSize($templateId);
                // create a page (landscape or portrait depending on the imported page size)
                if ($size['w'] > $size['h']) {
                    $fpdf->AddPage('L', [$size['w'], $size['h']]);
                } else {
                    $fpdf->AddPage('P', [$size['w'], $size['h']]);
                }
                if (isset($this->pageBackgrounds[$pageNo]['source']) && $this->pageBackgrounds[$pageNo]['source']) {
                    $fpdf->setSourceFile($this->pageBackgrounds[$pageNo]['source']);
                    try {
                        $templateIdBg = $fpdf->importPage($this->pageBackgrounds[$pageNo]['page']);
                        $fpdf->useTemplate($templateIdBg);
                    } catch (Exception $e) {

                    }
                }
                // use the imported page
                $fpdf->useTemplate($templateId);
            }

            if (count($this->HTMLDocuemnts) == 1) {
                /**
                 * Add Appendix...
                 */
                foreach ($this->HTMLDocuemnts as $htmlInfo) {
                    $appendix = $htmlInfo['appendix'];
                    if ($appendix) {
                        $pageCount = $fpdf->setSourceFile($appendix);
                        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {

                            $templateId = $fpdf->importPage($pageNo);
                            $size = $fpdf->getTemplateSize($templateId);
                            // create a page (landscape or portrait depending on the imported page size)
                            if ($size['w'] > $size['h']) {
                                $fpdf->AddPage('L', [$size['w'], $size['h']]);
                            } else {
                                $fpdf->AddPage('P', [$size['w'], $size['h']]);
                            }
                            // use the imported page
                            $fpdf->useTemplate($templateId);
                        }
                    }
                    break;
                }
            }
            /*
            foreach ( $this->pageBackgrounds as $bgFile )
            {
                @unlink($bgFile['source']);
            }
            */

            if ($this->document && $this->getTemplateInfo($this->document)) {
                $this->getTemplateInfo($this->document)
                     ->cleanFiles();
            }
        } catch (Exception $e) {
            //error_log("\n" . print_r($e->getTraceAsString(), true), 3, 'auit.log');
            $pdf = new Zend_Pdf();
            $this->_setPdf($pdf);
            $page = $this->_getPdf()
                         ->newPage(Zend_Pdf_Page::SIZE_A4);
            $this->_setFontBold($page, 8);
            $this->_getPdf()->pages[] = $page;

            if ($this->document) {
                $page->drawText(
                    __('Document-ID:%1, Increment-id: %2', $this->document->getId(), $this->document->getIncrementId()),
                    15,
                    820,
                    'UTF-8'
                );
            }
            foreach (str_split(__('Exception:') . $e->getMessage(), 140) as $idx => $v) {
                $page->drawText($v, 15, 800 - ($idx * 9), 'UTF-8');
            }
            error_reporting($level);

            return $this->_getPdf();
        }
        $this->_afterGetPdf();
        error_reporting($level);


        $pdf = new Zpdf();
        $this->_setPdf($pdf);
        //file_put_contents('test2.pdf',$fpdf->Output('S'));
        $pdf->setTCPFStream($fpdf->Output('S'));

        $this->_pdfRenderer = null;
        $this->systemTmpDirectory->delete($contentPdfFilename);

        return $this->_getPdf();
    }

    /**
     * @return string
     */
    protected function getTmpFileName()
    {
        return uniqid('snmpdf', true);
    }

    public function newPage(array $settings = [])
    {
    }

    /**
     * @param $lines
     * @param $blockName
     */
    public function drawLines($lines, $blockName)
    {
    }

    public function renderDocument()
    {
        $document = $this->document;
        $order = $this->getDocumentOrder($document);
        $this->_localeResolver->emulate(
            $order->getStore()
                  ->getId()
        );

//        $order->getStore()->getDefaultCurrency()->get
        //      $this->_localeResolver->setLocale()
        // Bug Fix reset Locale
//        $reflectionProperty = new \ReflectionProperty($order, '_orderCurrency');
        //      $reflectionProperty->setAccessible(true);
        //    $reflectionProperty->setValue($order,null);
        //  $currency = $order->getOrderCurrency();

        $template = $this->getTemplateInfo($document);
        if (!$template) {
            throw new LocalizedException(
                __('No template found! Please add a pdfPRINT template for this document/store.')
            );
        }

        $layoutInfo = $this->buildlayout($document);

        $html = '';
        $html .= '<style>@page {  margin: 0cm;} .product-img{height:10mm;}' . $template->getCss(
        ) . '</style><body class="default" data-id="' . $document->getId() . '">';
        if ($tmp = $this->getBuildBeforeTableText($document)) {
            $html .= '<div class="before-table-text">' . $tmp . '</div>';
        }


        if ($tmp = $this->renderDataTable($document, $layoutInfo)) {
            $html .= '<div class="data-table">';
            $html .= $tmp;
            $html .= $this->renderTotalTable($document, $layoutInfo);
            $html .= '</div>';
        }
        if ($tmp = $this->getBuildAfterTableText($document)) {
            $html .= '<div class="after-table-text">' . $tmp . '</div>';
        }
        $html .= '</body>';
        $this->HTMLDocuemnts[] = [
            'html'     => $html,
            'pdfname'  => $this->getProcessor()
                               ->filter($template->getAttachFilename()),
            'appendix' => $template->getAppendixPath()
        ];

        return;
    }

    /**
     * @param      $document
     * @param bool $boutput
     *
     * @return mixed
     */
    protected function buildlayout($document, $boutput = true)
    {
        //     $this->design->setDesignTheme('Magento/blank', 'frontend');

        $page = $this->resultFactory->create();
        $block = null;
        if ($this->registry->registry('current_order')) {
            $this->registry->unregister('current_order');
        }
        $result = $this->loadlayout($page, $document, $boutput);
        $this->_paymentDocInfo[$document->getId()] = $this->_paymentInfo;

        return $result;
    }

    /**
     * @param      $page
     * @param      $document
     * @param bool $boutput
     *
     * @return mixed
     */
    abstract protected function loadlayout($page, $document, $boutput = true);

    /**
     * @param $document
     *
     * @return string
     * @throws Exception
     */
    protected function getBuildBeforeTableText($document)
    {
        if ($this->getTemplateInfo($document)) {
            $v = $this->getTemplateInfo($document)
                      ->getData('text_before_table');

            return $this->getProcessor()
                        ->filter($v);
        }

        return '';
    }

    /**
     * @param $document
     * @param $layoutInfo
     *
     * @return string
     * @throws Exception
     */
    protected function renderDataTable($document, $layoutInfo)
    {
        $template = $this->getTemplateInfo($document);
        $cols = $this->getDataTableCols($document);
        $mainclass = 'table-items custom';
        if (!$cols) {
            $mainclass = 'table-items default';
            $cols = $layoutInfo['cols'];
        }
        $html = '<div class="table-items-frame"><table class="' . $mainclass . '" cellpadding="0" cellspacing="0">';
        $html .= '<thead><tr>';
        foreach ($cols as $col) {
            if (isset($col['class'])) {
                $html .= '<th class="' . $col['class'] . '">';
                $html .= $template->translateValue($col['valueHTML'], 'label');
                $html .= '</th>';
            } else {
                $type = isset($col['type']) ? $col['type'] : '';
                $hcss = isset($col['hcss']) ? $col['hcss'] : '';
                $html .= '<th class="' . $type . ' ' . $hcss . '"';
                if (isset($col['width']) && trim($col['width'])) {
                    $html .= ' style="width:' . $col['width'] . '" ';
                }
                $html .= '>';
                $html .= $template->translateValue($col['name'], 'label');
                $html .= '</th>';
            }
        }
        $html .= '</tr></thead>';
        $html .= '<tbody>';


        $idx = 0;
        foreach ($layoutInfo['rows'] as $row) {
            if (!$row['special']) {
                $idx++;
                $html .= '<tr class="' . $row['class'] . '">';
                $row['idx'] = $idx;
                $item = $this->getColectionItem($document, $row['itemid']);
                if ($this->helperBlock) {
                    $this->helperBlock->setPriceDataObject($item);
                }
                foreach ($cols as $col) {
                    if (isset($col['class'])) {
                        $html .= '<td class="' . $col['class'] . '">';
                        $html .= $template->translateValue($this->colDataTableValue($document, $row, $col), 'value');
                        $html .= '</td>';
                    } else {
                        $type = isset($col['type']) ? $col['type'] : '';
                        $css = isset($col['css']) ? $col['css'] : '';
                        $html .= '<td class="' . $type . ' ' . $css . '">';
                        $html .= $template->translateValue(
                            $this->getDataTableValue($item, $document, $row, $col),
                            'value'
                        );
                        $html .= '</td>';
                    }
                }
                $html .= '</tr>';
            } else {
                if (isset($row['cols']) && is_array($row['cols']) && count($row['cols'])) {
                    $html .= '<tr class="' . $row['class'] . '">';
                    $html .= '<td class="special" colspan="' . count($cols) . '">';
                    $row['idx'] = $idx;
                    $item = $this->getColectionItem($document, $row['itemid']);
                    if ($this->helperBlock) {
                        $this->helperBlock->setPriceDataObject($item);
                    }
                    foreach ($row['cols'] as $col) {
                        $html .= $template->translateValue($this->colDataTableValue($document, $row, $col), 'value');
                    }
                    $html .= '</td>';
                    $html .= '</tr>';
                }
            }
        }

        $html .= '</tbody></table></div>';

        // error_log("\n" . print_r($html, true), 3, 'auit.log');
        return $html;
    }

    /**
     * @param $document
     *
     * @return null
     */
    protected function getDataTableCols($document)
    {
        $cols = $this->getTemplateInfo($document)
                     ->getTableColumns();
        if ($this->getTemplateInfo($document)
                 ->getData('table_columns_use_default') && is_array($cols) && count($cols)) {
            return $cols;
        }

        return null;
    }

    /**
     * @param $document
     * @param $id
     *
     * @return null
     */
    protected function getColectionItem($document, $id)
    {

        //** @var $document \Magento\Sales\Model\Order
        // FIX 14.12.2016 Not saved as hash
        foreach ($document->getItemsCollection() as $item) {
            if ($item->getId() == $id) {
                return $item;
            }
        }

        return null;// $document->getItemsCollection()->getItemById($id);
    }

    /**
     * @param $document
     * @param $row
     * @param $col
     *
     * @return mixed|string
     */
    protected function colDataTableValue($document, $row, $col)
    {
        foreach ($row['cols'] as $rcol) {
            if ($rcol['class'] == $col['class']) {
                return $this->stripSpecialTags($rcol['valueHTML']);
            }
        }

        return '';//'Data for '.$col['class'].' not found';
    }

    /**
     * @param $str
     *
     * @return mixed
     */
    protected function stripSpecialTags($str)
    {
        $str = preg_replace('/<script(?:\s[^>]*)?>([^<]+)<\/script>/i', '', $str);
        $str = preg_replace(
            '/<a(?:\s[^>]*)class=\'([^\']*)\'[^>]*>([^<]+)<\/a>/i',
            '<span class="anchor \\1">\\2</span>',
            $str
        );
        $str = preg_replace(
            '/<a(?:\s[^>]*)class="([^"]*)"[^>]*>([^<]+)<\/a>/i',
            '<span class="anchor \\1">\\2</span>',
            $str
        );

        return $str;
    }

    /**
     * @param $item
     * @param $document
     * @param $row
     * @param $col
     *
     * @return string
     * @throws Exception
     * @noinspection PhpRedundantCatchClauseInspection
     */
    protected function getDataTableValue($item, $document, $row, $col)
    {

        /** @var $document \Magento\Sales\Model\Order */
        /** @var $block DefaultRenderer */
        $order = $this->getDocumentOrder($document);
        if (isset($col['type'])) {
            switch ($col['type']) {
                case 'position':
                    return $row['idx'];
                break;
                case 'fr_product':
                    //<strong class="product name product-item-name">Erika Running Short</strong><dl class="item-options"><dt>Color</dt><dd>Black</dd><dt>Size</dt><dd>55 cm</dd></dl>
                    return $this->getFrontendValue($row, 'fr_name');
                break;
                case 'fr_sku':
                    return $this->getFrontendValue($row, 'fr_sku');
                break;
                case 'fr_options':
                    $v = $this->getFrontendValue($row, 'fr_options');

                    return $v;
                break;
                case 'fr_product_sku':
                    $v = $this->getFrontendValue($row, 'fr_name');
                    $v .= '<div class="sku">' . $this->getFrontendValue($row, 'fr_sku') . '</div>';

                    return $v;
                break;
                case 'fr_price':
                    return '' . $this->getFrontendValue($row, 'fr_price');
                break;
                case 'price_excl_tax':
                    if ($item) {
                        return '' . $order->formatPriceTxt($item->getPrice());
                        //return  ''.$document->formatPricePrecision($item->getPrice(), 2, true);
                    }
                    break;
                case 'price_incl_tax':
                    if ($item) {
                        return '' . $order->formatPriceTxt($item->getPriceInclTax());
                    }
                    break;
                case 'price_original':
                    if ($item) {
                        return '' . $order->formatPriceTxt($item->getOriginalPrice());
                    }
                    break;
                case 'fr_qty':
                    $v = $this->getFrontendValue($row, 'fr_qty');

                    //<ul class="items-qty"><li class="item"><span class="title">Bestellt</span><span class="content">22</span></li></ul>
                    return $v;
                break;
                case 'qty':
                    if ($item) {
                        return $item->getQty() * 1;
                    }
                    // Bundle products
                    foreach ($row['cols'] as $rcol) {
                        if (strpos($rcol['class'], 'qty') !== false) {
                            return $this->stripSpecialTags($rcol['valueHTML']);
                        }
                    }
                    break;
                case 'qty_ordered':
                    if ($item) {
                        return $item->getQtyOrdered() * 1;
                    }
                    break;
                case 'qty_shipped':
                    if ($item) {
                        return $item->getQtyShipped() * 1;
                    }
                    break;
                case 'qty_canceled':
                    if ($item) {
                        return $item->getQtyCanceled() * 1;
                    }
                    break;
                case 'qty_refunded':
                    if ($item) {
                        return $item->getQtyRefunded() * 1;
                    }
                    break;
                case 'qty_invoiced':
                    if ($item) {
                        return $item->getQtyInvoiced() * 1;
                    }
                    break;
                case 'subtotal_incl_tax':
                    if ($item && $this->helperBlock) {
                        return $this->helperBlock->displayPrices(
                            $this->_checkoutHelper->getBaseSubtotalInclTax($item),
                            $this->_checkoutHelper->getSubtotalInclTax($item)
                        );
                    }

                    return '';
                break;
                case 'fr_row_total':
                    return '' . $this->getFrontendValue($row, 'fr_subtotal');
                break;
                case 'row_total_excl_tax':
                    if ($item) {
                        return '' . $order->formatPriceTxt($item->getRowTotal());
                    }

                    return '';
                break;
                case 'row_total_incl_tax':
                    if ($item) {
                        return '' . $order->formatPriceTxt($item->getRowTotalInclTax());
                    }

                    return '';
                break;
                case 'tax_amount':
                    if ($item && $this->helperBlock) {
                        return $this->helperBlock->displayPriceAttribute('tax_amount');
                        //return $this->displayPriceAttribute($document,$item,'tax_amount');
                    }
                    break;
                case 'tax_percent':
                    if ($item && $this->helperBlock) {
                        if (is_null($item->getTaxPercent()) && $item->getOrderItem()) {
                            return $this->helperBlock->displayTaxPercent($item->getOrderItem());
                        }

                        return $this->helperBlock->displayTaxPercent($item);
                    }

                    return '';
                break;
                case 'discount_amount':
                    if ($item && $this->helperBlock) {
                        return $this->helperBlock->displayPriceAttribute('discount_amount');
                    }
                    break;

                case 'status':
                    if ($item) {
                        return $item->getStatus();
                    }

                    return '';
                break;
                case 'image':
                    if ($item && $path = $this->_templateHelper->getProductImage($item)) {
                        return '<img class="product-img" src="' . $this->_mediaDirectory->getAbsolutePath($path) . '" />';
                    }

                    return '';
                break;
                case 'custom':
                    //custom_columns
                    //$block->displayPriceAttribute('discount_amount')
                    // $block = $this->_layout->createBlock('Snmportal\Pdfprint\Block\Helper');
                    $colIndex = !empty($col['sort_order']) ? 1 + $col['sort_order'] : 1;
                    $colCSS = !empty($col['css']) ? '' . $col['css'] : '';
                    $psku = '';
                    $product = null;
                    if ($item) {
                        $product = $item->getProduct();
                        $psku = $item->getSku();
                        if (!$product) {
                            $orderItem = $item->getOrderItem();
                            if ($orderItem) {
                                $item = $orderItem;
                                $product = $orderItem->getProduct();
                                $psku = $orderItem->getSku();
                            }
                        }
                    }
                    $parentProduct = $product;
                    if ($product && $psku && $product->getSku() != $psku) {
                        $repo = ObjectManager::getInstance()
                                         ->get('\Magento\Catalog\Model\ProductRepository');
                        try {
                            $product = $repo->get($psku);
                        } catch (NoSuchEntityException $noEntityException) {
                        }
                    }
                    $this->getProcessor()
                     ->setVariables(
                         [
                             'col_index'      => $colIndex,
                             'order_item'     => $item,
                             'product'        => $product,
                             'parent_product' => $parentProduct,
                             'col_css'        => $colCSS,
                             'col_info'       => $col
                         ]
                     );

                    return $this->getProcessor()
                            ->filter('{{block snm="custom_columns" }}');
                break;
                default:
                    break;
            }
        }
        if (isset($col['type'])) {
            return 'Data for ' . $col['type'] . ' not found';
        }

        return '';
    }

    /**
     * @param $row
     * @param $token
     *
     * @return mixed|string
     */
    protected function getFrontendValue($row, $token)
    {
        foreach ($row['cols'] as $rcol) {
            if ($rcol['type'] == $token) {
                return $this->stripSpecialTags($rcol['valueHTML']);
            }
        }

        return '';
    }

    /**
     * @param $document
     * @param $layoutInfo
     *
     * @return string
     */
    protected function renderTotalTable($document, $layoutInfo)
    {
        $template = $this->getTemplateInfo($document);
        if (!$template || $template->isTemplate(Template::TYPE_SHIPPING)) {
            return '';
        }
        $html = '';
        $html .= '<div class="table-totals-frame"><table  class="table-totals"  cellpadding="0" cellspacing="0">';
        $html .= '<tfoot>';

        //$this->_scopeConfig->getValue()

        $table_totals = $template->getData('table_totals');

        // Use Custom Totals
        if ($template->getData('table_totals_use_custom') && is_array($table_totals) && count($table_totals)) {
            $order = $this->getDocumentOrder($document);
            $totals = $this->_getTotalsList();

            foreach ($table_totals as $totalDef) {
                foreach ($totals as $total) {

                    $total->setSource($document);
                    $total->setOrder($order);
                    if (!$document->getOrder()) {
                        $document->setOrder($order);
                    }


                    $totalField = $total->getSourceField();
                    if ($totalDef['type'] == $totalField) {
                        if ($totalDef['visible'] == 'always' || $total->canDisplay()) {
                            if ($totalField == 'tax_amount' && !$template->getData('table_taxrenderer_default')) {
                                // Fix for PDF renderer multiple invoices
                                $html .= $this->_templateHelper->renderTaxTotals($document, $order, $template);
                            } else {
                                $totalsForDisplay = $total->getTotalsForDisplay();
                                foreach ($totalsForDisplay as $idx => $totalData) {
                                    $name = rtrim($totalData['label'], ':');
                                    $name = $template->translateValue($name, 'label');
                                    $name = str_replace('()', '', $name);
                                    $html .= '<tr class="' . $totalField . '">';
                                    $html .= '<td class="first"></td>';
                                    $html .= '<td class="label">' . $name . '</td>';
                                    $html .= '<td class="amount">' . $template->translateValue(
                                        $totalData['amount'],
                                        'value'
                                    ) . '</td>';
                                    $html .= '</tr>';
                                }
                            }
                        }
                        break;
                    }
                }
            }
        } else {
            foreach ($layoutInfo['totals'] as $total) {
                $html .= '<tr class="' . $total['class'] . '">';
                $html .= '<td class="first"></td>';
                foreach ($total['cols'] as $idx => $col) {
                    $html .= '<td class="' . $col['class'] . '">';

                    $html .= $template->translateValue($col['valueHTML'], $idx ? 'value' : 'label');
                    $html .= '</td>';
                }
                $html .= '</tr>';
            }
        }
        $html .= '</tfoot></table></div>';

        return $html;
    }

    /**
     * @param $document
     *
     * @return string
     * @throws Exception
     */
    protected function getBuildAfterTableText($document)
    {
        if ($this->getTemplateInfo($document)) {
            $v = $this->getTemplateInfo($document)
                      ->getData('text_after_table');

            return $this->getProcessor()
                        ->filter($v);
        }

        return '';
    }

    /**
     * @param $value
     *
     * @return string
     * @throws Exception
     */
    public function filterValue($value)
    {
        return $this->getProcessor()
                    ->filter($value);
    }

    protected function _drawItem(DataObject $item, Zend_Pdf_Page $page, \Magento\Sales\Model\Order $order)
    {
        return $page;
    }

    /**
     * @param $html
     *
     * @return array
     */
    protected function parseLayout($html)
    {
//        error_log("\n" . print_r($html, true), 3, 'auit.log');

        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $doc = new DOMDocument();
        $tables = null;
        try {
            $doc->loadHTML('<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<html><body>' . $html . '</body></html>');
            $xpath = new DOMXpath($doc);
            $tables = $xpath->query('//table[contains(@class,"table-order-items")]');
        } catch (Exception $e) {

        }

        $result = ['cols' => [], 'rows' => [], 'totals' => []];
        if ($tables && $tables->length) {
            $ths = $xpath->query('//thead//th', $tables[0]);
            foreach ($ths as $th) {
                $cl = $th->getAttribute('class');
                if ($this->document instanceof \Magento\Sales\Model\Order\Shipment && $cl == 'col price') {
                    // BUG Header has price not qty class
                    $cl = 'col qty';
                }
                $result['cols'][] = [
                    'class'     => $cl,
                    'type'      => $this->getColType($cl),
                    'value'     => $this->trimNodeValue($th),
                    'valueHTML' => $this->trimHTML($this->DOMinnerHTML($th))
                ];
            }
            $trs = $xpath->query('//tbody//tr', $tables[0]);
            $lastItemId = 0;
            foreach ($trs as $tr) {
                $tds = $xpath->query('td', $tr);
                $row = [];
                $row['class'] = $tr->getAttribute('class');


                if ($tr->getAttribute('id')) {
                    $row['id'] = $tr->getAttribute('id');
                    $x = explode('-', $tr->getAttribute('id'));
                    $row['itemid'] = $x[count($x) - 1];
                    $lastItemId = $row['itemid'];
                    $row['special'] = false;
                } else {
                    $row['special'] = true;
                    $row['itemid'] = $lastItemId;
                }

                foreach ($tds as $index => $td) {
                    $row['cols'][] = [
                        'class'     => $td->getAttribute('class'),
                        'type'      => $this->getColTypeIndex($index),
                        'value'     => $this->trimNodeValue($td),
                        'valueHTML' => $this->trimHTML($this->DOMinnerHTML($td))
                    ];
                }
                /*

                                if ( $row['id'] )
                                {
                                    $row['special']=false;
                                    $x = explode('-',$tr->getAttribute('id'));
                                    $row['itemid']=$x[count($x)-1];
                                    $lastItemId = $row['itemid'];
                                    foreach ( $tds as $td) {
                                        $row['cols'][] = array(
                                            'class' => $td->getAttribute('class'),
                                            'type' => $this->getColType($td->getAttribute('class')),
                                            'value' => $this->trimNodeValue($td),
                                            'valueHTML' => $this->trimHTML($this->DOMinnerHTML($td))
                                        );
                                    }
                                }else { //Special Rows Gift,...
                                    $row['itemid']=$lastItemId;
                                    $row['special']=true;
                                    foreach ( $tds as $td) {
                                        $row['cols'][] = array(
                                            'class' => $td->getAttribute('class'),
                                            'type' => $this->getColType($td->getAttribute('class')),
                                            'value' => $this->trimNodeValue($td),
                                            'valueHTML' => $this->trimHTML($this->DOMinnerHTML($td))
                                        );
                                    }
                                }
                */
                $result['rows'][] = $row;
            }
            $trs = $xpath->query('//tfoot//tr', $tables[0]);
            foreach ($trs as $tr) {
                $tds = $xpath->query('*', $tr);
                $row = [];
                $row['class'] = $tr->getAttribute('class');
                foreach ($tds as $td) {
                    $row['cols'][] = [
                        'class'     => $td->getAttribute('class'),
                        'value'     => $this->trimNodeValue($td),
                        'valueHTML' => $this->trimHTML($this->DOMinnerHTML($td))
                    ];
                }
                $result['totals'][] = $row;
            }
        }

        return $result;
    }

    /**
     * @param $class
     *
     * @return string
     */
    protected function getColType($class)
    {
        $types = ['name', 'sku', 'price', 'qty', 'subtotal', 'options'];

        foreach ($types as $type) {
            if (strpos($class, $type) !== false) {

                return 'fr_' . $type;
            }
        }

        return 'fr_';
    }

    /**
     * @param $node
     *
     * @return string
     */
    protected function trimNodeValue($node)
    {
        $v = str_replace("\t", "", $node->nodeValue);

        return trim($v);
    }

    /**
     * @param $html
     *
     * @return string
     */
    protected function trimHTML($html)
    {
        return trim($html);
    }

    /**
     * @param      $element
     * @param bool $checkArabic
     *
     * @return string
     */
    protected function DOMinnerHTML($element, $checkArabic = false)
    {
        $innerHTML = '';
        $children = $element->childNodes;
        foreach ($children as $child) {

            if ($child->nodeType == XML_TEXT_NODE) {
                if ($checkArabic) {

                    if ($tmp = trim($child->textContent)) {
                        $Arabic = new Glyphs();//'Glyphs');
                        $innerHTML .= $Arabic->utf8Glyphs($tmp);
                    }
                } else {
                    $innerHTML .= trim($child->textContent);
                }
            } else {
                if (!$child->hasChildNodes()) {
                    $innerHTML .= '<' . $child->nodeName . '/>';
                } else {
                    $innerHTML .= '<' . $child->nodeName;
                    foreach ($child->attributes as $name => $attrNode) {
                        $innerHTML .= " $name=\"" . $attrNode->nodeValue . '"';
                    }
                    $innerHTML .= '>';
                    /*
                    if ( $child->getAttribute('data-label') )
                    {
                        $innerHTML .= '<span class="data-label">'.$child->getAttribute('data-label').'</span>';
                    }
                    */

                    $innerHTML .= $this->DOMinnerHTML($child, $checkArabic);

                    $innerHTML .= '</' . $child->nodeName . '>';
                }
            }
            //$innerHTML .= $element->ownerDocument->saveHTML($child);
        }

        return $innerHTML;
    }

    /**
     * @param $index
     *
     * @return string
     */
    protected function getColTypeIndex($index)
    {
        $types = ['name', 'sku', 'price', 'qty', 'subtotal', 'options'];

        foreach ($types as $idx => $type) {
            if ($idx == $index) {

                return 'fr_' . $type;
            }
        }

        return 'fr_';
    }
}
