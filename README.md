# Imprimir comprobantes en impresora termica con Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/warrior/ticketer.svg?style=flat-square)](https://packagist.org/packages/warrior/ticketer)
[![Total Downloads](https://img.shields.io/packagist/dt/warrior/ticketer.svg?style=flat-square)](https://packagist.org/packages/warrior/ticketer)
![GitHub Actions](https://github.com/AlexanderBV/ticketer/actions/workflows/php.yml/badge.svg)

Paquete Laravel simple para integrar ESC/POS Print Driver para PHP y dar estructura de comprobantes segun [Normativa Sunat](https://www.sunat.gob.pe/legislacion/superin/2019/206-2019.pdf).
- Comprobantes: Boleta y Factura
- Tickets: Cocina, Avance de cuenta *(proximamete)*.

## Instalación

Puedes instalar el paquete a través de composer:

```bash
composer require warrior/ticketer
```

## Uso

Ejecute el siguiente comando para publicar la configuración utilizada por este paquete:

```bash
php artisan vendor:publish --provider="Warrior\Ticketer\TicketerServiceProvider" --tag="config"
```
Opcional: el  service provider se registrará automáticamente. O puede agregar manualmente el proveedor de servicios en su archivo config / app.php:

```php
'providers' => [
    // ...
    Warrior\Ticketer\TicketerServiceProvider::class,
];
```
#### Configuracíon previa

Edite el archivo de configuración ubicado en `config / ticketer.php` de la siguiente manera:

- Configure `conexion` en:
  - `connector_type`:
    - `windows` si está utilizando Windows como servidor web.
    - `cups` si está utilizando Linux o Mac como servidor web.
    - `network` si está utilizando una impresora de red.
    - `dummy` si el usuario debe recuperar los datos almacenados en búfer. Usado para apis.
  - `connector_descriptor`:
    - El nombre de la impresora si su `connector_type` es `windows` o `cups`.
    - La dirección IP o URI de Samba, por ejemplo: `smb://192.168.0.5/PrinterName` si su `connector_type` es `network`.
    - No es necesario especificar `connector_descriptor` si su `connector_type` es `dummy`.
  - `connector_port`:
    - Si su` connector_type` es `network` el puerto abierto de la impresora.
  - Más información en [mike42/escpos-php](https://github.com/mike42/escpos-php)
    
- Configure `store` para la cabecera de sus comprobantes:
  - `ruc`: Número de registro único de contribuyente de la tienda ó empresa.
  - `nombre_comercial`: Nombre comercial de la tienda ó empresa.
  - `razon_social`: Razón social de la tienda ó empresa.
  - `direccion`: Direción de tienda ó empresa.
  - `telefono`: Teléfono de la tienda ó empresa.
  - `email`:Correo electrónico de la tienda ó empresa.
  - `website`: Sitio web de la tienda o empresa *(donde el cliente prodra consultar su comprobante)*.
  - `logo`: Path del logo de la tienda, sino posee logo se debe especificar en `false` y se tomara el nombre comercial como logo principal de la cabecera. Se recomienda usar las dimenciones de 300x120 en pixeles, y de preferencia imagen en blanco y negro.

- Configure `leyendas` para el final de sus comprobantes:
  - `CONTRATOS DE CONSTRUCCIÓN EJECUTADOS EN LA AMAZONÍA REGIÓN SELVA`.
  - `SERVICIOS PRESTADOS EN LA AMAZONÍA  REGIÓN SELVA PARA SER CONSUMIDOS EN LA MISMA`.
  - `BIENES TRANSFERIDOS EN LA AMAZONÍA REGIÓN SELVA PARA SER CONSUMIDOS EN LA MISMA`.
  - Cualquier otra leyenda que se requiera.

## Ejempo de Imprimir comporbante

```php
use Warrior\Ticketer\Ticketer;
...
```

```php
$now = Carbon::now();
$ticketer = new Ticketer();
$ticketer->init('windows', 'EPSON TM-T88V Receipt');
$ticketer->setFechaEmision($now);
$ticketer->setComprobante('BOLETA');
$ticketer->setSerieComprobante('B001');
$ticketer->setNumeroComprobante('000000100');
$ticketer->seCodigoComprobante('01');
$ticketer->setCliente('Edwin Alexander Bautista Villegas');
$ticketer->setTipoDocumento(1);
$ticketer->setNumeroDocumento('72462226');
$ticketer->setCodigoDocumento('01');
$ticketer->setDireccion('Jr. Enarte Torres 421 - Santa Lucia');
$ticketer->setTipoDetalle('DETALLADO');

// $nombre, $cantidad, $precio, $icbper, $gratuita
$ticketer->addItem("POLLO A LA BRASA", 2, 21.5, false, false);
// $ticketer->addItem("ENSALADA RUSA", 3, 12, false, false);
// $ticketer->addItem("POLLO A LA BRASA", 4, 2 , false, false);
// $ticketer->addItem("AGUA MINERAL", 1, 2 , false, true);
// $ticketer->addItem("BOLSA PLASTICA", 1, 0.2 , true, false);

// Retornara true al mandar la impresión
$ticketer->printComprobante();
// Si quiere obtener los datos de impresion en base64
// util para trabajar con APIS web
// return $ticketer->printComprobante(true);
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email edwinbautista@upeu.edu.pe instead of using the issue tracker.

## Credits

-   [Alexander BV](https://github.com/AlexanderBV)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
