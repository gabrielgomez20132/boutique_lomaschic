@extends('admin')
@section('content2')
<style>
    #graficoProductos .apexcharts-legend {
        font-size: 11px !important;
        line-height: 1.2 !important;
    }

    #graficoProductos .apexcharts-legend-series {
        margin: 2px 4px !important;
    }

        /* Estilo de la tabla de ganancias */
    #tablaGanancias {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        font-family: 'Segoe UI', Tahoma, sans-serif;
        font-size: 14px;
        color: #333;
        background-color: #fff;
        border-radius: 8px;
        overflow: hidden;
    }

    #tablaGanancias thead {
        background-color: #f5f7fa;
        font-weight: bold;
    }

    #tablaGanancias th,
    #tablaGanancias td {
        padding: 10px 12px;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
    }

    #tablaGanancias td.text-center {
        text-align: center;
    }

    #tablaGanancias td.text-right {
        text-align: right;
    }

    .badge-cantidad {
        background-color: #e2e6fa;
        color: #3f51b5;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 12px;
        display: inline-block;
    }
</style>

    <h3>Dashboard - Reportes</h3>

    <!-- Botones y Filtros -->
    <div class="form-inline" style="margin-bottom: 20px;">
        <button onclick="cargarDatos('semana')" class="btn btn-primary">
            Última Semana
        </button>
        <button onclick="cargarDatos('mes')" class="btn btn-primary" style="margin-left: 10px;">
            Último Mes
        </button>

        <div class="form-group" style="margin-left: 20px;">
            <label for="fechaInicio">Desde:</label>
            <input type="date" id="fechaInicio" class="form-control input-sm" style="margin-left: 5px;">
        </div>

        <div class="form-group" style="margin-left: 10px;">
            <label for="fechaFin">Hasta:</label>
            <input type="date" id="fechaFin" class="form-control input-sm" style="margin-left: 5px;">
        </div>

        <button onclick="cargarDatosPersonalizados()" class="btn btn-success" style="margin-left: 10px;">
            Filtrar
        </button>
    </div>

    <!-- Gráficos -->
    <div class="row" style="margin-bottom: 20px;">
        <!-- Gráfico de ventas por día -->
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading text-center"><strong>Total de Ventas por Día</strong></div>
                <div class="panel-body text-center">
                    <div id="graficoVentasDia"></div>
                    <div id="resumenVentas" class="text-center" style="margin-top: 10px; font-weight: bold; font-size: 14px; color: #333;"></div>
                </div>
            </div>
        </div>

        <!-- Gráfico de productos más vendidos -->
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading text-center"><strong>15+ Vendidos</strong></div>
                <div class="panel-body text-center">
                    <div id="graficoProductos"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 30px;">
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading"><strong>Ganancias 15 Mas Vendidos</strong></div>
                <div class="panel-body">
                    <table class="table table-bordered table-striped table-hover" id="tablaGanancias">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cantidad Total</th>
                                <th class="text-right">Ganancia Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Contenido dinámico desde JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- <!-- Detalle de órdenes -->
    <div id="resumen" style="font-size: 13px;"></div> --}}

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>

        function formatoPesosArg(val) {
            return new Intl.NumberFormat('es-AR', {
                style: 'currency',
                currency: 'ARS',
                minimumFractionDigits: 2
            }).format(val);
        }

        Apex.defaults = {
            chart: {
                fontFamily: 'Arial, sans-serif',
                foreColor: '#333'
            }
        };   

        function cargarDatos(tipo) {
            fetch(`reporte/${tipo}`)
                .then(res => res.json())
                .then(data => {
                //mostrarDatos(data);
                mostrarGrafico(data);
                mostrarGraficoVentas(data);
                mostrarGananciasPorProducto(data);
            });
        }

        function cargarDatosPersonalizados() {
            const inicio = document.getElementById("fechaInicio").value;
            const fin = document.getElementById("fechaFin").value;

            if (!inicio || !fin) {
                alert("Por favor seleccioná ambas fechas.");
                return;
            }

            const fechaInicio = new Date(inicio);
            const fechaFin = new Date(fin);

            // Calcular la diferencia en milisegundos
            const diferenciaMs = fechaFin - fechaInicio;

            // Si es menor a 0, fecha fin es anterior a inicio
            if (diferenciaMs < 0) {
                alert("La fecha de fin no puede ser anterior a la de inicio.");
                return;
            }

            // Convertir a días
            const dias = diferenciaMs / (1000 * 60 * 60 * 24);

            if (dias > 31) {
                alert("El rango de búsqueda no puede superar 31 días.");
                return;
            }

            // Si está todo bien, continuar con la búsqueda
            fetch(`reporte/personalizado?inicio=${inicio}&fin=${fin}`)
                .then(res => res.json())
                .then(data => {
                    mostrarGrafico(data);
                    mostrarGraficoVentas(data);
                    mostrarGananciasPorProducto(data);
                });
        }

        let chart = null;
        let chartVentas = null;

        function mostrarGraficoVentas(ordenes) {
            const contenedor = document.querySelector("#graficoVentasDia");
            contenedor.innerHTML = '';

            if (chartVentas) {
                chartVentas.destroy();
                chartVentas = null;
            }

            if (!ordenes || ordenes.length === 0) {
                contenedor.innerHTML = "<p>No hay datos de ventas para este período.</p>";
                return;
            }

            const datosPorDia = {};

            ordenes.forEach(order => {
                const fecha = new Date(order.created_at).toLocaleDateString('es-AR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                });

                if (!datosPorDia[fecha]) {
                    datosPorDia[fecha] = { ingreso: 0, costo: 0 };
                }

                order.productos.forEach(p => {
                    const cantidad = parseFloat(p.cantidad);
                    const precio = parseFloat(p.monto);
                    const costoUnidad = parseFloat(p.costo_x_uni);

                    datosPorDia[fecha].ingreso += cantidad * precio;
                    datosPorDia[fecha].costo += cantidad * costoUnidad;
                });
            });

            const fechasOrdenadas = Object.keys(datosPorDia).sort((a, b) => {
                const [dA, mA, yA] = a.split('/');
                const [dB, mB, yB] = b.split('/');
                return new Date(`${yA}-${mA}-${dA}`) - new Date(`${yB}-${mB}-${dB}`);
            });

            const ingresos = [];
            const costos = [];
            const ganancias = [];

            fechasOrdenadas.forEach(fecha => {
                const ingreso = datosPorDia[fecha].ingreso;
                const costo = datosPorDia[fecha].costo;
                const ganancia = ingreso - costo;

                ingresos.push(ingreso);
                costos.push(costo);
                ganancias.push(ganancia);
            });

            const fechaInicio = fechasOrdenadas[0];
            const fechaFin = fechasOrdenadas[fechasOrdenadas.length - 1];

            const totalIngreso = ingresos.reduce((sum, val) => sum + val, 0);
            const totalCosto = costos.reduce((sum, val) => sum + val, 0);
            const totalGanancia = ganancias.reduce((sum, val) => sum + val, 0);

            const resumenEjeX = `Del ${fechaInicio} al ${fechaFin} — Ingreso: $${totalIngreso.toFixed(0)} | Costo: $${totalCosto.toFixed(0)} | Ganancia: $${totalGanancia.toFixed(0)}`;

            // Mostrar resumen debajo del gráfico
            document.getElementById('resumenVentas').innerHTML = resumenEjeX;

            chartVentas = new ApexCharts(contenedor, {
                chart: {
                    type: 'bar',
                    height: 400,
                    stacked: false,
                    fontFamily: 'Arial, sans-serif',
                    foreColor: '#333'
                },
                dataLabels: {
                    enabled: true,
                    style: {
                        colors: ['#000'],
                        fontSize: '12px',
                        fontWeight: 'bold'
                    },
                    formatter: function (val) {
                        return formatoPesosArg(val);
                    }
                },
                series: [
                    { name: 'Ingreso ($)', data: ingresos },
                    { name: 'Costo ($)', data: costos },
                    { name: 'Ganancia ($)', data: ganancias }
                ],
                xaxis: {
                    categories: fechasOrdenadas,
                },
                yaxis: {
                    title: { text: 'Venta en Pesos' },
                    labels: {
                        formatter: val => formatoPesosArg(val)
                    }
                },
                tooltip: {
                    y: { formatter: val => formatoPesosArg(val) }
                },
                colors: ['#4caf50', '#f44336', '#2196f3'],
                legend: { position: 'top' }
            });

            chartVentas.render();
        }

    function mostrarGrafico(ordenes) {
        const contenedor = document.querySelector("#graficoProductos");
        contenedor.innerHTML = '';
        if (chart) {
            chart.destroy();
            chart = null;
        }

        if (!ordenes || ordenes.length === 0) {
            contenedor.innerHTML = "<p>No hay productos vendidos para este período.</p>";
            return;
        }

        const contadores = {};
        ordenes.forEach(order => {
            order.productos.forEach(p => {
                const nombre = p.producto?.nombre ?? 'Desconocido';
                contadores[nombre] = (contadores[nombre] || 0) + parseFloat(p.cantidad);
            });
        });

        const topProductos = Object.entries(contadores)
            .sort((a, b) => b[1] - a[1])
            .slice(0, 15);

        const labels = topProductos.map(item => item[0]);
        const data = topProductos.map(item => item[1]);

        chart = new ApexCharts(contenedor, {
            chart: {
                type: 'donut',
                height: 400,
                fontFamily: 'Arial, sans-serif',
                foreColor: '#333'
            },
            labels: labels,
            series: data,
            stroke: {
                show: true,
                width: 1,
                colors: ['#fff']
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '60%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                fontSize: '12px'
                            }
                        }
                    }
                }
            },
            dataLabels: {
                style: {
                    fontSize: '10px'
                }
            },
            legend: {
                position: 'bottom',
                fontSize: '11px',
                itemMargin: {
                    horizontal: 5,
                    vertical: 2
                },
                markers: {
                    width: 10,
                    height: 10
                }
            },
            tooltip: {
                y: {
                    formatter: val => `${parseInt(val)} unidades`
                },
                style: {
                    fontSize: '12px'
                }
            }
        });

        chart.render();
    }

    function mostrarGananciasPorProducto(ordenes) {
        const tabla = document.querySelector('#tablaGanancias tbody');
        tabla.innerHTML = ''; // Limpiar contenido previo

        const productosMap = {};

        ordenes.forEach(order => {
            order.productos.forEach(p => {
                const nombre = p.producto?.nombre ?? 'Desconocido';
                const cantidad = parseFloat(p.cantidad);
                const precio = parseFloat(p.monto);
                const costo = parseFloat(p.costo_x_uni);
                const ganancia = (precio - costo) * cantidad;

                if (!productosMap[nombre]) {
                    productosMap[nombre] = { cantidad: 0, ganancia: 0 };
                }

                productosMap[nombre].cantidad += cantidad;
                productosMap[nombre].ganancia += ganancia;
            });
        });

        // Ordenar por ganancia descendente
        const productosOrdenados = Object.entries(productosMap)
            .sort((a, b) => b[1].cantidad - a[1].cantidad)
            .slice(0, 15);

        productosOrdenados.forEach(([nombre, datos]) => {
            const fila = document.createElement('tr');

            fila.innerHTML = `
                <td>${nombre}</td>
                <td class="text-center"><span class="badge-cantidad">${parseInt(datos.cantidad)} UND</span></td>
                <td class="text-right">${formatoPesosArg(datos.ganancia)}</td>
            `;

            tabla.appendChild(fila);
        });
    }

        // function mostrarDatos(ordenes) {
        //     const dashboard = document.getElementById('resumen');
        //     dashboard.innerHTML = '';

        //     ordenes.forEach(order => {
        //     let html = `<h3>Orden #${order.id}</h3>`;
        //     order.productos.forEach(p => {
        //         const producto = p.producto;
        //         const categoria = producto.category;

        //         const cantidad = parseFloat(p.cantidad);
        //         const monto = parseFloat(p.monto);
        //         const costo = parseFloat(p.costo_x_uni);

        //         const ingreso = cantidad * monto;
        //         const costoTotal = cantidad * costo;
        //         const ganancia = ingreso - costoTotal;

        //         html += `
        //             <p>
        //                 <strong>${producto.nombre}</strong> - ${cantidad} unidades<br>
        //                 Categoría: ${categoria.nombre} (${categoria.unidad})<br>
        //                 <strong>Ingreso:</strong> $${ingreso.toFixed(2)}<br>
        //                 <strong>Costo:</strong> $${costoTotal.toFixed(2)}<br>
        //                 <strong>Ganancia:</strong> $${ganancia.toFixed(2)}
        //             </p>`;
        //     });
        //     dashboard.innerHTML += html + '<hr>';
        // });
        // }

        // // Cargar por defecto la semana
        // cargarDatos('semana');
    </script>
@endsection