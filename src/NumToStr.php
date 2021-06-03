<?php

namespace Warrior\Ticketer;

use ParseError;

class NumToStr
{
    /**
     * @var array
     */
    private $unidades = [
        '',
        'UNO',
        'DOS',
        'TRES',
        'CUATRO',
        'CINCO',
        'SEIS',
        'SIETE',
        'OCHO',
        'NUEVE',
        'DIEZ',
        'ONCE',
        'DOCE',
        'TRECE',
        'CATORCE',
        'QUINCE',
        'DIECISÉIS',
        'DIECISIETE',
        'DIECIOCHO',
        'DIECINUEVE',
        'VEINTE',
        'VEINTIUNO',
        'VEINTIDÓS',
        'VEINTITRÉS',
        'VEINTICUATRO',
        'VEINTICINCO',
        'VEINTISÉIS',
    ];

    /**
     * @var array
     */
    private $decenas = [
        '',
        'DIEZ',
        'VEINTE',
        'TREINTA ',
        'CUARENTA ',
        'CINCUENTA ',
        'SESENTA ',
        'SETENTA ',
        'OCHENTA ',
        'NOVENTA ',
    ];

    /**
     * @var array
     */
    private $centenas = [
        '',
        'CIENTO',
        'DOSCIENTOS',
        'TRESCIENTOS',
        'CUATROCIENTOS',
        'QUINIENTOS',
        'SEISCIENTOS',
        'SETECIENTOS',
        'OCHOCIENTOS',
        'NOVECIENTOS',
    ];

    /**
     * @var array
     */
    private $acentosExcepciones = [
        'VEINTIDOS'  => 'VEINTIDÓS ',
        'VEINTITRES' => 'VEINTITRÉS ',
        'VEINTISEIS' => 'VEINTISÉIS ',
    ];

    /**
     * @var string
     */
    public $conector = 'CON';


    /**
     * Convierte numero de a letras
     *
     * @param int|double $num
     *
     * @return string
     */
    public function number($num)
    {

        if (($num < 0) || ($num > 999999999)) {
            // throw new Exception('Wrong parameter number');
            throw new ParseError('Wrong parameter number');
        }

        $converted = '';

        $numberStrFill = str_pad($num, 9, '0', STR_PAD_LEFT);
        $millones = substr($numberStrFill, 0, 3);
        $miles = substr($numberStrFill, 3, 3);
        $cientos = substr($numberStrFill, 6);
        
        if (intval($millones) > 0) {
            $converted .= ($millones == '001') ? 'UN MILLON ' : sprintf('%s MILLONES ', $this->convert($millones));
        }

        if (intval($miles) > 0) {
            $converted .= ($miles == '001') ? 'MIL ': sprintf('%s MIL ', $this->convert($miles));
        }

        if (intval($cientos) > 0) {
            $converted .= ($cientos == '001') ? 'UNO ' : sprintf('%s ', $this->convert($cientos));
        }

        return trim($converted);
    }

    /**
     * Convierte numero de 3 cifras a letras
     *
     * @param int $num
     *
     * @return string
     */
    public function convert($num)
    {
        $num = strval($num);
        
        $u = intval($num[2]);
        $d = intval($num[1]);
        $c = intval($num[0]);

        $unidades_name = (intval("$d$u") > 26) ? $this->unidades[$u] : ''; // 12
        $decenas_name  = (intval("$d$u") > 26) ? $this->decenas[$d] . (($u > 0 && $d > 0) ? ' Y ': '') : $this->unidades[intval("$d$u")];
        $centenas_name = ($c == 1 && $d == 0 && $u == 0) ? 'CIEN' : $this->centenas[$c];

        return "$centenas_name $decenas_name$unidades_name";
    }

    /**
     * Concatena las partes formateadas del número convertido.
     *
     * @param array $splitNumber
     *
     * @return string
     */
    private function glue($splitNumber)
    {
        return implode(' ' . mb_strtoupper($this->conector, 'UTF-8') . ' ', array_filter($splitNumber));
    }

    /**
     * Formatea y convierte un número a letras.
     *
     * @param int|float $number
     * @param int       $decimals
     *
     * @return string
     */
    public function toWords($number, $decimals = 2)
    {

        $number = number_format($number, $decimals, '.', '');

        $splitNumber = explode('.', $number);

        $splitNumber[0] = $this->number($splitNumber[0]);

        if (!empty($splitNumber[1])) {
            $splitNumber[1] = $this->number($splitNumber[1]);
        }

        return $this->glue($splitNumber);
    }

    /**
     * Formatea y convierte un número a letras en formato moneda.
     *
     * @param int|float $number
     * @param int       $decimals
     * @param string    $currency
     * @param string    $cents
     *
     * @return string
     */
    public function toMoney($number, $decimals = 2, $currency = '', $cents = '')
    {

        $number = number_format($number, $decimals, '.', '');

        $splitNumber = explode('.', $number);

        $splitNumber[0] = $this->number($splitNumber[0]) . ' ' . mb_strtoupper($currency, 'UTF-8');

        if (!empty($splitNumber[1])) {
            $splitNumber[1] = $this->number($splitNumber[1]);
        }

        if (!empty($splitNumber[1])) {
            $splitNumber[1] .= ' ' . mb_strtoupper($cents, 'UTF-8');
        }

        return $this->glue($splitNumber);
    }

    /**
     * Formatea y convierte un número a letras en formato facturación electrónica.
     *
     * @param int|float $number
     * @param int       $decimals
     * @param string    $currency
     *
     * @return string
     */
    public function toInvoice($number, $decimals = 2, $currency = '')
    {

        $number = number_format($number, $decimals, '.', '');

        $splitNumber = explode('.', $number);

        $splitNumber[0] = $this->number($splitNumber[0]);

        if (!empty($splitNumber[1])) {
            $splitNumber[1] .= '/100 ';
        } else {
            $splitNumber[1] = '00/100 ';
        }

        return $this->glue($splitNumber) . mb_strtoupper($currency, 'UTF-8');
    }
}
