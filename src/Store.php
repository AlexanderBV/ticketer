<?php

namespace Warrior\Ticketer;

class Store
{
    private $nombreComercial;
    private $razonSocial;
    private $direccion;
    private $ruc;
    private $telefono;
    private $email;
    private $website;
    private $logo;
   
    function __construct() {
    }

    public function setNombreComercial($nombreComercial) {
        $this->nombreComercial = $nombreComercial;
    }

    public function getNombreComercial() {
        return $this->nombreComercial;
    }

    public function setRazonSocial($razonSocial) {
        $this->razonSocial = $razonSocial;
    }

    public function getRazonSocial() {
        return $this->razonSocial;
    }

    public function setDireccion($direccion) {
        $this->direccion = $direccion;
    }

    public function getDireccion() {
        return $this->direccion;
    }

    public function setRuc($ruc) {
        $this->ruc = $ruc;
    }

    public function getRuc() {
        return $this->ruc;
    }

    public function setTelefono($telefono) {
        $this->telefono = $telefono;
    }

    public function getTelefono() {
        return $this->telefono;
    }
    
    public function setEmail($email) {
        $this->email = $email;
    }

    public function getEmail() {
        return $this->email;
    }
    
    public function setWebsite($website) {
        $this->website = $website;
    } 

    public function getWebsite() {
        return $this->website;
    } 
    
    public function setLogo($logo) {
        $this->logo = $logo;
    } 

    public function getLogo() {
        return $this->logo;
    } 
    
}
