<?php

namespace Snmportal\Pdfprint\Model\Template;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Snmportal\Pdfprint\Model\Template;
use Snmportal\Pdfprint\Model\TemplateFactory;

class Setup extends DataObject
{
    /**
     * @var TemplateFactory
     */
    protected $_templateFactory;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    ////select * from `core_config_data` where `path` like "%auit_pdf%"
    //mysqldump demo2 core_config_data --no-create-info --where='path like "auit_pdf%"' > auit_pdf_config_data.sql
    //mysql magento2_demo2 < auit_pdf_config_data.sql
    // clear cache
    // import m1
    /**
     * Setup constructor.
     *
     * @param ScopeConfigInterface  $config
     * @param StoreManagerInterface $storeManager
     * @param TemplateFactory       $templateFactory
     * @param Filesystem            $filesystem
     * @param array                 $data
     */
    public function __construct(
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager,
        TemplateFactory $templateFactory,
        Filesystem $filesystem,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->_scopeConfig = $config;
        $this->_templateFactory = $templateFactory;
//        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function importM1Data()
    {
        $defaultStore = $this->storeManager->getStore(Store::DEFAULT_STORE_ID);

        $sections = [
            ['auit_offer', 'email_attachments_order', Template::TYPE_ORDER],
            ['invoice', 'email_attachments_invoice', Template::TYPE_INVOICE],
            ['shipment', 'email_attachments_shipment', Template::TYPE_SHIPPING],
            ['creditmemo', 'email_attachments_creditmemo', Template::TYPE_CREDITMEMO],
        ];
        $bhasImported = false;
        foreach ($sections as $section) {
            $defaultData = $this->getConfigData($section[0], $section[1], $defaultStore);

            if ($defaultData['margins1']) {
                $bhasImported = true;
                $this->importAs(null, $defaultData, $section[2]);
                $stores = $this->storeManager->getStores(false, true);
                foreach ($stores as $store) {
                    $storeData = $this->getConfigData($section[0], $section[1], $store);
                    if (!$this->isEqual($defaultData, $storeData)) {
                        $this->importAs($store, $defaultData, $section[2]);
                    }
                }
            }
        }
        if (!$bhasImported) {
            return false;
        }

        return true;
    }

    /**
     * @param $config
     * @param $configEmail
     * @param $store
     *
     * @return array
     */
    protected function getConfigData($config, $configEmail, $store)
    {
        $mainKey = 'auit_pdf/' . $config;
        $items = [];
        $items[] = [
            'key'      => 'billingaddress',
            'position' => $this->_loadStyleFrame($mainKey . '/billingaddress', $store),
            'data'     => $this->updateAdressItems(
                $this->getStoreValue($mainKey . '/text_billingaddress', $store),
                'billingaddress.'
            )
        ];

        $items[] = [
            'key'      => 'shippingaddress',
            'position' => $this->_loadStyleFrame($mainKey . '/shippingaddress', $store),
            'data'     => $this->updateAdressItems(
                $this->getStoreValue($mainKey . '/text_shippingaddress', $store),
                'shippingaddress.'
            )
        ];
        $items[] = [
            'key'      => 'free_page_1',
            'position' => $this->_loadItems($mainKey . '/free_page_1', $store)
        ];

        $items[] = [
            'key'      => 'free_page_n',
            'position' => $this->_loadItems($mainKey . '/free_page_n', $store)
        ];

        /*
                show_shippingaddress
                table_template
                table_show_pos
                table_add_sku
                table_show_sku
                table_show_qty_number
          */

        return [
            'items'                      => $items,
            'text_before_table'          => $this->getStoreValue($mainKey . '/text_before_table', $store),
            'text_after_table'           => $this->getStoreValue($mainKey . '/text_after_table', $store),
            'margins1'                   => $this->_unserial($mainKey . '/table_margins', $store),
            'margins2'                   => $this->_unserial($mainKey . '/table_margins2', $store),
            'file_template'              => $this->getStoreValue($mainKey . '/template', $store),
            'file_append'                => $this->_unserial($mainKey . '/append', $store),
            'attachament_pdf_1_enabled'  => $this->getStoreValue($mainKey . '/pdf_1_enabled', $store),
            'attachament_pdf_1'          => $this->getStoreValue($mainKey . '/pdf_1', $store),
            'attachament_pdf_1_filename' => $this->getStoreValue($mainKey . '/pdf_1_filename', $store),
            'attachament_pdf_2_enabled'  => $this->getStoreValue($mainKey . '/pdf_2_enabled', $store),
            'attachament_pdf_2'          => $this->getStoreValue($mainKey . '/pdf_2', $store),
            'attachament_pdf_2_filename' => $this->getStoreValue($mainKey . '/pdf_2_filename', $store),
            'css'                        => $this->getStoreValue('auit_pdf/style/global', $store),
        ];
    }

    /**
     * @param $key
     * @param $store
     *
     * @return mixed|string
     */
    protected function _loadStyleFrame($key, $store)
    {
        return $this->_unserial($key, $store);
    }

    /**
     * @param $key
     * @param $store
     *
     * @return mixed|string
     */
    protected function _unserial($key, $store)
    {
        $data = $this->getStoreValue($key, $store);
        if (strpos($data, 'base64:') === 0) {
            $data = base64_decode(substr($data, 7));
        }
        //$data = @unserialize($data);
        $data = null;
        try {
            $data = ObjectManager::getInstance()
                                 ->get('\Magento\Framework\Serialize\Serializer\Serialize')
                                 ->unserialize($data);
        } catch (Exception $e) {

        }

        return $data;
    }

    /**
     * @param $key
     * @param $store
     *
     * @return string
     */
    protected function getStoreValue($key, $store)
    {
        if (!$store || !$store->getId()) {
            return trim($this->_scopeConfig->getValue($key));
        }

        return trim($this->_scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE, $store));
    }

    /**
     * @param $data
     * @param $key
     *
     * @return mixed
     */
    protected function updateAdressItems($data, $key)
    {
        $tokens = [
            'prefix',
            'firstname',
            'middlename',
            'lastname',
            'suffix',
            'company',
            'street',
            'street1',
            'street2',
            'street3',
            'postcode',
            'city',
            'country',
            'telephone',
            'fax',
            'vat_id'
        ];
        foreach ($tokens as $token) {
            $data = preg_replace('/\b' . $token . '\b/', $key . $token, $data);
        }

        return $data;
    }

    /**
     * @param $key
     * @param $store
     *
     * @return mixed|string
     */
    protected function _loadItems($key, $store)
    {
        return $this->_unserial($key, $store);
    }

    /**
     * @param $store
     * @param $dataM1
     * @param $type
     *
     * @throws LocalizedException
     */
    public function importAs($store, $dataM1, $type)
    {
        /**
         * @var Template $template
         */
        $template = $this->_templateFactory->create();
        $identifier = '_migration_' . $type;
        $templId = $template->checkIdentifier($identifier, $store ? $store->getId() : Store::DEFAULT_STORE_ID);
        if ($templId) {
            $template->load($templId);
        }
        $data = [];
        $data['title'] = 'Migration ' . ($store ? __('Store ') . $store->getCode() : __('Default'));
        $data['text_before_table'] = $dataM1['text_before_table'];
        $data['text_after_table'] = $dataM1['text_after_table'];
        $m = array_shift($dataM1['margins1']);
        $data['margins1'] = [
            [
                'left'   => $m['left'],
                'top'    => $m['top1'],
                'right'  => $m['right'],
                'bottom' => $m['bottom']
            ]
        ];

        $m = array_shift($dataM1['margins2']);
        $data['margins2'] = [
            [
                'left'   => $m['left'],
                'top'    => $m['top1'],
                'right'  => $m['right'],
                'bottom' => $m['bottom']
            ]
        ];
        $data['css'] = $dataM1['css'];

        $data['css'] .= "
.col.position	{width:10mm;text-align: center;}
.col.name		{width:20mm;}
.col.image		{width:30mm;}
.shipment .col.name		{width:125mm;}
.shipment-barcode .col.name		{width:105mm;}
.col.sku		{width:40mm;}
.col.price		{width:20mm;text-align: right;auto-stretching:1;}
.col.qty		{width:15mm;text-align: center;}";

        /*
        $data['css'].="\n.data-table .table-items {border:1px solid red;}";
        $data['css'].="\n.data-table .table-items {border:1px solid red;}";
        $data['css'].="\n.data-table .table-items td {border:1px solid yellow;}";
        $data['css'].="\n.data-table .table-items thead th {text-align:left;}";
        $data['css'].="\n.data-table .table-totals {border:1px solid red;}";
        $data['css'].="\n.data-table .table-totals tfoot td {border:1px solid yellow;}";
*/
        $data['css'] .= "\ntable.table-items {width:100%;}";
        $data['css'] .= "\n.data-table .table-totals tfoot td.mark {width:145mm;text-align:right;}";
        $data['css'] .= "\n.data-table .table-totals tfoot td.amount  {width:30mm;text-align:right;}";

        $data['css'] .= "\n.table-items tbody tr:nth-child(odd) {background: #EEE}";
        //tr:nth-child(odd) {background: #FFF}
        $data['css'] .= "\n.table-items td.left {text-align:left;}";
        $data['css'] .= "\n.table-items td.center {text-align:center;}";
        $data['css'] .= "\n.table-items td.right {text-align:right;}";
        $data['css'] .= "\n.table-items * {font-size:3pt}";
        $data['css'] .= "\n.table-items th {text-align:left;}";
        $data['css'] .= "\n.table-items td {vertical-align: top;}";
        $data['css'] .= "\n.table-items img.product-img {max-width:100%;}";

        $data['css'] .= "\nul.items-qty {padding:0;margin:0;}";
        $data['css'] .= "\nul.items-qty li.item {list-style-type: none;padding:0;margin:0;white-space:nowrap;}";
        $data['css'] .= "\nul.items-qty li.item span.content {padding-left:0.2em;}";

        $data['css'] .= "\ndl.item-options dd {margin-left:1em;}";

        $data['type'] = $type;

        $data['free_items'] = [];
        $data['free_items_p2'] = [];

        foreach ($dataM1['items'] as $group) {

            foreach ($group['position'] as $key => $item) {
                $r = [
                    'x_pos'  => $item['x'],
                    'y_pos'  => $item['y'],
                    'width'  => $item['w'],
                    'height' => $item['h'],
                    'style'  => $item['class'],
                    'script' => 'markup',
                    'value'  => ''
                ];
                if ($group['key'] == 'shippingaddress' || $group['key'] == 'billingaddress') {
                    $r['value'] = $group['data'];
                    $data['free_items'][] = $r;
                } else {
                    if ($group['key'] == 'free_page_1') {
                        $r['value'] = $item['value'];
                        $data['free_items'][] = $r;
                    } else {
                        if ($group['key'] == 'free_page_n') {
                            $r['value'] = $item['value'];
                            $data['free_items_p2'][] = $r;
                        }
                    }
                }
            }
        }
        // Files
        $mediaDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
        foreach ([
                     'file_template'     => 'pdf_background',
                     'file_append'       => 'pdf_appendix',
                     'attachament_pdf_1' => 'pdf_attachment1',
                     'attachament_pdf_2' => 'pdf_attachment2'
                 ] as $k => $v) {
            if (isset($dataM1[$k])) {
                if ($mediaDirectory->isFile('snm-portal/sales/pdf/' . $dataM1[$k])) {
                    $filePath = $mediaDirectory->getAbsolutePath('snm-portal/sales/pdf/' . $dataM1[$k]);
                    $template->importFile($v, $filePath);
                }
            }
        }

        if (!$templId) {
            $data['is_active'] = 1;
            $data['identifier'] = $identifier;
            $data['stores'] = $store ? $store->getId() : 0;
            $template->setData($data);
            $template->save();
        } else {
            $template->setData($data);
            $template->save();
        }
    }

    /**
     * @param $a1
     * @param $a2
     *
     * @return bool
     */
    protected function isEqual($a1, $a2)
    {
        foreach ($a1 as $k => $v) {
            if (is_array($v)) {
                if (!isset($a2[$k]) || !is_array($a2[$k])) {

                    return false;
                }

                if (!$this->isEqual($v, $a2[$k])) {
                    return false;
                }
            } else {
                if ($v != $a2[$k]) {

                    return false;
                }
            }
        }

        return true;
    }
}
/*
 *
 @font-face { font-family: 'UNIFONT';font-style: normal;font-weight: normal; src: url('fonts/unifont.ttf')  }
@font-face { font-family: 'UNIFONT-ARIAL'; src: url('fonts/ARIALUNI.TTF')  }
@font-face { font-family: 'UNIFONT-ARIAL'; font-weight: bold; src: url('fonts/ARIALUNI.TTF')  }

@font-face { font-family: 'UNIFONT-ARIAL1'; src: url('fonts/arial.ttf')  }
@font-face { font-family: 'UNIFONT-ARIAL1'; font-weight: bold; src: url('fonts/arialbd.ttf')  }

Französisch Bienvenue!
Englisch Welcome!
Portugiesisch Bem-vindo!
Russisch Добро пожаловать!
Spanisch ¡Bienvenido!
Chinesisch vereinfacht 欢迎
Japanisch ようこそ。
Deutsch Willkommen!
Polnisch Witaj!
Ungarisch Isten hozott!
Niederländisch Welkom!
Rumänisch Fiţi bineveniţi!
Türkisch Hoşgeldiniz
Schwedisch Välkommen!
Italienisch Benvenuto!
Finnisch Tervetuloa!
Hebräisch ברוך הבא/ ברוכה הבאה/ ברוכים הבאים
Norwegisch Velkommen!
Slowakisch Vitajte!
Kroatisch Dobrodošli!
Arabisch أهلاً وسهلاً
Griechisch Καλώς ήρθες
Koreanisch 환영합니다
Tschechisch Vítejte!
Dänisch Velkommen!
Esperanto Bonvenon!
Slowenisch Dobrodošli, Pozdravljeni
Albanisch Mirë se vjen
Serbisch Dobrodošli!
Irisch Fáilte, Tá fáilte romhat/romhaibh
Estnisch Tere tulemast
Hindi स्वागत
Litauisch Sveiki atvykę
Thailändisch ยินดีต้อนรับ
Urdu خوش آمديد
Vietnamesisch Hoan nghênh / Được tiếp đãi ân cần
Bulgarisch Добре дошли!
Brasilianisches Portugiesisch Bem-vindo!
Latein Salve!
Isländisch Velkomin!
Afrikaans Welkom!
Faröisch Vælkomin!
Kurdisch Bi xêr bît, Bi xêr hatî
Persische Sprache خوش آمدی!
Mazedonisch Добредојде ;Добредојдовте
Bosnisch Dobrodošli
Aserbeidschanisch Xoş gəlmişsiniz!
Georgisch კეთილი იყოს თქვენი/შენი მობრძანება
Lettisch Laipni lūdzam!
Indonesisch Selamat datang
Ukrainisch Ласкаво просимо
Mongolisch Тавтай морилогтун
Malaiisch Selamat datang
Bengali স্বাগতম
Tagalog Mabuhay!
Baskisch Ongi etorri
Bretonisch Deuit mad deoc'h
Friesisch Wolkom!
 */
/*
Bienvenue!
Welcome!
Bem-vindo!
Добро пожаловать!
¡Bienvenido!
欢迎
ようこそ。
Willkommen!
Witaj!
Isten hozott!
Welkom!
Fiţi bineveniţi!
Hoşgeldiniz
Välkommen!
Benvenuto!
Tervetuloa!
ברוך הבא/ ברוכה הבאה/ ברוכים הבאים
Velkommen!
Vitajte!
Dobrodošli!
أهلاً وسهلاً
Καλώς ήρθες
환영합니다
Vítejte!
Velkommen!
Bonvenon!
Dobrodošli
Mirë se vjen
Dobrodošli!
Fáilte
Tere tulemast
स्वागत
Sveiki atvykę
ยินดีต้อนรับ
خوش آمديد
Hoan nghênh
Добре дошли!
Bem-vindo!
Salve!
Velkomin!
Welkom!
Vælkomin!
Bi xêr bît
خوش آمدی!
Добредојде
Dobrodošli
Xoş gəlmişsiniz!
კეთილი იყოს თქვენი
Laipni lūdzam!
Selamat datang
Ласкаво просимо
Тавтай морилогтун
Selamat datang
স্বাগতম
Mabuhay!
Ongi etorri
Deuit mad deoc'h
Wolkom!
*/
