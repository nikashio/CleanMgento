<?php

namespace Snmportal\Pdfprint\Helper;

use Exception;
use Magento\Backend\Model\Session;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Cache\Type\Collection;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Module\ModuleResource;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Snmportal\Pdfprint\Controller\Adminhtml\FileFactory;
use Snmportal\Pdfprint\Logger\Logger;
use Zend\Http\Client;
use Zend\Json\Json;

class Base extends AbstractHelper
{
    const MNAME = 'Snmportal_Pdfprint';

    const MKEY = 'snm-pdf-m2-001';

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var WriteInterface
     */
    private $tmpDirectory;

    /**
     * @var WriteInterface
     */
    private $cacheDirectory;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $_backendUrl;

    /**
     * @var Config
     */
    private $resourceConfig;

    /**
     * @var string
     */
    //private $locale;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var ModuleResource
     */
    private $moduleResource;

    /**
     * @var Logger
     */
    private $snmLogger;

    private $fileCounter = 0;

    private $fileprev = '';

    /**
     * @var \Magento\Config\Model\Config
     */
    private $config;

    /**
     * @var Collection
     */
    private $cache;

    /**
     * Base constructor.
     *
     * @param Logger                              $snmLogger
     * @param FileFactory                         $fileFactory
     * @param \Magento\Config\Model\Config        $configModel
     * @param Collection                          $cache
     * @param StoreManagerInterface               $storeManager
     * @param ModuleResource                      $moduleResource
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param Context                             $context
     * @param Config                              $resourceConfig
     * @param Filesystem                          $filesystem
     * @param ProductMetadataInterface            $productMetadata
     * @param Session                             $session
     *
     * @throws FileSystemException
     */
    public function __construct(
        Logger $snmLogger,
        FileFactory $fileFactory,
        \Magento\Config\Model\Config $configModel,
        Collection $cache,
        StoreManagerInterface $storeManager,
        ModuleResource $moduleResource,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        Context $context,
        Config $resourceConfig,
        Filesystem $filesystem,
        ProductMetadataInterface $productMetadata,
        Session $session
    ) {
        $this->fileFactory = $fileFactory;
        $this->cache = $cache;
        $this->snmLogger = $snmLogger;
        $this->config = $configModel;
        $this->moduleResource = $moduleResource;
        $this->productMetadata = $productMetadata;
        $this->storeManager = $storeManager;
        $this->_backendUrl = $backendUrl;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->tmpDirectory = $filesystem->getDirectoryWrite(DirectoryList::TMP);
        $this->cacheDirectory = $filesystem->getDirectoryWrite(DirectoryList::CACHE);
        $this->resourceConfig = $resourceConfig;
        parent::__construct(
            $context
        );
    }

    public function __destruct()
    {
        $this->cleanTempFile();
    }

    public function cleanTempFile()
    {
        while ($this->fileCounter > 0) {
            $tmpFile = $this->fileprev . $this->fileCounter;
            $this->tmpDirectory->delete($tmpFile);
            $this->fileCounter--;
        }
    }

    /**
     * @param $data
     *
     * @return string
     * @throws FileSystemException
     */
    public function saveTempFile($data)
    {
        $this->fileCounter++;
        $tmpFile = $this->fileprev . $this->fileCounter;
        $this->tmpDirectory->writeFile($tmpFile, $data);

        return $this->tmpDirectory->getAbsolutePath($tmpFile);
    }

    /**
     * @return WriteInterface
     */
    public function getMediaDirectory()
    {
        return $this->mediaDirectory;
    }

    /**
     * @param $file
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMediaUrl($file)
    {
        return $this->getBaseMediaUrl() . $file;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getBaseMediaUrl()
    {
        return $this->storeManager->getStore()
                                  ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @return bool|false|string
     */
    public function getModulVersion()
    {
        return $this->moduleResource->getDataVersion(self::MNAME);
    }

    public function Log()
    {
        $r = $this->Info(false);
        if ($r && isset($r['in']) && ($r['in'] & 8)) {
            throw new Exception(
                (isset($r['msg']) && $r['msg']) ? $r['msg'] : 'Ple' . 'ase ch' . 'eck yo' . 'ur li' . 'cen' . 'se k' . 'ey'
            );
        }
    }

    /**
     * @param bool $bload
     *
     * @return array|bool|mixed|null|string
     */
    public function Info($bload = true)
    {
        $result = false;
        $cacheKey = 'snm' . 'info_' . self::MNAME;
        $cacheFile = 'mage-' . 'snm/' . $cacheKey;
        $data = null;
        try {
            if ($this->cacheDirectory->isFile($cacheFile)) {
                $stat = $this->cacheDirectory->stat('mage-snm/' . $cacheKey);
                $diff = time() - $stat['mtime'];
                if (!$bload || $diff < 86400) {
                    $data = $this->cacheDirectory->readFile('mage-snm/' . $cacheKey);
                    if ($data) {
                        $data = ObjectManager::getInstance()
                                             ->get('\Magento\Framework\Serialize\Serializer\Serialize')
                                             ->unserialize($data);
                    }
                }
            }
        } catch (Exception $e) {
        }
        try {
            $params = $this->getParam();
            if (is_array($data)) {
                $result = $data;
                if (!isset($result['s']) || $result['s'] != $params['s']) {
                    $result = false;
                } else {
                    if (!isset($result['ek']) || $result['ek'] != $params['ek']) {
                        $result = false;
                    }
                }
            }
            if (!$result && $bload) {
                $params = base64_encode(Json::encode($params));
                $url = 'htt' . 'ps:/' . '/sn' . 'm-por' . 'tal.c' . 'om/me' . 'dia/mo' . 'dule/mo' . 'dule.json';
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $jsoninfo = file_get_contents($url . '?i=' . $params);
                } else {
                    $client = new Client($url, ['timeout' => 10]);
                    $client->setParameterGet(['i' => $params]);
                    $jsoninfo = $client->send()
                                       ->getBody();
                }
                if ($jsoninfo) {
                    $info = json_decode($jsoninfo, true);
                    if (is_array($info) && isset($info['version'])) {
                        $result = $info;
                        $this->cacheDirectory->writeFile(
                            'mage-snm/' . $cacheKey,
                            ObjectManager::getInstance()
                                         ->get(
                                             '\Magento\Framework\Serialize\Serializer\Serialize'
                                         )
                                         ->serialize($result)
                        );
                    }
                }
            }
        } catch (Exception $e) {
            $this->snmLogger->addError($e->getMessage());
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getParam()
    {
        return [
            's'  => trim($this->getServerName()),
            'mv' => $this->productMetadata->getVersion(),
            'ex' => self::MKEY,
            'ek' => trim($this->getStoreConfig('snmportal_pdf' . 'print' . '/gen' . 'eral/li' . 'ce' . 'nse')),
            'ev' => $this->moduleResource->getDataVersion(self::MNAME)
        ];
    }

    /**
     * @return string
     */
    public function getServerName()
    {
        //$url = $this->_backendUrl->getRebuiltUrl($this->_backendUrl->getBaseUrl());
        if ($this->_backendUrl->getHost()) {
            return $this->_backendUrl->getHost();
        }

        return $this->_backendUrl->getBaseUrl();
    }

    /**
     * @param      $pfad
     * @param null $store
     *
     * @return mixed
     */
    public function getStoreConfig($pfad, $store = null)
    {
        return $this->scopeConfig->getValue(
            $pfad,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
