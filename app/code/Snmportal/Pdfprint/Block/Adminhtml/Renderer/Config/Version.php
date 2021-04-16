<?php

namespace Snmportal\Pdfprint\Block\Adminhtml\Renderer\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Snmportal\Pdfprint\Helper\Base;

class Version extends Field
{
    const MNAME = 'Snmportal_Pdfprint';

    const MKEY = 'snm-pdf-m2-001';

    /**
     * @var Base
     */
    protected $baseHelper;

    /**
     * Version constructor.
     *
     * @param Base    $baseHelper
     * @param Context $context
     * @param array   $data
     */
    public function __construct(
        Base $baseHelper,
        Context $context,
        array $data = []
    ) {
        $this->baseHelper = $baseHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $dVersion = $this->baseHelper->getModulVersion();
        $info = $this->baseHelper->Info();
        $html = '<div style="padding-top:7px">';
        $html .= '<div >' . $dVersion . '&#160;(<span>' . $this->baseHelper->getServerName() . '</span>)</div>';
        if ($info) {
            if (version_compare($info['version'], $dVersion) > 0) {
                $html .= '<b>' . __('New version %1 available on the server.', $info['version']) . '</b>';
            } else {
                $html .= '<b>' . __('is current version.') . '</b>';
            }
            if (isset($info['msg'])) {
                $html .= '<br/> ' . $info['msg'] . '';
            }
        }
        $html .= '</div>';

        return $html;
    }
    /*
        CONST testField='snmportal_pdfprint_general_l'.'ic'.'en'.'se';
        private $moduleResource;
        public function __construct(\Magento\Framework\Module\ModuleResource $moduleResource,
                                    \Magento\Backend\Block\Template\Context $context, array $data = [])
        {
            $this->moduleResource = $moduleResource;
            parent::__construct($context, $data);
        }
        protected function _getElementHtml(AbstractElement $element)
        {
            $dVersion = $this->moduleResource->getDataVersion('Snmportal_Pdfprint');
            $f = 'aw2saa';
            $a=array('m'=>'m2pdf','f'=> 'pdfprint');
            $f = '\''.$f.'\'';
            return ''.$dVersion.$this->prxa($a,$f);//.$this->prx();
        }
        protected function prxa($a,$f)
        {
            return "<script>
    require([
        \"jquery\",\"validation\"
    ], function ($,validation) {
    if ( typeof(AuItJ) == \"undefined\" )
        window.AuItJ = {};
    AuItJ.c=function(s) {
      s = String(s);
      var polynomial = arguments.length < 2 ? 0x04C11DB7 : arguments[1],
          initialValue = arguments.length < 3 ? 0xFFFFFFFF : arguments[2],
          finalXORValue = arguments.length < 4 ? 0xFFFFFFFF : arguments[3],
          crc = initialValue,table = [], i, j;
      function reverse(x, n) {
        var b = 0;
        while (n) {
          b = b * 2 + x % 2;
          x /= 2;
          x -= x % 1;
          n--;
        }
        return b;
      }
      var range = 255, c=0;
      for (i = 0; i < s.length; i++){
        c = s.charCodeAt(i);
        if(c>range){ range=c; }
      }

      for (i = range; i >= 0; i--) {
        c = reverse(i, 32);

        for (j = 0; j < 8; j++) {
          c = ((c * 2) ^ (((c >>> 31) % 2) * polynomial)) >>> 0;
        }

        table[i] = reverse(c, 32);
      }

      for (i = 0; i < s.length; i++) {
        c = s.charCodeAt(i);
        if (c > range) {
          throw new RangeError();
        }
        j = (crc % 256) ^ c;
        crc = ((crc / 256) ^ table[j]) >>> 0;
      }
      return (crc ^ finalXORValue) >>> 0;
    };


    AuItJ.ha=function (hex) {
        var str = '';
        for (var i = 0; i < hex.length; i += 2)
            str += String.fromCharCode(parseInt(hex.substr(i, 2), 16));
        return str;
    };

    AuItJ.v = function(v) {
        v = v.split('-');
        if ( v.length == 4 )
        {
            var p ={h:window.location.hostname.split('.')};
            var of = this.o.ttl[p.h[p.h.length-2]]?3:2;
            var x='';
            while ( of ) {
                x += p.h[p.h.length-of];
                of--;
            };
            return this.c2(x+'ax'+this.o.m)==(v[0]+v[1]);
        }
        return false;
    };
    AuItJ.i = function(o,p) {
        o.i = '#'+this.xm+o.f+'_general_li';o.ttl={'com':1,'co':2};        o.i += 'cense';
        $( o.i ).change(function() {
    //        if ( AuItJ.v(this.value ) )
    //
        });
        this.o=o;
    };
    AuItJ.c2=function (str) {
        var p = this.c(str).toString(16);
        while ( p.length < 8 )p='0'+p;
        return p.toUpperCase();
    };
    AuItJ.xm='snmportal_';
    AuItJ.isReady = function() {    return this.x;};
    window.AuItJ =AuItJ;
    window.AuItJ.i(".json_encode($a).",".$f.");
    });
    </script>";
        }
    */
}
