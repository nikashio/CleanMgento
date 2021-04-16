<?php

namespace Snmportal\Pdfprint\Logger;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Logger extends \Monolog\Logger
{
    protected $_scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $name,
        array $handlers = [],
        array $processors = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($name, $handlers, $processors);
    }

    public function addRecord($level, $message, array $context = [])
    {
        if ($this->getStoreConfig('snmportal_pdfprint/general/logging')) {
            parent::addRecord($level, $message, $context);
        }
    }

    protected function getStoreConfig($pfad, $store = null)
    {
        return $this->_scopeConfig->getValue(
            $pfad,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
