<?php
// @codingStandardsIgnoreFile

namespace Snmportal\Pdfprint\Helper;


use Exception;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Mail\Message;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Item;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Helper\Data as TaxHelper;
use Snmportal\Pdfprint\Controller\Adminhtml\FileFactory;
use Snmportal\Pdfprint\Model\ResourceModel\Template\CollectionFactory;
use Snmportal\Pdfprint\Model\Template as SNMTemplate;
class Template extends AbstractHelper
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var TaxHelper
     */
    protected $taxHelper;

    /**
     * @var Email
     */
    protected $emailHelper;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * Template constructor.
     *
     * @param Context                $context
     * @param StoreManagerInterface  $storeManager
     * @param FileFactory            $fileFactory
     * @param ObjectManagerInterface $objectManager
     * @param TaxHelper              $taxHelper
     * @param Email                  $emailHelper
     * @param CollectionFactory      $collectionFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        FileFactory $fileFactory,
        ObjectManagerInterface $objectManager,
        TaxHelper $taxHelper,
        Email $emailHelper,
        CollectionFactory $collectionFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->fileFactory = $fileFactory;
        $this->collectionFactory = $collectionFactory;
        $this->_objectManager = $objectManager;
        $this->taxHelper = $taxHelper;
        $this->emailHelper = $emailHelper;
        parent::__construct(
            $context
        );
    }

    /**
     * @param $className
     *
     * @return mixed
     */
    public function createObj($className)
    {
        return $this->_objectManager->create($className);
    }

    /**
     * @return bool
     */
    public function getIsDefaultPrint()
    {
        return $this->_request->getActionName() == 'print';
    }

    public function getMassTemplates($type)
    {
        $result = [];
        if ($type == 'order') {
            $type = SNMTemplate::TYPE_ORDER;
            $orderTemplates = $this->collectionFactory->create();
            $orderTemplates->addFieldToFilter('type', $type);
            $orderTemplates->addFieldToFilter('is_active', 1);
            $orderTemplates->addFieldToFilter('is_massaction', 1);
            $orderTemplates->walk('afterLoad');
            foreach ($orderTemplates as $template) {
                $result[] = ['value' => $template->getId(), 'label' => $template->getTitle()];
            }
        }

        return $result;
    }

    /**
     * @param     $document
     * @param int $defaultId
     *
     * @return null
     */
    public function getBestTemplateForDocument($document, $defaultId = 0)
    {
        $type = null;
        $storeId = 0;
        if ($document instanceof Invoice) {
            $type = SNMTemplate::TYPE_INVOICE;
            $storeId = $document->getOrder()
                                ->getStoreId();
        } else {
            if ($document instanceof Shipment) {
                $type = SNMTemplate::TYPE_SHIPPING;
                $storeId = $document->getOrder()
                                    ->getStoreId();
            } else {
                if ($document instanceof Creditmemo) {
                    $type = SNMTemplate::TYPE_CREDITMEMO;
                    $storeId = $document->getOrder()
                                        ->getStoreId();
                } else {
                    if ($document instanceof Order) {
                        $type = SNMTemplate::TYPE_ORDER;
                        $storeId = $document->getStoreId();
                    }
                }
            }
        }
        if (!is_null($type)) {
            $orderTemplates = $this->collectionFactory->create();
            $orderTemplates->addFieldToFilter('type', $type);
            $orderTemplates->addFieldToFilter('is_active', 1);
            if ($storeId) {
                $orderTemplates->addStoreFilter($storeId);
            }
            $orderTemplates->walk('afterLoad');
            if ($defaultId) {
                foreach ($orderTemplates as $template) {
                    if ($template->getId() == $defaultId) {
                        return $template;
                    }
                }
            }
            $bestTemplate = null;
            foreach ($orderTemplates as $template) {
                if (!$template->getIsDefault()) {
                    continue;
                }
                if (!$bestTemplate) {
                    $bestTemplate = $template;
                }
                if ($template->getStoreId() && is_array($template->getStoreId())) {
                    foreach ($template->getStoreId() as $sId) {
                        if ($sId == $storeId) {
                            return $template;
                        }
                    }
                }
            }
            if ($bestTemplate) {
                return $bestTemplate;
            }
            $bestTemplate = null;
            foreach ($orderTemplates as $template) {
                if (!$bestTemplate) {
                    $bestTemplate = $template;
                }
                if ($template->getStoreId() && is_array($template->getStoreId())) {
                    foreach ($template->getStoreId() as $sId) {
                        if ($sId == $storeId) {
                            return $template;
                        }
                    }
                }
            }

            return $bestTemplate;
        }

        return null;
    }

    /**
     * @param $item
     *
     * @return string
     */
    public function getProductImage($item)
    {
        /**
         * @var Item $item
         */
        $product = $item->getProduct();
        if (!$product) {
            $orderItem = $item->getOrderItem();
            if ($orderItem) {
                $product = $orderItem->getProduct();
            }
        }
        if ($product) {
            if ($product->getSku() !== $item->getSku()) {
                try {
                    $prepro = $this->_objectManager->get(ProductRepository::class);
                    $product = $prepro->get($item->getSku());
                } catch (Exception $e) {

                }
            }
            if ($product && $product->getImage() && $product->getImage() != 'no_selection') {// 24.02.2020
                return 'catalog/product/' . $product->getImage();
            }
            $picpath = '';
            $parent_products = $this->_objectManager->create(
                'Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable'
            )
                                                    ->getParentIdsByChild($product->getId());
            foreach ($parent_products as $pid) {
                try {
                    $prepro = $this->_objectManager->get(ProductRepository::class);
                    $cproduct = $prepro->getById($pid);
                    if ($cproduct && $cproduct->getImage() && $cproduct->getImage() != 'no_selection') {
                        $picpath = $cproduct->getImage();
                        break;
                    }
                } catch (Exception $e) {

                }
            }
            if ($picpath) {
                return 'catalog/product/' . $picpath;
            }
        }

        return '';
    }

    /**
     * @param $document
     *
     * @return mixed
     */
    public function getTemplatesForDocument($document)
    {
        $type = null;
        $storeId = 0;
        if ($document instanceof Invoice) {
            $type = SNMTemplate::TYPE_INVOICE;
            $storeId = $document->getOrder()
                                ->getStoreId();
        } else {
            if ($document instanceof Shipment) {
                $type = SNMTemplate::TYPE_SHIPPING;
                $storeId = $document->getOrder()
                                    ->getStoreId();
            } else {
                if ($document instanceof Creditmemo) {
                    $type = SNMTemplate::TYPE_CREDITMEMO;
                    $storeId = $document->getOrder()
                                        ->getStoreId();
                } else {
                    if ($document instanceof Order) {
                        $type = SNMTemplate::TYPE_ORDER;
                        $storeId = $document->getStoreId();
                    }
                }
            }
        }
        $orderTemplates = $this->collectionFactory->create();
        $orderTemplates->addFieldToFilter('type', $type);
        $orderTemplates->addFieldToFilter('is_active', 1);
        if ($storeId) {
            $orderTemplates->addStoreFilter($storeId);
        }
        $orderTemplates->walk('afterLoad');

        //    $order->setAuItPrintTemplates($orderTemplates);
        return $orderTemplates;
    }

    /**
     * @param $document
     *
     * @return int|null
     */
    public function mapDocToTyp($document)
    {
        $typ = null;
        if ($document instanceof Order) {
            $typ = SNMTemplate::TYPE_ORDER;
        } else {
            if ($document instanceof Invoice) {
                $typ = SNMTemplate::TYPE_INVOICE;
            } else {
                if ($document instanceof Shipment) {
                    $typ = SNMTemplate::TYPE_SHIPPING;
                } else {
                    if ($document instanceof Creditmemo) {
                        $typ = SNMTemplate::TYPE_CREDITMEMO;
                    }
                }
            }
        }

        return $typ;
    }

    /**
     * @param $typ
     *
     * @return array
     */
    public function getTypinfo($typ)
    {
        switch ($typ) {
            case  SNMTemplate::TYPE_ORDER:
                return array('order', '\Snmportal\Pdfprint\Model\Pdf\Order');
                break;
            case  SNMTemplate::TYPE_INVOICE:
                return array('invoice', '\Snmportal\Pdfprint\Model\Pdf\Invoice');
                break;
            case  SNMTemplate::TYPE_SHIPPING:
                return array('shipment', '\Snmportal\Pdfprint\Model\Pdf\Shipment');
                break;
            case  SNMTemplate::TYPE_CREDITMEMO:
                return array('creditmemo', '\Snmportal\Pdfprint\Model\Pdf\Creditmemo');
                break;
        }

        return array('unknown', '');
    }

    /**
     * @param      $key
     * @param null $store
     *
     * @return bool
     */
    public function isSetFlag($key, $store = null)
    {
        return $this->scopeConfig->isSetFlag($key, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param $typ
     * @param $store
     *
     * @return bool
     */
    protected function isTypeEnabled($typ, $store)
    {
        $typ = $this->getTypinfo($typ);
        if ($this->scopeConfig->isSetFlag('snmportal_pdfprint/general/enabled', ScopeInterface::SCOPE_STORE, $store) &&

            $this->scopeConfig->isSetFlag(
                'snmportal_pdfprint/' . $typ[0] . '/enabled',
                ScopeInterface::SCOPE_STORE,
                $store
            )) {
            return true;
        }


        return false;
    }

    /**
     * @param $typ
     * @param $store
     *
     * @return bool
     */
    protected function isAttachPdfToEmail($typ, $store)
    {
        $typ = $this->getTypinfo($typ);

        return $this->scopeConfig->isSetFlag(
            'snmportal_pdfprint/' . $typ[0] . '/attach_pdf_email',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param $typ
     * @param $store
     *
     * @return \Snmportal\Pdfprint\Model\Pdf\Base |null
     */
    public function getEngine($typ, $store)
    {
        if ($this->isTypeEnabled($typ, $store)) {
            $typ = $this->getTypinfo($typ);
            if ($typ[1]) {
                $engine = $this->_objectManager->create($typ[1]);
                if ($engine) {
                    return $engine;
                }
            }
        }

        return null;
    }

    /**
     * @param AbstractModel $document
     *
     * @return mixed|null
     */
    public function getEngineForDocument(AbstractModel $document)
    {
        $typ = $this->mapDocToTyp($document);
        $typ = $this->getTypinfo($typ);
        if ($typ[1]) {
            $engine = $this->_objectManager->create($typ[1]);
            if ($engine) {
                return $engine;
            }
        }

        return null;
    }

    /**
     * @param Sender $caller
     *
     * @return Message|null
     */
    /*
    protected function getMailMessage(\Magento\Sales\Model\Order\Email\Sender $caller)
    {
        $reflectionObject = new \ReflectionObject($caller);
        $reflectionMethod = $reflectionObject->getMethod('getSender');
        $reflectionMethod->setAccessible(true);
        $sender = $reflectionMethod->invoke($caller);

        if ($sender instanceof \Magento\Sales\Model\Order\Email\SenderBuilder) {
            // @var  $sender \Magento\Sales\Model\Order\Email\SenderBuilder
            $reflectionProperty = new \ReflectionProperty($sender, 'transportBuilder');
            $reflectionProperty->setAccessible(true);
            $transportBuilder = $reflectionProperty->getValue($sender);
            // @var  $transportBuilder \Magento\Framework\Mail\Template\TransportBuilder
            if ($transportBuilder instanceof \Magento\Framework\Mail\Template\TransportBuilder) {
                //** @var $message \Magento\Framework\Mail\Message
                $reflectionProperty = new \ReflectionProperty($transportBuilder, 'message');
                $reflectionProperty->setAccessible(true);
                $message = $reflectionProperty->getValue($transportBuilder);
                if ($message instanceof \Magento\Framework\Mail\Message) {
                    return $message;
                }
            }
        }
        return null;
    }
*/

    /**
     * @param Sender $caller
     * @param        $content
     * @param        $fileName
     * @param string $mimeType
     * @param string $disposition
     * @param string $encoding
     */
    protected function attachToEmail(
        Sender $caller,
        $content,
        $fileName,
        $mimeType = 'application/pdf',
        $disposition = 'attachment',
        $encoding = 'base64'
    ) {
        $part = new DataObject(
            [
                'content'     => $content,
                'encoding'    => $encoding,
                'type'        => $mimeType,
                'disposition' => $disposition,
                'filename'    => $fileName
            ]
        );
        $this->emailHelper->addAttachment($part);
    }

    public function attachEmails(Sender $caller, $document, $typ, $store)
    {
        if ($this->isTypeEnabled($typ, $store)) {
            if ($this->isAttachPdfToEmail($typ, $store)) {
                $engine = $this->getEngine($typ, $store);
                if ($engine) {
                    $pdf = $engine->getPdf([$document]);
                    if ($pdf) {
                        // $pdf->render();
                        $this->attachToEmail(
                            $caller,
                            $pdf->render(),
                            $engine->getEmailFilename()
                        );
                    }
                    foreach ($engine->getEmailAttachments($document) as $attachment) {
                        $fname = $attachment['name'] ? $attachment['name'] : basename($attachment['path']);
                        if (file_exists($attachment['path']) && is_readable($attachment['path'])) {
                            $this->attachToEmail(
                                $caller,
                                file_get_contents($attachment['path']),
                                $fname
                            );
                        }
                    }
                }
            }
        }
    }

    public function downloadPDF(\Snmportal\Pdfprint\Model\Pdf\Base $engine, $documents)
    {
        if (!is_array($documents)) {
            $documents = [$documents];
        }
        $content = $engine->getPdf($documents)
                          ->render();
        $fileName = $engine->getEmailFilename();

        return $this->fileFactory->create(
            $fileName,
            $content,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }

    /**
     * @param $document
     *
     * @return null
     */
    protected function getDocumentOrder($document)
    {
        if ($document instanceof Order) {
            return $document;
        }
        if ($document) {
            return $document->getOrder();
        }

        return null;
    }

    /**
     * @param $document
     *
     * @return array
     */
    public function getTaxTotals($document)
    {
        return $this->taxHelper->getCalculatedTaxes($document);
    }

    /**
     * @param $document
     * @param $order
     * @param $template
     *
     * @return string
     */
    public function renderTaxTotals($document, $order, $template)
    {
        $totals = $this->getTaxTotals($document);
        $totalclass = 'tax';

        $html = '';
        if ($template->getData('table_tax_full_summary')) {
            foreach ($totals as $total) {
                $percent = $total['percent'];
                $percent = sprintf('%s', $percent + 0);
                $percent = $percent ? ' (' . $percent . '%)' : '';
                $amount = $total['tax_amount'];
                $name = __('Tax') . $percent . ':';
                $html .= '<tr class="' . $totalclass . ' part">';
                $html .= '<td class="first"></td>';
                $html .= '<td class="label">' . $template->translateValue($name, 'label') . '</td>';
                $html .= '<td class="amount">' . $template->translateValue(
                        $order->formatPrice($amount),
                        'value'
                    ) . '</td>';
                $html .= '</tr>';
            }
        }
        if ($template->getData('table_tax_all')) {
            if (!$template->getData('table_tax_full_summary') || $template->getData(
                    'table_tax_all'
                ) == 1 || ($template->getData('table_tax_all') == 2 && count($totals) > 1)) {
                $amount = $document->getTaxAmount();
                $name = __('Tax') . ':';
                $html .= '<tr class="' . $totalclass . ' total">';
                $html .= '<td class="first"></td>';
                $html .= '<td class="label">' . $template->translateValue($name, 'label') . '</td>';
                $html .= '<td class="amount">' . $template->translateValue(
                        $order->formatPrice($amount),
                        'value'
                    ) . '</td>';
                $html .= '</tr>';
            }
        }

        return $html;
    }
}
