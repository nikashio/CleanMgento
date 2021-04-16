<?php

namespace Snmportal\Pdfprint\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends Base
{
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
