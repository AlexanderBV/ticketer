<?php

namespace Warrior\Ticketer;

class Item
{
    /**
     * @var string
     */
    private $nombre;

    /**
     * @var int
     */
    private $cantidad;

    /**
     * @var double|int
     */
    private $precio;

    /**
     * @var boolean
     */
    private $icbper;

    /**
     * @var boolean
     */
    private $gratuita;

    function __construct($nombre, $cantidad, $precio, $icbper,$gratuita) {
        $this->nombre   = $nombre;
        $this->cantidad = $cantidad;
        $this->precio   = $precio; 
        $this->icbper   = filter_var($icbper, FILTER_VALIDATE_BOOLEAN); 
        $this->gratuita = filter_var($gratuita, FILTER_VALIDATE_BOOLEAN); 
    }

    /**
     * @param string $nombre Nombre o descripción del producto
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * @param double|int $cantidad Cantidad del producto
     */
    public function setcantidad($cantidad)
    {
        $this->cantidad = $cantidad;
    }

    public function getcantidad()
    {
        return $this->cantidad;
    }

    /**
     * @param double $precio Precio del producto
     */
    public function setPrecio($precio)
    {
        $this->precio = $precio;
    }

    public function getPrecio()
    {
        return $this->precio;
    }

    /**
     * @param boolean $icbper Si el producto contiene inpuesto ICBPER
     */
    public function setIcbper($icbper)
    {
        $this->icbper = filter_var($icbper , FILTER_VALIDATE_BOOLEAN);
    }

    public function getIcbper()
    {
        return $this->icbper;
    }

    /**
     * @param boolean $gratuita Si el producto es una transferencia gratuita.
     */
    public function setGratuita($gratuita)
    {
        $this->gratuita = filter_var($gratuita , FILTER_VALIDATE_BOOLEAN);
    }

    public function getGratuita()
    {
        return $this->gratuita;
    }

   
    public function __toString()
    {

        $producto = $this->gratuita ? $this->nombre . " (TRANSFERENCIA GRATUITA)" : $this->nombre;
        
        $precio   = number_format($this->precio, 2, '.', '');
        $subtotal = number_format($this->precio * $this->cantidad, 2, '.', '');
        
        $print_producto = str_pad($producto, 0);
        $print_cantidad = str_pad($this->cantidad, 22);
        $print_precio   = str_pad($precio  , 10, ' ', STR_PAD_LEFT);
        $print_subtotal = str_pad($subtotal, 10, ' ', STR_PAD_LEFT);

        return "$print_producto\n$print_cantidad$print_precio$print_subtotal\n";
    }
}
