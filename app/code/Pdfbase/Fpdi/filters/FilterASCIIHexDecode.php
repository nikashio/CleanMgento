<?php
namespace Snmportal\Pdfbase\Fpdi;

/**
 * This file is part of FPDI
 *
 * @package   FPDI
 * @copyright Copyright (c) 2015 Setasign - Jan Slabon (http://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 * @version   1.6.1
 */

/**
 * Class FilterASCIIHexDecode
 */
/*
class FilterASCIIHexDecode
{
    public function decode($data)
    {
        $data = preg_replace('/[^0-9A-Fa-f]/', '', rtrim($data, '>'));
        if ((strlen($data) % 2) == 1) {
            $data .= '0';
        }

        return pack('H*', $data);
    }
    public function encode($data, $leaveEOD = false)
    {
        return current(unpack('H*', $data)) . ($leaveEOD ? '' : '>');
    }
}
    */
