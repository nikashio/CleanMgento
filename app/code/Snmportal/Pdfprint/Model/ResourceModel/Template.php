<?php

namespace Snmportal\Pdfprint\Model\ResourceModel;

use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Template extends AbstractDb
{
    /**
     * Store model
     *
     * @var null|Store
     */
    protected $_store = null;

    /**
     * @var DateTime\DateTime
     */
    protected $_date;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * Construct
     *
     * @param Context               $context
     * @param DateTime\DateTime     $date
     * @param StoreManagerInterface $storeManager
     * @param DateTime              $dateTime
     * @param string|null           $resourcePrefix
     */
    public function __construct(
        Context $context,
        DateTime\DateTime $date,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
        $this->_storeManager = $storeManager;
        $this->dateTime = $dateTime;
    }

    /**
     * Load an object using 'identifier' field if there's no field specified and value is not numeric
     *
     * @param AbstractModel $object
     * @param mixed         $value
     * @param string        $field
     *
     * @return $this
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'identifier';
        }

        return parent::load($object, $value, $field);
    }

    /**
     * Check if page identifier exist for specific store
     * return page id if page exists
     *
     * @param string $identifier
     * @param int    $storeId
     *
     * @return int
     * @throws LocalizedException
     */

    public function checkIdentifier($identifier, $storeId)
    {
        if (is_array($storeId)) {
            $stores = $storeId;
        } else {
            $stores = [$storeId];
        }
        //$stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID, $storeId];
        $select = $this->_getLoadByIdentifierSelect($identifier, $stores, 1);
        $select->reset(\Zend\Db\Sql\Select::COLUMNS)
               ->columns('cp.template_id')
               ->order('cps.store_id DESC')
               ->limit(1);

        return $this->getConnection()
                    ->fetchOne($select);
    }
    //'pdf_background','pdf_appendix','pdf_attachment1','pdf_attachment2',
    //'uniq_template_id'

    /**
     * Retrieve load select with filter by identifier, store and activity
     *
     * @param string    $identifier
     * @param int|array $store
     * @param int       $isActive
     *
     * @return Select
     * @throws LocalizedException
     */
    protected function _getLoadByIdentifierSelect($identifier, $store, $isActive = null)
    {
        $select = $this->getConnection()
                       ->select()
                       ->from(
                           ['cp' => $this->getMainTable()]
                       )
                       ->join(
                           ['cps' => $this->getTable('snm_pdfprint_template_store')],
                           'cp.template_id = cps.template_id',
                           []
                       )
                       ->where(
                           'cp.identifier = ?',
                           $identifier
                       )
                       ->where(
                           'cps.store_id IN (?)',
                           $store
                       );

        if (!is_null($isActive)) {
            $select->where('cp.is_active = ?', $isActive);
        }

        return $select;
    }

    /**
     * Retrieve store model
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->_store);
    }

    /**
     * Set store model
     *
     * @param Store $store
     *
     * @return $this
     */
    public function setStore($store)
    {
        $this->_store = $store;

        return $this;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('snm_pdfprint_template', 'template_id');
    }

    /**
     * Process page data before deleting
     *
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        $condition = ['template_id = ?' => (int)$object->getId()];

        $this->getConnection()
             ->delete($this->getTable('snm_pdfprint_template_store'), $condition);

        return parent::_beforeDelete($object);
    }

    /**
     * Process page data before saving
     *
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /*
         * For two attributes which represent timestamp data in DB
         * we should make converting such as:
         * If they are empty we need to convert them into DB
         * type NULL so in DB they will be empty and not some default value
         */
        /*
                if (!$this->isValidPageIdentifier($object)) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The page URL key contains capital letters or disallowed symbols.')
                    );
                }

                if ($this->isNumericPageIdentifier($object)) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The page URL key cannot be made of only numbers.')
                    );
                }
        */
        if ($object->isObjectNew() && !$object->hasCreationTime()) {
            $object->setCreationTime($this->_date->gmtDate());
        }

        $object->setUpdateTime($this->_date->gmtDate());

        $this->_saveBlobData($object, $this->getBlobFields());

        return parent::_beforeSave($object);
    }

    /**
     * @param AbstractModel $object
     * @param               $fields
     */
    protected function _saveBlobData(AbstractModel $object, $fields)
    {
        foreach ($fields as $field => $items) {
            $blob = (string)$object->getData($field);
            //$data = (array)@unserialize($blob);
            $data = [];
            try {
                $data = (array)ObjectManager::getInstance()
                                            ->get('\Magento\Framework\Serialize\Serializer\Serialize')
                                            ->unserialize($blob);
            } catch (Exception $e) {

            }
            foreach ($items as $blobField) {
                if (!is_null($object->getData($blobField))) {
                    if ($this->isSerialBlobField($blobField)) {
                        $a = $object->getData($blobField);
                        if (is_array($a)) {
                            $d = $a;
                            $a = [];
                            foreach ($d as $v) {
                                if (!isset($v['delete']) || !$v['delete']) {
                                    $a[] = $v;
                                }
                            }
                        }
                        //$data[$blobField] = (string)@serialize($a);
                        $data[$blobField] = (string)ObjectManager::getInstance()
                                                                 ->get(
                                                                     '\Magento\Framework\Serialize\Serializer\Serialize'
                                                                 )
                                                                 ->serialize($a);
                    } else {
                        $data[$blobField] = (string)$object->getData($blobField);
                    }
                }
            }
            //$object->setData($field, (string)@serialize($data));
            $object->setData(
                $field,
                (string)ObjectManager::getInstance()
                                     ->get('\Magento\Framework\Serialize\Serializer\Serialize')
                                     ->serialize($data)
            );
        }
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function isSerialBlobField($name)
    {
        return in_array(
            $name,
            [
                'free_items',
                'free_items_p2',
                'margins1',
                'margins2',
                'table_columns',
                'table_totals',
                'block_templates',
                'translation_table',
                'pdf_files'
            ]
        );
    }

    /**
     * @return array
     */
    public function getBlobFields()
    {
        return [
            'content3' => [
                'pdf_appendix_use',
                'pdf_attachment1_use',
                'pdf_attachment1_name',
                'pdf_attachment2_use',
                'pdf_attachment2_name',
                'free_items',
                'free_items_p2',
                'text_before_table',
                'text_before_table',
                'text_after_table',
                'css',
                'margins1',
                'margins2',
                'table_columns',
                'block_templates',
                'pdf_download_name',
                'table_columns_use_default',
                'table_totals',
                'table_totals_use_custom',
                'translation_table',
                'table_taxrenderer_default',
                'table_tax_full_summary',
                'table_tax_all'
            ],
            'content'  => ['pdf_files']
        ];
    }

    /**
     * Assign page to store views
     *
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        if (empty($newStores)) {
            $newStores = (array)$object->getStoreId();
        }
        $table = $this->getTable('snm_pdfprint_template_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = ['template_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete];

            $this->getConnection()
                 ->delete($table, $where);
        }

        if ($insert) {
            $data = [];

            foreach ($insert as $storeId) {
                $data[] = ['template_id' => (int)$object->getId(), 'store_id' => (int)$storeId];
            }

            $this->getConnection()
                 ->insertMultiple($table, $data);
        }

        return parent::_afterSave($object);
    }

    /**
     *  Check whether page identifier is numeric
     *
     * @param AbstractModel $object
     *
     * @return bool
     */
    /*
    protected function isNumericPageIdentifier(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }
*/
    /**
     *  Check whether page identifier is valid
     *
     * @param AbstractModel $object
     *
     * @return bool
     */
    /*
    protected function isValidPageIdentifier(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('identifier'));
    }


*/

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $pageId
     *
     * @return array
     */
    public function lookupStoreIds($pageId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
                             ->from(
                                 $this->getTable('snm_pdfprint_template_store'),
                                 'store_id'
                             )
                             ->where(
                                 'template_id = :template_id'
                             );

        $binds = [':template_id' => (int)$pageId];

        return $connection->fetchCol($select, $binds);
    }

    /**
     * Perform operations after object load
     *
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {

        if ($object->getId()) {
            // 2.2. ss
            if (!$object->getData('store_id')) {
                $stores = $this->lookupStoreIds($object->getId());
                $object->setData('store_id', $stores);
            }
        }
        $this->_loadBlobData($object, $this->getBlobFields());

        return parent::_afterLoad($object);
    }

    /**
     * @param AbstractModel $object
     * @param               $fields
     */
    protected function _loadBlobData(AbstractModel $object, $fields)
    {
        foreach ($fields as $field => $items) {
            $blob = (string)$object->getData($field);
            //$data = (array)@unserialize($blob);
            $data = [];
            try {
                $data = (array)ObjectManager::getInstance()
                                            ->get('\Magento\Framework\Serialize\Serializer\Serialize')
                                            ->unserialize($blob);
            } catch (Exception $e) {

            }
            foreach ($items as $blobField) {
                if (isset($data[$blobField])) {
                    if ($this->isSerialBlobField($blobField)) {
                        if ($data[$blobField]) {
                            //$object->setData($blobField, @unserialize($data[$blobField]));
                            try {
                                $object->setData(
                                    $blobField,
                                    ObjectManager::getInstance()
                                                 ->get('\Magento\Framework\Serialize\Serializer\Serialize')
                                                 ->unserialize($data[$blobField])
                                );
                            } catch (Exception $e) {

                            }
                        } else {
                            $object->setData($blobField, []);
                        }
                    } else {
                        $object->setData($blobField, $data[$blobField]);
                    }
                }
            }
        }
        if (is_null($object->getData('table_taxrenderer_default'))) {
            $object->setData('table_taxrenderer_default', 1);
        }
        if (is_null($object->getData('table_tax_full_summary'))) {
            $object->setData('table_tax_full_summary', 0);
        }
        if (is_null($object->getData('table_tax_all'))) {
            $object->setData('table_tax_all', 1);
        }
    }

    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $storeIds = [Store::DEFAULT_STORE_ID, (int)$object->getStoreId()];
            $select->join(
                ['snm_pdfprint_template_store' => $this->getTable('snm_pdfprint_template_store')],
                $this->getMainTable() . '.template_id = snm_pdfprint_template_store.template_id',
                []
            )
                   ->where(
                       'is_active = ?',
                       1
                   )
                   ->where(
                       'snm_pdfprint_template_store.store_id IN (?)',
                       $storeIds
                   )
                   ->order(
                       'snm_pdfprint_template_store.store_id DESC'
                   )
                   ->limit(
                       1
                   );
        }

        return $select;
    }
}
