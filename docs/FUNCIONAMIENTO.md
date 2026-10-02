# LoMásChic: funcionamiento del sistema y despliegue

Producción: https://lomaschic.syncodeit.com.ar
Servidor: `/opt/docker/boutique_lomaschic` (Docker + nginx-proxy + MySQL compartido)

---

## 1. Ventas

### Flujo normal
1. **VENDER** → se crea la orden con `completada = 0`.
2. Se cargan los productos. **El stock se descuenta en el momento de agregar el producto**, no al cobrar.
3. Se elige cliente y forma de pago → **Cobrar** (`ControlController@cerrar_orden`) → la orden pasa a `completada = 1` y se guardan los pagos.

### Ventas pendientes
- Una orden **sin cobrar** (`completada = 0`) aparece en gris en *Ingresos del día*.
- **No** suma en Movimientos, en el Informe ni en el Cierre de caja hasta que se cobra.
- **Sí** tiene el stock descontado.
- Para cobrarla: botón del ojo → completar el cobro. Para anularla: tacho (devuelve el stock).
- ⚠️ Si se cierra la caja con ventas pendientes, desaparecen de *Ingresos del día* y, si se cobran después, esa plata no entra en ningún turno. **Cobrar o anular las pendientes antes de cerrar.**

### Descuento y recargo
- **% DESC / $ DESC**: descuento en porcentaje o en monto fijo.
- **% ADIC** (azul, debajo de % DESC): recargo en porcentaje, calculado **sobre el total ya descontado**.
  - Ej: $31.827 − 10% DESC = $28.645 → + 10% ADIC = $31.510.
- Al cobrar, el recargo **se suma al monto de la orden** y queda guardado en `orders.recargo`. Por eso efectivo, tarjeta, vuelto, Movimientos y Cierre ya lo incluyen.
- El recargo no suma ganancia por producto; queda como ingreso extra.
- En la orden cobrada se ven las etiquetas `DESC. $` y `ADIC. $`. El ticket **no** muestra una línea de recargo, pero su Total sí lo incluye.

### Ticket de venta
- Muestra **Venta Nº X** arriba a la derecha. Con ese número se busca la venta para hacer una devolución.
- Las facturas AFIP no se tocaron (hoy están desactivadas).

---

## 2. Formas de pago (en caja)
| Forma | Dónde suma |
|---|---|
| Efectivo | Efectivo (y en el efectivo que tiene que haber en caja) |
| Tarjeta | Tarjetas |
| **Banco Nación Marcaton (id 6)** | **Tarjeta Marcaton**, en una línea separada de Tarjetas. También incluye las ventas Marcaton con parte fiada (que el sistema guarda en `pago_cheque`). |
| Transferencia | Transferencias |
| Mercado Pago (id 24) | Mercado Pago |
| Cheque | Cheques |
| Vale | Se descuenta del vale (no entra plata nueva) |

---

## 3. Caja

### Movimientos del turno (Control → Movimientos)
- Muestra solo el turno abierto (`deHoy = 1`) y solo las ventas cobradas.
- Historial: busca por fecha (si hubo dos turnos en un día, los suma).

### Cierre de caja
Al confirmar **"Sí, cerrar"**:
1. Se arma un **resumen del turno** con los mismos cálculos que Movimientos (`ControlController@datosTurno`).
2. Se **guarda** en la tabla `cierres_caja` como una foto del momento: si después se editan ventas, el cierre guardado no cambia.
3. Se cierra la caja (las órdenes pasan a `deHoy = 0`).
4. Se muestra el **Resumen de cierre**, que se puede imprimir y trae líneas de firma.

El resumen incluye: efectivo que tiene que haber en caja (con su detalle), cantidad de ventas, unidades, ingresos, ganancia (solo la ve el admin), cobrado por forma de pago, movimientos de caja, productos vendidos y aviso de ventas pendientes.

### Historial de cierres (Caja → Cierres de Caja)
- Lista todos los cierres: fecha, horario del turno, quién cerró, ventas, efectivo y total.
- **Ver** abre el resumen. **Imprimir** lo abre en otra pestaña y lanza la impresión.

---

## 4. Informe (Reportes)
- Gráfico por día y productos más vendidos.
- ⚠️ Hoy **cuenta también las ventas pendientes** (no filtra `completada`). Si hay una pendiente, el informe da más que Movimientos hasta que se cobra.
  - Para que coincidan, agregar `->where('completada', 1)` en `ReporteController` (`mes`, `semana`, `personalizado`).

---

## 5. Devoluciones y cambios (Control → Devoluciones)

### Devolución
1. **Devoluciones → crear** → buscar la venta por **Nº de venta** (está en el ticket), cliente o fecha.
2. Se ven **Cliente, Fecha y Forma de pago** de la venta original.
3. Marcar los productos y cantidades a devolver (opcional: observaciones).
4. **Procesar devolución**:
   - el stock vuelve al inventario;
   - se genera un **vale** por el monto devuelto, **válido 15 días**;
   - el sistema vuelve al detalle de la devolución y el **ticket del vale se abre en una ventana aparte**, que se cierra sola al imprimir.
- No se puede devolver más de lo vendido: se tienen en cuenta las devoluciones anteriores de la misma venta.

### ¿Para qué el vale?
Es un crédito a favor del cliente. Sirve para que no pierda la plata de lo que devuelve y la use en lo que se lleva. La caja queda bien sola: solo entra la diferencia que se cobra.

### Cambio (venta con el vale)
1. **VENDER** → cargar lo que se lleva.
2. Elegir el **mismo cliente** de la venta original (o General).
3. Forma de pago:
   - **Vale**, si lo nuevo vale igual o menos;
   - **Efectivo/Vale, Tarjeta/Vale, Transferencia/Vale o Cheque/Vale**, si vale más.
4. Elegir el vale en **"Vale disponible"** o escribir el código en **"Buscar Vale"**.
5. En las formas combinadas, completar los dos montos → **Cobrar**.
- Si lo nuevo vale **menos** que el vale, se genera un **vale nuevo por la diferencia**.

Ejemplo: devuelve un Top de $10.000 y se lleva algo de $15.000 → Efectivo/Vale: $10.000 con el vale y $5.000 en efectivo. En caja entran $5.000.

---

## 6. Imágenes
- Fotos de productos: `storage/app/public/products/` (se ven en `/storage/products/...`). Requiere `php artisan storage:link` (ya hecho en el server).
- Imagen por defecto: `sinImagen.png` (está en el repo).
- `git pull` y `docker compose build` **no** borran imágenes. **Nunca** usar `git clean -x`.

---

## 7. Assets (CSS/JS)
- Las vistas Blade y `public/css/style.css` **no** necesitan compilación.
- Si se toca `resources/assets/js` o `resources/assets/css`: correr `npm run assets` (concatena en `public/js/app.js` y `public/css/app.css`; no necesita `npm install`).
- `npm run prod` no funciona: Laravel Mix 1.x y node-sass no son compatibles con el Node actual.

---

## 8. Despliegue

### Desde la PC (local)
```bash
php artisan migrate          # si hay migraciones nuevas
npm run assets               # solo si se tocó resources/assets
git add .
git status                   # que no aparezcan .env, *.key, *.sql
git commit -m "mensaje"
git push
```

### En el servidor
```bash
cd /opt/docker/boutique_lomaschic
git pull
docker exec -it lomaschic_app php artisan migrate --force
docker exec -it lomaschic_app php artisan route:clear
docker exec -it lomaschic_app php artisan config:cache
docker exec -it lomaschic_app php artisan view:cache
```
- Si cambia el `.env` → volver a correr `config:cache`.
- Si cambia el `Dockerfile` → `docker compose build --no-cache && docker compose up -d`.
- Si `git pull` da conflicto por permisos en `storage/` → `git config core.fileMode false` (ya está configurado).
- Error 500 → `tail -50 storage/logs/laravel.log`.

### Infraestructura
- Contenedores: `lomaschic_app` (PHP 7.4-fpm) y `lomaschic_nginx`.
- Redes: `nginx-proxy_proxy-network` (proxy) y `mysql_default` (MySQL compartido, host `mysql`).
- Base: `boutique_lomaschic`, usuario `lomaschic_user`.
- Proxy: `deploy/lomaschic.conf` copiado al `conf.d` del nginx-proxy. Para recargarlo: `docker exec nginx-proxy nginx -t && docker exec nginx-proxy nginx -s reload`.
- Cloudflare: subdominio `lomaschic` con el proxy activado (nube naranja).
