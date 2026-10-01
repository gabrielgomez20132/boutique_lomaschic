# CLAUDE.md

Este archivo proporciona orientación a Claude Code (claude.ai/code) al trabajar con código en este repositorio.

## Descripción del Proyecto

Control-Soft es un sistema de punto de venta (POS) y gestión de inventario construido con Laravel 8.0 y Vue.js. El sistema gestiona ventas de productos, inventario, facturación (con integración AFIP para Argentina), administración de clientes, devoluciones, vales y presupuestos.

## Stack Tecnológico

- **Backend**: Laravel 8.0 (PHP 7.4+)
- **Frontend**: Vue.js 2.6, jQuery, Bootstrap 3
- **Herramienta de Build**: Laravel Mix (wrapper de Webpack)
- **Base de Datos**: MySQL 5.7
- **Generación de PDF**: barryvdh/laravel-dompdf
- **Generación de Códigos de Barra**: picqer/php-barcode-generator
- **Integración AFIP**: Cliente SOAP personalizado para AFIP (WSAA/WSFEV1)

## Comandos de Desarrollo

### Configuración Inicial
```bash
# Instalar dependencias PHP
composer install

# Instalar dependencias JavaScript
npm install

# Copiar archivo de entorno (si no existe)
cp .env.example .env  # Nota: .env.example puede no existir, verificar .env existente

# Generar clave de aplicación
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# Poblar base de datos con datos iniciales
php artisan db:seed
```

### Flujo de Trabajo de Desarrollo
```bash
# Compilar assets para desarrollo
npm run dev

# Observar cambios y recompilar automáticamente
npm run watch

# Compilar para producción (minificado)
npm run production

# Ejecutar servidor de desarrollo (servidor PHP integrado)
php artisan serve

# Limpiar caché de aplicación
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Pruebas
```bash
# Ejecutar todas las pruebas
vendor/bin/phpunit

# Ejecutar suite de pruebas específica
vendor/bin/phpunit --testsuite=Feature
vendor/bin/phpunit --testsuite=Unit

# Ejecutar pruebas con cobertura
vendor/bin/phpunit --coverage-html coverage/
```

### Desarrollo con Docker
```bash
# Iniciar contenedores Docker
docker-compose up -d

# Aplicación disponible en: http://localhost:8085
# phpMyAdmin disponible en: http://localhost:8080
# Puerto MySQL: 33065

# Detener contenedores
docker-compose down

# Acceder al contenedor de la aplicación
docker exec -it laravel5_app bash

# Credenciales de base de datos (docker-compose.yml):
# - Base de datos: controlsoft
# - Usuario: laravel
# - Contraseña: 12345678
# - Contraseña root: root
```

### Gestión de Base de Datos
```bash
# Crear nueva migración
php artisan make:migration create_table_name

# Ejecutar migraciones frescas (ADVERTENCIA: destruye datos)
php artisan migrate:fresh

# Revertir última migración
php artisan migrate:rollback

# Crear nuevo seeder
php artisan make:seeder SeederName
```

## Arquitectura General

### Modelos de Dominio Principales

El sistema está organizado alrededor de entidades de negocio principales:

- **Order**: Transacciones de venta con múltiples métodos de pago (efectivo, tarjeta, cheque, transferencia, dólares, vales). Las órdenes pueden vincularse a facturas AFIP.
- **Product**: Artículos de inventario con categorías, colores, talles, marcas y soporte de códigos de barra.
- **Vale**: Crédito del comercio generado desde devoluciones o emitido a clientes. Rastrea monto original, monto usado y vencimiento.
- **Devolucion**: Devoluciones de productos que pueden generar vales para clientes.
- **Presupuesto**: Sistema de presupuestos/cotizaciones con productos y servicios.
- **User**: Maneja tanto clientes como personal (diferenciados por UserType).
- **Afip**: Integración con AFIP para facturación (Facturas) y notas de crédito.

### Integración AFIP (Sistema Tributario Argentino)

Ubicada en `app/Classes/`:
- **WSAA.php**: Servicio de autenticación para web services de AFIP. Genera tickets de autenticación.
- **WSFEV1.php**: Servicio de generación de facturas electrónicas.
- Usa protocolo SOAP con autenticación basada en certificados.
- Soporta ambientes de producción y homologación (testing).
- Claves almacenadas en `app/Classes/keys/` (certificados y claves privadas).
- Templates XML en `app/Classes/xml/`.

**Importante**: La configuración de AFIP usa rutas hardcodeadas que pueden necesitar ajuste según el entorno (ver `WSAA.php:30` para diferencias Windows/Linux).

### Arquitectura del Sistema de Pagos

Las órdenes soportan múltiples métodos de pago simultáneos:
- `pago_efec`: Pago en efectivo
- `pago_tarj`: Pago con tarjeta
- `pago_cheque`: Pago con cheque
- `pago_transf`: Transferencia bancaria
- `pago_dolares`: Pago en dólares
- `pago_vale`: Pago con vale/crédito del comercio

Una sola orden puede dividir pagos entre estos métodos. La tabla pivot `order_vales` rastrea qué vales fueron aplicados a qué órdenes.

### Flujo de Devoluciones y Vales

1. Cliente inicia devolución vía `DevolucionController`
2. Sistema crea registro `Devolucion` con productos devueltos
3. Si el método de reembolso es "vale", el sistema genera un `Vale` con fecha de vencimiento
4. Los vales pueden usarse parcialmente en múltiples órdenes
5. Tabla pivot `OrderVale` rastrea uso de vales por orden

### Organización de Controladores

- **ControlController**: Interfaz principal de POS y gestión de caja
- **ProductController**: CRUD de productos, generación de códigos de barra, actualización masiva de precios
- **AfipController**: Generación de facturas, notas de crédito, visualización de tickets
- **DevolucionController**: Procesamiento de devoluciones y generación de vales
- **PresupuestoController**: Creación de presupuestos/cotizaciones
- **ReporteController**: Reportes de ventas (semanales, mensuales, personalizados)
- **UserController**: Gestión de clientes y personal

### Arquitectura Frontend

Los componentes Vue.js están embebidos en templates Blade. Los assets se concatenan vía webpack.mix.js:
- JavaScript: jQuery, Vue, Axios, notificaciones Toastr, Moment.js
- CSS: Bootstrap 3, tema SB Admin 2, íconos Open Iconic

Vistas principales organizadas por dominio:
- `resources/views/control/`: Interfaz POS (caja, ingresos, gastos, movimientos)
- `resources/views/products/`: Gestión de productos
- `resources/views/devoluciones/`: Interfaz de devoluciones
- `resources/views/afip/`: Generación de facturas
- `resources/views/pdf/`: Templates PDF para facturas/tickets

### Estructura de Rutas

Todas las rutas de admin están protegidas por middleware `is_admin`. Grupos de rutas principales:
- `/admin/productos`: Gestión de productos
- `/admin/afip`: Generación y gestión de facturas
- `/admin/reportes`: Reportes de ventas
- `/admin/control/*`: Operaciones de POS (definidas en ControlController)

### Convención de Nomenclatura de Migraciones

Las migraciones usan prefijos de timestamp. La migración `2099_12_31_235959_crear_relaciones.php` se ejecuta última y crea relaciones de claves foráneas, asegurando que todas las tablas existan primero.

## Características Clave a Entender

### Generación de Códigos de Barra
Los productos pueden generar códigos de barra individuales o en lote vía rutas:
- `/admin/productos/{product}/generate-bar-code`: Producto único
- `/admin/productos/generate-bar-codes`: Múltiples productos

### Creación Múltiple de Productos
El sistema soporta creación de productos única y en lote:
- Único: `ProductController@create`
- Múltiple: `ProductController@createMultiple` (útil para productos con múltiples talles/colores)

### Actualización de Precios
Actualización masiva de precios disponible vía `ProductController@actualizarPrecios` - permite actualizar precios por categorías o todos los productos.

### Gestión de Caja
El sistema POS rastrea operaciones de efectivo diarias con balances de apertura/cierre. El modelo `Control` rastrea movimientos financieros.

## Backup de Base de Datos

El script `backup.sh` automatiza backups de base de datos y archivos:
- Lee credenciales desde archivo `.env`
- Crea dump MySQL con timestamp
- Archiva directorio `public/uploads`
- Envía a ubicación remota de backup vía `/opt/backup/backup.php`

**Uso**: Ejecutar desde raíz del proyecto: `./backup.sh`

## Notas Importantes

- El sistema usa Bootstrap 3 (versión antigua) - al actualizar componentes UI, asegurar compatibilidad.
- La integración AFIP requiere certificados válidos en `app/Classes/keys/`. El testing usa ambiente de homologación.
- Imágenes de productos y uploads almacenados en directorio `public/uploads`.
- La rama principal es `main`, el desarrollo parece ocurrir en ramas de features (ej. `oregon2`).
- Existe soporte multi-moneda (pesos/dólares) en métodos de pago.
