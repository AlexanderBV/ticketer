<?php

/*
 * Configuración de Warrior\Ticketer\Tiketer 
 * documentación en https://github.com/AlexanderBV/ticketer
 */
return [

    /**
     * Configuracion para la conexuon por defecto con la impresora termica.
     */
    'conexion' => [
        /**
         * 'windows' si está utilizando Windows como servidor web.
         * 'cups' si está utilizando Linux o Mac como servidor web.
         * 'network' si está utilizando una impresora de red.
         * 'dummy' si el usuario debe recuperar los datos almacenados en búfer. Usado para apis.
         */
        'connector_type' => 'windows',
        /**
         * El nombre de la impresora si su connector_type es windows o cups.
         * La dirección IP o URI de Samba, por ejemplo: smb://192.168.0.5/PrinterName si su connector_type es network.
         * No es necesario especificar connector_descriptor si su connector_type es dumm
         */
        'connector_descriptor' => 'EPSON TM-T88V Receipt',
        /**
         * Si su connector_type es network el puerto abierto de la impresora.
         */
        'connector_port' => 9100,
    ],


    /**
     * Configure la cabecera de su comprobantes con los datos de su empresa
     */
    'store' => [
        /**
         * ruc: Número de registro único de contribuyente de la tienda ó empresa.
         */
        'ruc' => '00000000000',

        /**
         * Nombre comercial de la tienda ó empresa
         */
        'nombre_comercial' => 'MI TIENDITA',

        /**
         *  Razón social de la tienda ó empresa.
         */
        'razon_social' => 'DE: PINEDO IZUIZA DEYBITH',

        /**
         * Direción de tienda ó empresa.
         */
        'direccion' => 'URB. FONAVI MANZANA B LOTE 7',

        /**
         * Teléfono de la tienda ó empresa.
         */
        'telefono' => '(048) 642736',

        /**
         * Correo electrónico de la tienda ó empresa.
         */
        'email' => '',

        /**
         * Sitio web de la tienda o empresa (donde el cliente prodra consultar su comprobante).
         */
        'website' => 'kentakito.ceatec.com',

        /**
         * Path del logo de la tienda, sino posee logo se debe especificar en false y se tomara el nombre comercial como logo principal de la cabecera. 
         * Se recomienda usar las dimenciones de 300x120 en pixeles, y de preferencia imagen en blanco y negro.
         */
        'logo' => false,
        // 'logo' => public_path('logo.png'),
    ],

    /**
     * Si la venta en us totalidad es una transferencia gratuta, configure su leyenda
     */
    'leyenda_transferencia_total_gratuita' => '*** TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE ***',

    /**
     * Configure las leyendas que se imprimiran aL final del comprobante
     */
    'leyendas' => [
        
        // "CONTRATOS DE CONSTRUCCIÓN EJECUTADOS EN LA AMAZONÍA REGIÓN SELVA",
        // "SERVICIOS PRESTADOS EN LA AMAZONÍA  REGIÓN SELVA PARA SER CONSUMIDOS EN LA MISMA",
        "BIENES TRANSFERIDOS EN LA AMAZONÍA REGIÓN SELVA PARA SER CONSUMIDOS EN LA MISMA",
        // "GRACIAS POR SU COMPRA, FELICES FIESTAS PATRIAS".
        // "GRACIAS POR SU COMPRA, FELIZ NAVIDAD".
    ]
];  