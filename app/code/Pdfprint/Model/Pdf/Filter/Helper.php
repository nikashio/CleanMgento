<?php

namespace Snmportal\Pdfprint\Model\Pdf\Filter;

use Exception;
use IntlDateFormatter;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\ListsInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Status\History\Collection;
use Magento\Store\Model\ScopeInterface;
use Snmportal\Pdfprint\Model\Pdf\Barcode\Barcode;

class Helper extends AbstractModel
{
    const XML_PATH_EU_COUNTRIES_LIST = 'general/country/eu_countries';

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var ListsInterface
     */
    protected $_localeLists;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Group service
     *
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var Barcode
     */
    protected $barcodeGenerator;

    /*
    protected $_allowedFormats = array(
        Mage_Core_Model_Locale::FORMAT_TYPE_FULL,
        Mage_Core_Model_Locale::FORMAT_TYPE_LONG,
        Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM,
        Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
    );
    const DATETIME_INTERNAL_FORMAT = 'yyyy-MM-dd HH:mm:ss';
*/
    /**
     * Helper constructor.
     *
     * @param Context                  $context
     * @param Registry                 $registry
     * @param TimezoneInterface        $localeDate
     * @param ListsInterface           $localeLists
     * @param GroupRepositoryInterface $groupRepository
     * @param ScopeConfigInterface     $scopeConfig
     * @param Barcode                  $barcode
     * @param AbstractResource|null    $resource
     * @param AbstractDb|null          $resourceCollection
     * @param array                    $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        TimezoneInterface $localeDate,
        ListsInterface $localeLists,
        GroupRepositoryInterface $groupRepository,
        ScopeConfigInterface $scopeConfig,
        Barcode $barcode,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->barcodeGenerator = $barcode;
        $this->_localeDate = $localeDate;
        $this->groupRepository = $groupRepository;
        $this->_localeLists = $localeLists;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $registry, $resource, $resourceCollection);
    }

    /**
     * @param null $a
     * @param null $b
     *
     * @return bool
     */
    function eq($a = null, $b = null)
    {
        $a = $this->getValue($a, false);
        $b = $this->getValue($b, false);

        return (!is_null($a) && !is_null($b)) ? ($a == $b) : false;
    }

    /**
     * @param      $a
     * @param bool $noObject
     *
     * @return null|string
     */
    function getValue($a, $noObject = true)
    {
        if (!$this->getProcessor()) {
            return $a;
        }
        if (is_numeric($a)) { // 07.12.15
            return $a;
        }
        if ($this->_isConst($a)) {
            // Konstante
            return $a;
        }

        //$cl = $this->getProcessor()->getAuitVariableLine();
        if (!$noObject && $this->_isConst($a)) {
            // Konstante
            return $a;
        }
        if (!is_null($a)) {
            $r = null;
            try {
                //             set_error_handler('AuIt_ErrorHandler');
                $r = $this->getProcessor()
                          ->filter('{{var ' . $a . '}}');
                if ($r === 'Object') {
                    $vars = $this->getProcessor()
                                 ->getVariables();
                    if (isset($vars[$a])) {
                        $r = $vars[$a];
                    }
                }


                if (is_object($r) || $r == '') {
                    if ($r instanceof DataObject) {
                        if (!$noObject) {
                            $r = null;
                        } else {
                            $r = implode(',', $r->debug());
                        }
                    }
                }
            } catch (Exception $e) {
                $r = null;
            }
            //      set_error_handler(Mage_Core_Model_App::DEFAULT_ERROR_HANDLER);

            if (is_null($r)) { // 03.03.15 Empty Value nicht zurück setzen
                $r = $a;
            }

            //if ( !$r ) $r=$a;
            return $r;
        }

        /** @noinspection PhpExpressionAlwaysNullInspection */
        return $a;
    }

    /**
     * @param $a
     *
     * @return bool
     */
    protected function _isConst($a)
    {
        if (!$a) {
            return true;
        }
        $cl = $this->getProcessor()
                   ->getAuitVariableLine();
        if (strpos($cl, "'" . $a . "'") !== false) {
            return true;
        }
        if (strpos($cl, '"' . $a . '"') !== false) {
            return true;
        }

        return false;
    }

    /**
     * @param null $a
     * @param null $b
     *
     * @return bool
     */
    function neq($a = null, $b = null)
    {
        $a = $this->getValue($a, false);
        $b = $this->getValue($b, false);

        return (!is_null($a) && !is_null($b)) ? ($a != $b) : false;
    }

    /**
     * @param null $a
     * @param null $b
     *
     * @return bool
     */
    function lt($a = null, $b = null)
    {
        $a = $this->getValue($a, false);
        $b = $this->getValue($b, false);

        return (!is_null($a) && !is_null($b)) ? ($a < $b) : false;
    }

    /**
     * @param null $a
     * @param null $b
     *
     * @return bool
     */
    function lteq($a = null, $b = null)
    {
        $a = $this->getValue($a, false);
        $b = $this->getValue($b, false);

        return (!is_null($a) && !is_null($b)) ? ($a <= $b) : false;
    }

    /**
     * @param null $a
     * @param null $b
     *
     * @return bool
     */
    function gt($a = null, $b = null)
    {
        $a = $this->getValue($a, false);
        $b = $this->getValue($b, false);

        return (!is_null($a) && !is_null($b)) ? ($a > $b) : false;
    }

    /**
     * @param null $a
     * @param null $b
     *
     * @return bool
     */
    function gteq($a = null, $b = null)
    {
        $a = $this->getValue($a, false);
        $b = $this->getValue($b, false);

        return (!is_null($a) && !is_null($b)) ? ($a >= $b) : false;
    }

    /**
     * @param null $a
     *
     * @return string
     */
    function nl2br($a = null)
    {
        return nl2br(trim($this->getValue($a)));
    }

    /**
     * @param null $a
     *
     * @return string
     */
    function tolower($a = null)
    {
        $a = $this->getValue($a);

        return strtolower($a);
    }

    /**
     * @param null $a
     *
     * @return string
     */
    function toupper($a = null)
    {
        $a = $this->getValue($a);

        return strtoupper($a);
    }

    /**
     * @param null $a
     * @param null $b
     *
     * @return string
     */
    function country($a = null, $b = null)
    {
        $r = '';
        $a = $this->getValue($a);
        if ($a) {
            $r = $this->_localeLists->getCountryTranslation($a);
        }
        if ($b == 1) {
            $r = strtoupper($r);
        }
        if ($b == 2) {
            $r = strtolower($r);
        }

        return $r;
    }

    /**
     * @param null   $a
     * @param int    $b
     * @param string $c
     * @param int    $showtime
     *
     * @return string
     */
    function date($a = null, $b = 0, $c = 'medium', $showtime = 0)
    {
        if (!is_numeric($b)) {
            $b = (int)$this->getValue($b);
        }
        if ($a) {
            $a = $this->getValue($a);
        }
        //$c = $this->getValue($c);

        $order = $this->getProcessor()
                      ->auitVariable('order');
        $format = null;
        switch ($c) {
            case 'medium':
                $format = IntlDateFormatter::MEDIUM;
                break;
            case 'short':
                $format = IntlDateFormatter::SHORT;
                break;
            case 'long':
                $format = IntlDateFormatter::LONG;
                break;
            case 'full':
                $format = IntlDateFormatter::FULL;
                break;
        }
        if ($format) {
            if ($b) {
                return $this->formatdate($order->getStore(), $a . (" $b days"), $format, $showtime);
            }

            return $this->formatdate($order->getStore(), $a, $format, $showtime);
        }
        $date = $b ? $this->_localeDate->date($a . (" $b days")) : $this->_localeDate->date($a);

        // http://php.net/manual/en/function.date.php
        return $date->format($c);
    }

    /**
     * @param      $store
     * @param      $date
     * @param int  $format
     * @param bool $showTime
     *
     * @return string
     */
    public function formatdate($store, $date, $format = IntlDateFormatter::MEDIUM, $showTime = false)
    {
        /**
         * FIx 2.8.11 mit Timezone Problem
         */
        return $this->_localeDate->formatDate(
            $date,
            $format,
            $showTime
        );
        /**
         * FIx 2.8.11
         *
         * return $this->_localeDate->formatDate(
         * $this->_localeDate->scopeDate($store, $date, true),
         * $format,
         * $showTime
         * );
         * **/
    }

    /**
     * @return bool
     */
    function hasGiftMessage()
    {
        $order = $this->getProcessor()
                      ->auitVariable('order');
        if ($order && is_object($order) && $order->getGiftMessageId()) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    function hasComments()
    {
        $entity = $this->getProcessor()
                       ->auitVariable('entity');
        $_collection = null;
        if ($entity && $entity instanceof Order) {
            $_collection = $entity->getStatusHistoryCollection();
        } else {
            if ($entity) {
                $_collection = $entity->getCommentsCollection();
            }
        }
        if ($_collection && count($_collection)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    function hasVisibleComments()
    {
        $entity = $this->getProcessor()
                       ->auitVariable('entity');
        $_collection = null;
        if ($entity && $entity instanceof Order) {
            $_collection = $entity->getStatusHistoryCollection();
        } else {
            if ($entity) {
                $_collection = $entity->getCommentsCollection();
            }
        }
        if ($_collection && count($_collection)) {
            foreach ($_collection as $_comment) {
                if ($_comment->getIsVisibleOnFront()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return Collection|null
     */
    function getCommentsCollection()
    {
        $entity = $this->getProcessor()
                       ->auitVariable('entity');
        $_collection = null;
        if ($entity && $entity instanceof Order) {
            $_collection = $entity->getStatusHistoryCollection();
        } else {
            if ($entity) {
                $_collection = $entity->getCommentsCollection();
            }
        }

        return $_collection;
    }

    /**
     * @param $countryCode
     *
     * @return bool
     */
    function isCountryNotInEU($countryCode)
    {
        return !$this->isCountryInEU($countryCode);
    }

    /**
     * @param      $countryCode
     * @param bool $isString
     *
     * @return bool
     */
    function isCountryInEU($countryCode, $isString = false)
    {
        if (!$isString) {
            $countryCode = trim($this->getValue($countryCode));
        }
        if (!$countryCode) { // AUIT 19.03.2013 empty codes
            return false;
        }
        if (1) {
            $order = $this->getProcessor()
                          ->auitVariable('order');
            $storeId = null;
            if ($order) {
                $storeId = $order->getStore()
                                 ->getId();
            }

            return $this->_isCountryInEU($countryCode, $storeId);
        }

        return false;
    }

    /**
     * @param      $countryCode
     * @param null $storeId
     *
     * @return bool
     */
    protected function _isCountryInEU($countryCode, $storeId = null)
    {
        $euCountries = explode(
            ',',
            $this->scopeConfig->getValue(
                self::XML_PATH_EU_COUNTRIES_LIST,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );

        return in_array($countryCode, $euCountries);
    }

    /**
     * @param null $tax_amount
     * @param null $country_id
     * @param null $vat_id
     * @param null $country_id2
     * @param null $vat_id2
     * @param null $taxvat
     *
     * @return int
     */
    function isEUVATTaxFree(
        $tax_amount = null,
        $country_id = null,
        $vat_id = null,
        $country_id2 = null,
        $vat_id2 = null,
        $taxvat = null
    ) {
        if (!is_null($tax_amount)) {
            $vidCode = $this->getVatID($country_id, $vat_id, $country_id2, $vat_id2, $taxvat);
            if (strlen($vidCode) > 2) {
                $cid = substr($vidCode, 0, 2);
                if ($cid == 'EL') {
                    $cid = 'GR';
                }
                $ta = floatval($this->getValue($tax_amount));
                if (('' . $ta != $tax_amount && $ta == 0)) {
                    if ($this->isCountryInEU($cid, true)) {
                        return 1;
                    }
                }
            }
        }

        return 0;
    }

    /**
     * @param null $country_id
     * @param null $vat_id
     * @param null $country_id2
     * @param null $vat_id2
     * @param null $taxvat
     *
     * @return string
     */
    function getVatID($country_id = null, $vat_id = null, $country_id2 = null, $vat_id2 = null, $taxvat = null)
    {
        $cid = false;
        $vid = false;
        if (!is_null($country_id)) {
            $countryCode = trim($this->getValue($country_id));
            if ($country_id != $countryCode && strlen($countryCode) == 2) {
                $cid = $country_id;
                $vid = $vat_id;
            }
        }
        if (!$cid && !is_null($country_id2)) {
            $countryCode = trim($this->getValue($country_id2));
            if ($country_id2 != $countryCode && strlen($countryCode) == 2) {
                $cid = $country_id2;
                $vid = $vat_id2;
            }
        }
        if ($cid && $vid) {
            $vidCode = trim($this->getValue($vid));
            if ($vidCode != $vid && $vidCode) { // Variable not set
                $countryCode = strtoupper(trim($this->getValue($cid)));
                $vidCode = strtoupper(trim($vidCode));
                if (substr($vidCode, 0, strlen($countryCode)) == $countryCode) {
                    return $vidCode;
                }

                return strtoupper(trim($countryCode . $vidCode));
            }
        }
        if (!is_null($taxvat)) {
            $vidCode = trim($this->getValue($taxvat));
            if ($vidCode != $taxvat && $vidCode) { // Variable not set
                return strtoupper(trim($vidCode));
            }
        }

        return '';
    }

    /**
     * @param null $tax_amount
     * @param null $country_id
     * @param null $country_id2
     *
     * @return int
     */
    function isWorldTaxFree($tax_amount = null, $country_id = null, $country_id2 = null)
    {
        if (is_null($tax_amount)) {
            return 0;
        }
        $tax_amount = floatval($this->getValue($tax_amount));
        if (!$tax_amount) {
            $cid = false;
            // Check for emtpy conutryid 19.03.2013
            if (!is_null($country_id)) {
                $countryCode = trim($this->getValue($country_id));
                if ($country_id != $countryCode && strlen($countryCode) == 2) {
                    $cid = $country_id;
                }
            }
            if (!$cid && !is_null($country_id2)) {
                $countryCode = trim($this->getValue($country_id2));
                if ($country_id2 != $countryCode && strlen($countryCode) == 2) {
                    $cid = $country_id2;
                }
            }
            if ($cid && !$this->isCountryInEU($cid)) {
                return 1;
            }
        }

        return 0;
    }

    /**
     * @param $customerGroupId
     *
     * @return string
     */
    function getCustomerGroupName($customerGroupId)
    {
        $customerGroupId = $this->getValue($customerGroupId);
        //if ($this->getOrder()) {
        //$customerGroupId = $this->getOrder()->getCustomerGroupId();
        try {
            if ($customerGroupId !== null) {
                return $this->groupRepository->getById($customerGroupId)
                                             ->getCode();
            }
        } catch (Exception $e) {
            return '';
        }

        //}
        return '';
//        $customer_group_id = $this->getValue($customer_group_id);
//        return Mage::getModel('customer/group')
        //          ->load($customer_group_id)
        //        ->getCustomerGroupCode();
    }

    /**
     * @param $price
     *
     * @return float
     */
    function roundPrice($price)
    {
        //$price = $this->getValue($price);
        return $this->round($price);
    }

    /**
     * @param     $price
     * @param int $anzahl
     *
     * @return float
     */
    function round($price, $anzahl = 2)
    {
        $price = $this->getValue($price);
        $anzahl = $this->getValue($anzahl, false);

        return round($price, (int)$anzahl);
    }

    /**
     * @param     $price
     * @param int $addBrackets
     *
     * @return mixed
     */
    function formatPrice($price, $addBrackets = 0)
    {
        $price = $this->getValue($price);
        $addBrackets = $this->getValue($addBrackets);
        $order = $this->getProcessor()
                      ->auitVariable('order');

        return $order->formatPrice($price, $addBrackets);
    }

    /**
     * @return bool
     */
    function isShippingNeqBillingAddress()
    {
        return !$this->isShippingEqBillingAddress();
    }

    /**
     * @return bool
     */
    function isShippingEqBillingAddress()
    {
        $entity = $this->getProcessor()
                       ->auitVariable('entity');
        if (!$entity->getShippingAddress()) {
            return true;
        }

        $billingAdress = $entity->getBillingAddress();
        $shippingAdress = $entity->getShippingAddress();
        if (!$shippingAdress || !$billingAdress) {
            return true;
        }
        foreach (['postcode', 'lastname', 'firstname', 'street', 'city', 'country_id'] as $code) {
            if ($billingAdress->getData($code) != $shippingAdress->getData($code)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    function hasBillingAddress()
    {
        $entity = $this->getProcessor()
                       ->auitVariable('entity');

        return $entity && $entity->getBillingAddress();
    }

    /**
     * @return bool
     */
    function hasShippingAddress()
    {
        $entity = $this->getProcessor()
                       ->auitVariable('entity');

        return $entity && $entity->getShippingAddress();
    }

    /**
     * @param        $type
     * @param        $code
     * @param int    $w
     * @param int    $h
     * @param string $color
     *
     * @return string
     */
    function getBarcode1D($type, $code, $w = 2, $h = 30, $color = 'black')
    {
        $type = $this->getValue($type);
        $code = $this->getValue($code);

        return $this->barcodeGenerator->getBarcode1D($code, $type, $w, $h, $color);
    }

    /**
     * @param        $type
     * @param        $code
     * @param int    $w
     * @param int    $h
     * @param string $color
     *
     * @return string
     */
    function getBarcode2D($type, $code, $w = 10, $h = 10, $color = 'black')
    {
        $type = $this->getValue($type);
        $code = $this->getValue($code);

        return $this->barcodeGenerator->getBarcode2D($code, $type, $w, $h, $color);
    }

    /**
     * @return Barcode
     */
    function getBarcodeGenerator()
    {
        return $this->barcodeGenerator;
    }
}
