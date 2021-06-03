<?php

namespace Warrior\Ticketer;


use Exception;

use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\DummyPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\Printer;


class Ticketer
{
    /**
     * @var Printer ESC/POS 
     */
    private $printer;

    /**
     * @var CupsPrintConnector|WindowsPrintConnector|NetworkPrintConnector|DummyPrintConnector|FilePrintConnector
     */
    private $connector;

    /**
     * @var CapabilityProfile
     */
    private $profile;

    /**
     * @var Store
     */
    private $store;

    /**
     * @var Carbon
     */
    private $fecha_emision;

    private $comprobante;
    private $serie_comprobante;
    private $numero_comprobante;
    private $codigo_comprobante;

    private $cliente;
    private $tipo_documento;
    private $numero_documento;
    private $codigo_documento;
    private $direccion;

    private $tipo_detalle;

    private $items;

    private $monto_icbper = 0;

    private $subtotal  = 0;
    private $descuento = 0;
    private $inafecta  = 0;
    private $exonerada = 0;
    private $igv       = 0;
    private $icbper    = 0;
    private $total     = 0;

    private $efectivo;
    private $vuelto;

    private $QR;

    /** PARA MESA  **/

    private $mozo;
    private $ambiente;

    private $data;

    public function __construct() {
        $this->printer = null;
        $this->items = [];
    }

    /**
     * Establecer conexion a la impresora ESC/POS
     * 
     * @param string $connector_type Tipo de conexion a la impresora
     * @param string $connector_descriptor Nombre de la impresora
     * @param string|int $connector_port Puerto de la impresora
     * 
     * @throws Exception Si los parametros de conexión son inválidos
     */
    public function init($connector_type = null, $connector_descriptor = null,  $connector_port = 9100)
    {

        $connector_type = ($connector_type) ? $connector_type :config('ticketer.conexion.connector_type');
        $connector_descriptor = ($connector_descriptor) ? $connector_descriptor :config('ticketer.conexion.connector_descriptor');

        switch (strtolower($connector_type)) {
            case 'windows':
                $this->connector = new WindowsPrintConnector($connector_descriptor);
                break;
            case 'cups':
                $this->connector = new CupsPrintConnector($connector_descriptor);
                break;
            case 'network':
                $this->connector = new NetworkPrintConnector($connector_descriptor);
                break;
            case 'dummy':
                $this->connector = new DummyPrintConnector();
                break;
            default:
                $this->connector = new FilePrintConnector("php://stdout");
                break;
        }

        if ($this->connector) {
            $this->profile = CapabilityProfile::load("default");

            $this->printer = new Printer($this->connector, $this->profile);
        } else {
            throw new Exception('Tipo de conector de impresora no válido. Los valores aceptados son: tazas');
        }
    }
    
    /**
     * @param Store $store Datos de la empresa
     */
    public function setStore($store) {

        $this->store = $store;
    }

    /**
     * @param string $fecha_emision Fecha que se ha emitido el comprobante
     * @example 2021-05-29 14:10:41
     */
    public function setFechaEmision($fecha_emision)
    {
        $this->fecha_emision = $fecha_emision;
    }

    /**
     * @param string $comprobante Nombre del comprobante 
     * @example BOLETA|FACTURA|BOLETA-LIBRE
     */
    public function setComprobante($comprobante)
    {
        $this->comprobante = $comprobante;
    }

    /**
     * @param string $serie_comprobante serie del comprobante
     * @example B001, F001, BL01
     */
    public function setSerieComprobante($serie_comprobante)
    {
        $this->serie_comprobante = $serie_comprobante;
    }

    /**
     * @param string $serie_comprobante serie del comprobante
     * @example 000000100, 000000101, 000000102
     */
    public function setNumeroComprobante($numero_comprobante)
    {
        $this->numero_comprobante = $numero_comprobante;
    }

    public function seCodigoComprobante($codigo_comprobante)
    {
        $this->codigo_comprobante = $codigo_comprobante;
    }

    /**
     * @param string $cliente Nombre del cliente
     * @example CeatecSoft E.I.R.L , Edwin Alexander Bautista Villegas
     */
    public function setCliente($cliente)
    {
        $this->cliente = $cliente;
    }

    /**
     * @param string $tipo_documento Tipo de documento de identificación
     * @example 1 = (RUC), 2 = (DNI)
     */
    public function setTipoDocumento($tipo_documento)
    {
        $this->tipo_documento = $tipo_documento;
    }

    /**
     * @param string $numero_documento Número del documento de identificación
     * @example 72462226, 20542332990 
     */
    public function setNumeroDocumento($numero_documento)
    {
        $this->numero_documento = $numero_documento;
    }

    /**
     * @param string $codigo_documento Código del documento,
     * @example 00, 01, 06. Para: Nulo, DNI, RUC
     */
    public function setCodigoDocumento($codigo_documento)
    {
        $this->codigo_documento = $codigo_documento;
    }

    /**
     * @param string $direccion Dirección del comprobante
     * @example Jr. Enarte Torres 421 - Santa Lucia
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;
    }

    /**
     * @param string $tipo_detalle Tipo de detalle del comprobante
     * @example DETALLADO, CONSUMO
     */
    public function setTipoDetalle($tipo_detalle)
    {
        $this->tipo_detalle = $tipo_detalle;
    }

    /**
     * Agregar lista detallada de los productos
     * 
     * @param string $nombre Nombre del producto
     * @param int|double $cantidad Cantidad del producto
     * @param int|double $precio Precio del productp
     * @param boolean|string $icbper ICBPER si el producto aplica inpuesto
     * @param boolean|string $gratuita Transferencia gratuita si aplica
     */
    public function addItem($nombre = '-', $cantidad = 0, $precio = 0, $icbper = false, $gratuita = false, $item = true) {

        if ($item) {
            $this->items[] = new Item($nombre, $cantidad, $precio, $icbper, $gratuita);
        }else{
            $this->items[] =  $this->next("[{$cantidad}] {$nombre}");
        }
    }

    public function setMontoIcbper($monto_icbper)
    {
        $this->monto_icbper = $monto_icbper;
    }


    public function setDescuento($descuento)
    {
        $this->descuento = $descuento;
    }

    public function setInafecta($inafecta)
    {
        $this->inafecta = $inafecta;
    }

    public function setExonerada($exonerada)
    {
        $this->exonerada = $exonerada;
    }

    public function setIgv($igv)
    {
        $this->igv = $igv;
    }

    public function setCodeQr($QR) {
        $this->QR = $QR;
    }

    public function setMozo($mozo)
    {
        $this->mozo = $mozo;
    }

    public function setAmbiente($ambiente)
    {
        $this->ambiente = $ambiente;
    }

    public function calcularTotales()
    {
        $this->subtotal = 0;
        $this->icbper = 0;
        $this->total = 0;

        $this->monto_icbper = ($this->monto_icbper == 0) ? $this->year_icbper() : $this->monto_icbper;

        foreach ($this->items as $item) {

            // Calcular sub total siempre y cuando NO sea una transferencia gratuita
            if (!$item->getGratuita()) {
                $this->subtotal += $item->getcantidad() * $item->getPrecio();
            }

            // Calcular el ICBPER siempre y cuando el producto SI aplique para impuesto al consumo de las bolsas de plástico
            if ($item->getIcbper()) {
                $this->icbper += $item->getcantidad() * $this->monto_icbper;
            }

        }

        $this->total = ($this->subtotal - $this->descuento) + $this->exonerada + $this->inafecta + $this->igv + $this->icbper;
        $this->vuelto = $this->efectivo - $this->total;
    }

    /**
     * @param boolean $base64 En caso desee retornar datos de la impresión codificados base 64
     * @param boolean $cut En caso desee cortar el ticket, sirve para acudo se imprime una lista de comprobantes
     * @return boolean|string $base64_data cuando se usa la codificación
     * @throws Exception Cuando los parametros de conexión no se han establecido o no se han encontrado
     */
    public function printComprobante($base64 = false, $cut = true)
    {
        if ($this->printer) {
           
            $this->printer->initialize();
            $this->printer->setPrintLeftMargin(1);

            // Si no hay datos de la tienda tomanos por defecto los de config/ticket.php 
            if (empty($this->store)) {

                $store = new Store();
                $store->setRuc(config('ticketer.store.ruc'));
                $store->setNombreComercial(config('ticketer.store.nombre_comercial'));
                $store->setRazonSocial(config('ticketer.store.razon_social'));
                $store->setDireccion(config('ticketer.store.direccion'));
                $store->setTelefono(config('ticketer.store.telefono'));
                $store->setEmail(config('ticketer.store.email'));
                $store->setWebsite(config('ticketer.store.website'));
                $store->setLogo(config('ticketer.store.logo'));

                $this->setStore($store);

            }

            // HEADER
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);

            if ($this->store->getLogo()) {
                $image = EscposImage::load($this->store->getLogo(), false);
                $this->printer->graphics($image);//
                
            }else{
                $this->printer->text($this->next($this->store->getNombreComercial()));
            }

            $this->printer->selectPrintMode();
            $this->printer->text($this->next($this->store->getRazonSocial()));
            $this->printer->selectPrintMode(Printer::MODE_FONT_B);
            $this->printer->text($this->next($this->store->getDireccion()));
            $this->printer->text($this->next('RUC:'. $this->store->getRuc()));


            //TIPO Y NRO DE DOCUEMNTO 
            $this->printer->selectPrintMode();// text normal
            $this->printer->text($this->line());
            $this->printer->setEmphasis(true);
            $this->printer->text($this->next($this->comprobante . " ". $this->serie_comprobante . '-' . $this->numero_comprobante));
            $this->printer->setEmphasis(false);
            $this->printer->text($this->line());

            //DATOS CLIENTE
            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->selectPrintMode();// text normal
            $this->printer->text($this->next('FECHA: ' . $this->fecha_emision));
            $this->printer->text($this->next('NRO.DOC: ' . $this->numero_documento));
            $this->printer->text($this->next('CLIENTE: ' . $this->cliente));
            $this->printer->text($this->next('DIRECCION: ' . $this->direccion));
            $this->printer->feed();

            // DETALLE DEL PEDIDO
            $this->printer->setEmphasis(true);
            $this->printer->text("DESCRIPCION\n");
            $this->printer->text("CANTIDAD                   PRECIO  IMPORTE\n");
            $this->printer->text($this->line());
            $this->printer->setEmphasis(false);
            $this->printer->selectPrintMode();// text normal

            $this->printer->setJustification(Printer::JUSTIFY_LEFT);

            if (strtoupper($this->tipo_detalle) === 'DETALLADO') {
                foreach ($this->items as $item) {
                    $this->printer->text($item);
                }
            }else if(strtoupper($this->tipo_detalle) === 'CONSUMO'){
                $this->printer->text(str_pad('POR CONSUMO', 32) . str_pad($this->formatter_num($this->subtotal)  , 10, ' ', STR_PAD_LEFT));
            }

            $this->calcularTotales();

            $this->printer->text($this->line());
            $this->printer->text($this->next(str_pad('SUB TOTAL   S/ ', 32, ' ', STR_PAD_LEFT) . str_pad($this->formatter_num($this->subtotal) , 10, ' ', STR_PAD_LEFT)) );
            $this->printer->text($this->next(str_pad('DESCUENTO   S/ ', 32, ' ', STR_PAD_LEFT) . str_pad($this->formatter_num($this->exonerada), 10, ' ', STR_PAD_LEFT)) );
            $this->printer->text($this->next(str_pad('INAFECTA   S/ ' , 32, ' ', STR_PAD_LEFT) . str_pad($this->formatter_num($this->inafecta) , 10, ' ', STR_PAD_LEFT)) );
            $this->printer->text($this->next(str_pad('IGV (18%)   S/ ', 32, ' ', STR_PAD_LEFT) . str_pad($this->formatter_num($this->igv)      , 10, ' ', STR_PAD_LEFT)) );
            $this->printer->text($this->next(str_pad('ICBPER   S/ '   , 32, ' ', STR_PAD_LEFT) . str_pad($this->formatter_num($this->icbper)   , 10, ' ', STR_PAD_LEFT)) );
            $this->printer->setEmphasis(true);
            $this->printer->text($this->next(str_pad('**** TOTAL   S/ ', 32, ' ', STR_PAD_LEFT). str_pad($this->formatter_num($this->total), 10, ' ', STR_PAD_LEFT)) ); 
            $this->printer->setEmphasis(false);
            
            $this->printer->text($this->line());
            $total_formatter = (new NumToStr())->toInvoice($this->formatter_num($this->total), 2, 'SOLES');
            $this->printer->text("SON: {$total_formatter}\n");
            $this->printer->text($this->line());

            $this->printer->text($this->next(str_pad('EFECTIVO   S/ ', 32, ' ', STR_PAD_LEFT) . str_pad($this->formatter_num($this->efectivo), 10, ' ', STR_PAD_LEFT)) );
            $this->printer->text($this->next(str_pad('VUELTO   S/ '  , 32, ' ', STR_PAD_LEFT) . str_pad($this->formatter_num($this->vuelto)  , 10, ' ', STR_PAD_LEFT)) );
            $this->printer->text($this->line());

            $this->printer->setJustification(Printer::JUSTIFY_CENTER);

            $QR = $this->store->getRuc()   . '|' 
                . $this->codigo_comprobante . '|' 
                . $this->serie_comprobante  . '|' 
                . $this->numero_comprobante . '|' 
                . $this->formatter_num($this->igv)   . '|' 
                . $this->formatter_num($this->total) . '|' 
                . $this->fecha_emision->format('Y-m-d') . '|'
                . $this->codigo_documento . '|'
                . $this->numero_documento;

            $this->setCodeQr($QR);

            if ($this->comprobante == 'BOLETA' || $this->comprobante == 'FACTURA') {
                $this->printer->qrCode($this->QR, Printer::QR_ECLEVEL_L, 4);
                $this->printer->selectPrintMode(Printer::MODE_FONT_B);
            
                $this->printer->text("REPRESENTACION IMPRESA DE LA $this->comprobante \n");
                $this->printer->text("PUEDE CONSULTAR EN: ");
                $this->printer->setEmphasis(true);//bolt
                $this->printer->text("{$this->store->getWebsite()}\n");
                $this->printer->setEmphasis(false);//bolt
            }

            if (config('ticketer.leyendas')) {
                foreach (config('ticketer.leyendas') as $key => $leyenda) {
                    $this->printer->feed();//bolt
                    $this->printer->text($this->next($leyenda));
                    
                }
            }

            if ($cut) {
                $this->printer->cut();
            }

            // Se solicita codificacion en base 64? SI: convertir data; NO: retornar true
            // Importane pedir el data sin antes cerrar la conexion
            if ($base64) {
                $this->data = base64_encode($this->connector->getData());
            }else{
                $this->data = true;
            }

            $this->printer->close();
            
            return $this->data;

        } else {
            throw new Exception('Printer no ha sido inicializado.');
        }
    }

    /**
     * @param boolean $base64 En caso desee retornar datos de la impresión codificados base 64
     * @param boolean $cut En caso desee cortar el ticket, sirve para acudo se imprime una lista de avances de cuenta
     * @return boolean|string $base64_data cuando se usa la codificación
     * @throws Exception Cuando los parametros de conexión no se han establecido o no se han encontrado
     */
    public function printAvance($base64 = false, $cut = true)
    {
        if ($this->printer) {
           
            $this->printer->initialize();
            $this->printer->setPrintLeftMargin(1);

            // Si no hay datos de la tienda tomanos por defecto los de config/ticket.php 
            if (empty($this->store)) {

                $store = new Store();
                $store->setRuc(config('ticketer.store.ruc'));
                $store->setNombreComercial(config('ticketer.store.nombre_comercial'));
                $store->setRazonSocial(config('ticketer.store.razon_social'));
                $store->setDireccion(config('ticketer.store.direccion'));
                $store->setTelefono(config('ticketer.store.telefono'));
                $store->setEmail(config('ticketer.store.email'));
                $store->setWebsite(config('ticketer.store.website'));
                $store->setLogo(config('ticketer.store.logo'));

                $this->setStore($store);

            }

            // HEADER
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);

            if ($this->store->getLogo()) {
                $image = EscposImage::load($this->store->getLogo(), false);
                $this->printer->graphics($image);//
                
            }else{
                $this->printer->text($this->next($this->store->getNombreComercial()));
            }

            //TIPO Y NRO DE DOCUEMNTO 
            $this->printer->selectPrintMode();// text normal
            $this->printer->text($this->line());
            $this->printer->setEmphasis(true);
            $this->printer->text("AVANCE DE CUENTA\n");
            $this->printer->text($this->next($this->ambiente));
            
            // DETALLE DEL PEDIDO
            $this->printer->setEmphasis(true);
            $this->printer->text("DESCRIPCION\n");
            $this->printer->text("CANTIDAD                   PRECIO  IMPORTE\n");
            $this->printer->text($this->line());
            $this->printer->setEmphasis(false);
            $this->printer->selectPrintMode();// text normal

            $this->printer->setJustification(Printer::JUSTIFY_LEFT);

            foreach ($this->items as $item) {
                $this->printer->text($item);
            }

            $this->calcularTotales();

            $this->printer->text($this->line());
            $this->printer->text($this->next(str_pad('SUB TOTAL   S/ ', 32, ' ', STR_PAD_LEFT) . str_pad($this->formatter_num($this->subtotal) , 10, ' ', STR_PAD_LEFT)) );
            $this->printer->text($this->next(str_pad('DESCUENTO   S/ ', 32, ' ', STR_PAD_LEFT) . str_pad($this->formatter_num($this->exonerada), 10, ' ', STR_PAD_LEFT)) );
            $this->printer->text($this->next(str_pad('INAFECTA   S/ ' , 32, ' ', STR_PAD_LEFT) . str_pad($this->formatter_num($this->inafecta) , 10, ' ', STR_PAD_LEFT)) );
            $this->printer->text($this->next(str_pad('IGV (18%)   S/ ', 32, ' ', STR_PAD_LEFT) . str_pad($this->formatter_num($this->igv)      , 10, ' ', STR_PAD_LEFT)) );
            $this->printer->text($this->next(str_pad('ICBPER   S/ '   , 32, ' ', STR_PAD_LEFT) . str_pad($this->formatter_num($this->icbper)   , 10, ' ', STR_PAD_LEFT)) );
            $this->printer->setEmphasis(true);
            $this->printer->text($this->next(str_pad('**** TOTAL   S/ ', 32, ' ', STR_PAD_LEFT). str_pad($this->formatter_num($this->total), 10, ' ', STR_PAD_LEFT)) ); 
            $this->printer->setEmphasis(false);
            
            $this->printer->text($this->line());
            $total_formatter = (new NumToStr())->toInvoice($this->formatter_num($this->total), 2, 'SOLES');
            $this->printer->text("SON: {$total_formatter}\n");
            $this->printer->text($this->line());

            if ($this->mozo) {
                $this->printer->text('ATENDIDO POR: '. $this->next($this->mozo));
                $this->printer->text($this->line());
            }

            $this->printer->text("DNI/RUC:\n");
            $this->printer->text("NOMBRES/RAZON SOCIAL:\n\n");
            $this->printer->text("DIRECCION:\n\n");

            if ($cut) {
                $this->printer->cut();
            }

            // Se solicita codificacion en base 64? SI: convertir data; NO: retornar true
            // Importane pedir el data sin antes cerrar la conexion
            if ($base64) {
                $this->data = base64_encode($this->connector->getData());
            }else{
                $this->data = true;
            }

            $this->printer->close();
            
            return $this->data;

        } else {
            throw new Exception('Printer no ha sido inicializado.');
        }
    }

    /**
     * @param boolean $base64 En caso desee retornar datos de la impresión codificados base 64
     * @param boolean $cut En caso desee cortar el ticket, sirve para acudo se imprime una lista de tickets cocina
     * @return boolean|string $base64_data cuando se usa la codificación
     * @throws Exception Cuando los parametros de conexión no se han establecido o no se han encontrado
     */
    public function printCocina($base64 = false, $cut = true)
    {
        if ($this->printer) {
           
            $this->printer->initialize();
            $this->printer->setPrintLeftMargin(1);

            // HEADER
            $this->printer->setJustification(Printer::JUSTIFY_LEFT);

            $this->printer->selectPrintMode();
            $this->printer->setFont(Printer::FONT_B);
            $this->printer->setTextSize(2, 2);

            $this->printer->text($this->next($this->cliente));

            // $this->printer->text("[CANT] DESCRIPCION\n");
            $this->printer->text($this->line());

            foreach ($this->items as $item) {
                $this->printer->text($this->next($item));
            }

            $this->printer->setTextSize(1, 1);
            $this->printer->text($this->next($this->fecha_emision));
            $this->printer->text($this->next($this->ambiente));
            $this->printer->text($this->next($this->mozo));

            $this->printer->feed();

            if ($cut) {
                $this->printer->cut();
            }

            // Se solicita codificacion en base 64? SI: convertir data; NO: retornar true
            // Importane pedir el data sin antes cerrar la conexion
            if ($base64) {
                $this->data = base64_encode($this->connector->getData());
            }else{
                $this->data = true;
            }

            $this->printer->close();
            
            return $this->data;

        } else {
            throw new Exception('Printer no ha sido inicializado.');
        }
    }

    /**
     * El inpuesto a las bolsas plasticas según su monto
     * @param string|int $year Año del inpuesto
     * @return double 
     */
    public static function year_icbper($year = null)
    {
        $year = ($year) ? $year : date("Y");

        switch ((int)$year) {
            case 2020: return 0.2; break;
            case 2021: return 0.3; break;
            case 2022: return 0.4; break;
            case 2023: return 0.5; break;
            case 2024: return 0.6; break;
            case 2025: return 0.7; break;
            case 2026: return 0.8; break;
            case 2027: return 0.9; break;
            case 2028: return 1.0; break;
            default:   return 0.3; break;
        }
    }

    /**
     * @var double|int $numero
     * @return string Número de dos dígitos autocompletado 2 decimales
     */
    public static function formatter_num($numero)
    {
        return number_format($numero, 2, '.', '');
    }

    /**
     * @var string $texto
     * @return string Texto concatenado a salto de linea
     */
    public static function next($texto)
    {
        return "{$texto}\n";
    }

    /**
     * @return string Linea de guiones
     */
    public static function line()
    {
        return "------------------------------------------\n";
    }
}
