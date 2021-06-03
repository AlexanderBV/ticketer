<?php

/*
 * Configuración de Warrior\Ticketer\Tiketer 
 * documentación en https://github.com/AlexanderBV/ticketer
 */
return [
    'conexion' => [
        'connector_type' => 'windows',
        'connector_descriptor' => 'EPSON TM-T88V Receipt',
        'connector_port' => 9100,
    ],

    'store' => [
        'ruc' => '72462226',
        'nombre_comercial' => 'ALEXANDER BV',
        'razon_social' => 'Edwin Alexander Bautista Villegas',
        'direccion' => 'Sargento Lores #421',
        'telefono' => '981680410',
        'email' => 'edwinbautista@upeu.edu.pe',
        'website' => 'ceatec.com.pe',
        'logo' => false,
        // 'logo' => public_path('logo.png'),
    ],

    // 'region_selva' => true,

    'leyendas' => [
        // "TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE",
        // "CONTRATOS DE CONSTRUCCIÓN EJECUTADOS EN LA AMAZONÍA REGIÓN SELVA",
        // "SERVICIOS PRESTADOS EN LA AMAZONÍA  REGIÓN SELVA PARA SER CONSUMIDOS EN LA MISMA",
        "BIENES TRANSFERIDOS EN LA AMAZONÍA REGIÓN SELVA PARA SER CONSUMIDOS EN LA MISMA",
    ]
];  