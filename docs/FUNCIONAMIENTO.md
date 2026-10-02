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

### Paso 1: Registrar la devolución
1. **Control → Devoluciones → crear.**
2. Buscar la venta original por **Nº de venta** (está impreso en el ticket, arriba a la derecha), cliente o fecha.
3. Se muestran **Cliente, Fecha y Forma de pago** de esa venta, para confirmar que es la correcta.
4. Marcar los productos que devuelve y la cantidad. Opcional: observaciones (motivo, estado).
5. **Procesar devolución y generar vale**:
   - las unidades **vuelven al stock**;
   - se genera un **vale** por el monto devuelto, **válido 15 días**;
   - el sistema vuelve al detalle de la devolución y el **ticket del vale se abre en una ventana aparte**, que se cierra sola al imprimir. Ese ticket se le entrega al cliente.
- No se puede devolver más de lo vendido: se tienen en cuenta las devoluciones anteriores de la misma venta.
- La devolución **no mueve plata de la caja**: no sale efectivo, queda un crédito (el vale).

### ¿Para qué sirve el vale?
Es un **crédito a favor del cliente** por lo que devolvió. Permite:
- que use esa plata en lo que se lleva, sin que salga ni entre efectivo de más;
- que la caja quede bien sola: **solo entra la diferencia** que paga;
- que quede registro de cada cambio (qué se devolvió, de qué venta y con qué vale).

### Paso 2: Venta con el vale (el cambio)
1. **VENDER** → cargar lo que se lleva (por ej. el talle nuevo).
2. **Cliente**: el mismo de la venta original (o General).
3. **F. de pago**:
   - **Vale**, si lo nuevo cuesta **igual o menos** que el saldo del vale.
   - **Efectivo/Vale, Tarjeta/Vale, Transferencia/Vale o Cheque/Vale**, si cuesta **más**.
4. **Elegir el vale**:
   - Cliente **General** → **Buscar Vale**: escribir el código impreso en el ticket del vale (`VALE-...`).
   - Cliente **con nombre** → **Vale disponible**: lista desplegable con los vales de ese cliente.
   - Se muestra el saldo **Disponible** del vale.
5. Montos:
   - Con **Vale**: el **Monto Vale se completa solo** (lo menor entre el saldo del vale y el TOTAL) y se actualiza si cambia el total. No se edita a mano.
   - Con una **forma combinada**: aparecen dos campos, el del medio de pago (Efectivo, Tarjeta, Transferencia o Cheque) y **Vale**. Ver "Forma combinada paso a paso".
6. **Cobrar.**

### Forma combinada paso a paso (lo nuevo cuesta más que el vale)
1. F. de pago: **Efectivo/Vale**, **Tarjeta/Vale**, **Transferencia/Vale** o **Cheque/Vale**.
2. Elegir el vale (Buscar Vale o Vale disponible) y mirar el **Disponible**.
3. En **Vale**: poner el saldo del vale (no más que el Disponible).
4. En el otro campo: poner **la diferencia exacta**: TOTAL − Vale.
5. **Cobrar** (se habilita cuando los dos montos suman el TOTAL).

⚠️ En el campo de efectivo va **lo que corresponde cobrar**, no lo que entrega el cliente. Si paga $5.000 con un billete de $10.000, se escribe **5.000** y el vuelto se da aparte. Si se escribe de más, el sistema registra esa plata en caja y además **genera un vale por el excedente**.

Si el monto del vale supera el saldo, o el vale está vencido o inactivo, el sistema **no cobra** y muestra el error arriba de la orden.

### ¿Qué pasa con el saldo del vale?
- **Lo nuevo cuesta lo mismo que el vale** → el vale se usa entero y queda inactivo.
- **Lo nuevo cuesta menos** → se usa solo lo justo y **el resto sigue en el mismo vale** (mismo código, mismo vencimiento). El cliente vuelve otro día con el mismo ticket. Cuando el saldo llega a $0, el vale se desactiva solo.
- **Lo nuevo cuesta más** → forma combinada: el vale cubre una parte y el cliente paga la diferencia.
- Caso raro: si en una forma combinada se cargan pagos que suman **más** que el total, el sistema genera un **vale nuevo por el excedente**.
- Un vale vencido (más de 15 días) o sin saldo no aparece ni se puede usar.

### Ejemplos
| Devuelve | Se lleva | Forma de pago | Resultado |
|---|---|---|---|
| Top $10.000 | Top otro talle $10.000 | Vale | No paga nada. El vale queda usado. En caja: $0 |
| Top $10.000 | Remera $15.000 | Efectivo/Vale ($10.000 vale + $5.000 efectivo) | En caja entran $5.000 |
| Top $20.000 | Top $15.000 | Vale | Paga con el vale. **Al vale le quedan $5.000** para otra compra |

### Cómo se ve en caja
- **Devolución:** no aparece en Movimientos (no mueve plata).
- **Venta con vale:** la parte pagada con vale **no** suma a efectivo, tarjeta, etc. Solo suma lo que realmente entró (la diferencia).
- **Stock:** sube con la devolución y baja con la venta nueva.

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
