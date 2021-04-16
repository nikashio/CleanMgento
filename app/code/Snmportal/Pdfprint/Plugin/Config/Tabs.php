<?php

namespace Snmportal\Pdfprint\Plugin\Config;

class Tabs
{
    public function afterEscapeHtml(
        $subject,
        $result
    ) {
        if (stripos($result, 'snm-portal.com/media/module/module_snm.png') !== false) {
            return 'SNM-PORTAL';
        }

        return $result;
    }
}
