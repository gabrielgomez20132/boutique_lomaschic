Vue.config.devtools = true;
window.onload = function () {
    function debounce(fn, delay) {
        var timeoutID = null
        return function () {
            clearTimeout(timeoutID)
            var args = arguments
            var that = this
            timeoutID = setTimeout(function () {
                fn.apply(that, args)
            }, delay)
        }
    }
    toastr.options.timeOut = 3000;

    Vue.filter('formatDate', function (value) {
        if (value) {
            return moment(String(value)).format('DD/MM/YY')
        }
    });

    Vue.filter('formatTime', function (value) {
        if (value) {
            return moment(String(value)).format('hh:mm')
        }
    });

    Vue.config.devtools = true
    // Vue.js
    if (document.getElementById('main')) {
        new Vue({
            el: '#main',
            delimiters: ['!{', '}!'],
            data: function() {
                return {
                    clientes: [],
                    clienteElegido: 2,
                    fsdpago: [],
                    fdpagoElegida: 1,
                    monto: 1,
                    descripcion: null,
                    newCant: 1,
                    newCod: null,
                    descuento: null,
                    perc_desc: null,
                    dec_desc: null,
                    perc_adic: null,
                    recargo: 0,
                    total: null,
                    subOrdenes: [],
                    products: [],
                    pagoCon: null,
                    pagoEfec: 0,
                    pagoVale: 0,
                    valesDisponibles: [],
                    valeElegido: '',
                    montoVale: 0,
                    codigoValeBuscado: '',
                    valeEncontrado: null,
                    buscarValeTimeout: null,
                    productsFound: []
                };
            },
            methods: {
                getClientes: function () {
                    var urlClientes = '/admin/getclientes';
                    axios.get(urlClientes).then(response => {
                        this.clientes = response.data
                    });
                },
                getFdPago: function () {
                    var urlFdPago = '/admin/getfdpago';
                    axios.get(urlFdPago).then(response => {
                        this.fsdpago = response.data
                    });
                },
                getSubOrdenes: function () {
                    var urlSubOrdenes = '/admin/getsubordenes/' + App.id_order;
                    axios.get(urlSubOrdenes).then(response => {
                        this.subOrdenes = response.data;
                        this.setFocus();
                    });
                },
                addSubOrder: function (product) {

                    var urlcreatesuborden = '/admin/createsuborden_prod/' + App.id_order;
                    axios.post(urlcreatesuborden, {
                        cantidad: this.newCant,
                        product_id: product.id
                    }).then(response => {
                        if (response.data.status == "OK") {
                            this.getSubOrdenes();
                            this.newCant = 1;
                            this.newCod = null;
                            toastr.success(response.data.message);
                            $('#modalMultiProd').modal('hide');
                        }
                    }).catch(function (error) {
                        toastr.error(error.response.data.message, error.response.data.titulo);
                    });
                },
                createSuborden: function () {
                    var urlcreatesuborden = '/admin/createsuborden/' + App.id_order;
                    axios.post(urlcreatesuborden, {
                        cantidad: this.newCant,
                        codigo: this.newCod
                    }).then(response => {
                        if (response.data.status == "OK") {
                            this.getSubOrdenes();
                            this.newCant = 1;
                            this.newCod = null;
                            toastr.success(response.data.message);
                        } else if (response.data.status == "QUERY") {
                            this.products = response.data.products;
                            $('#modalMultiProd').modal('show');
                        }
                    }).catch(function (error) {
                        toastr.error(error.response.data.message, error.response.data.titulo);
                    });
                },
                createSubordenSvc: function () {
                    var urlcreatesuborden = '/admin/createsuborden_svc/' + App.id_order;
                    axios.post(urlcreatesuborden, {
                        monto: this.monto,
                        descripcion: this.descripcion
                    }).then(response => {
                        this.getSubOrdenes();
                        this.descripcion = null;
                        this.monto = 0;
                        $("#modalServicios").modal("hide");
                        $(".modal-backdrop").remove();
                        $(document.body).removeClass("modal-open");

                        /*                     
                                               $('#btnCancelar').click();
                       
                                               $("#modalServicios").modal("toogle");
                       
                                               $(document.body).removeClass("modal-open");
                                               $(".modal-backdrop").remove();
                                               this.newCant = 1;
                                               this.newCod = null;
                                               toastr.success(response.data.message);*/

                    }).catch(function (error) {
                        toastr.error(error.response.data.message, error.response.data.titulo);
                    });
                },
                deleteSuborden: function (subOrden) {
                    var urldeletesuborden = '/admin/deletesuborden/' + subOrden.id;
                    axios.delete(urldeletesuborden)
                        .then(response => {
                            this.getSubOrdenes();
                            toastr.error(response.data.message);
                        });
                },
                deleteSubordenSvc: function (subOrden) {
                    var urldeletesuborden = '/admin/deletesuborden_svc/' + subOrden.id;
                    axios.delete(urldeletesuborden)
                        .then(response => {
                            this.getSubOrdenes();
                            toastr.error(response.data.message);
                        });
                },
                formatCantidad: function (cantidad) {
                    var n = parseFloat(cantidad);
                    if (isNaN(n)) {
                        return cantidad;
                    }
                    if (Math.abs(n - Math.round(n)) < 0.0000001) {
                        return String(Math.round(n));
                    }
                    return String(n);
                },
                updateCantidad: function (subOrden, event) {
                    var nueva = String(event.target.value).replace(',', '.').trim();
                    var cantidad = parseFloat(nueva);
                    if (!cantidad || cantidad <= 0 || isNaN(cantidad)) {
                        event.target.value = this.formatCantidad(subOrden.cantidad);
                        toastr.error('La cantidad tiene que ser mayor a 0');
                        return;
                    }
                    if (parseFloat(subOrden.cantidad) === cantidad) {
                        event.target.value = this.formatCantidad(subOrden.cantidad);
                        return;
                    }
                    var self = this;
                    axios.post('/admin/updatesuborden/' + subOrden.id, {
                        cantidad: cantidad
                    }).then(function (response) {
                        self.getSubOrdenes();
                        toastr.success(response.data.message);
                    }).catch(function (error) {
                        event.target.value = self.formatCantidad(subOrden.cantidad);
                        toastr.error(error.response.data.message, error.response.data.titulo);
                    });
                },
                setFocus: function () {
                    // Note, you need to add a ref="search" attribute to your input.
                    if (this.$refs.codigo) {
                        this.$refs.codigo.focus();
                    }
                },
                resetPagocon: function () {
                    this.pagoCon = null;
                    this.pagoEfec = 0;
                },
                resetPagoEfec: function () {
                    this.pagoEfec = null;
                },
                resetPagoTarj: function () {
                    this.pagoEfecTarj = 0;
                },
                resetPagoVale: function () {
                    this.pagoVale = null;
                },
                resetDesc: function () {
                    this.descuento = null;
                    this.perc_desc = null;
                    this.dec_desc = null;
                    this.perc_adic = null;
                    this.recargo = 0;

                },
                getValesCliente: function (idCliente) {
                    var urlVales = '/admin/getvalescliente/' + idCliente;
                    axios.get(urlVales).then(response => {
                        this.valesDisponibles = response.data;
                        // Reset vale seleccionado cuando cambia cliente
                        this.valeElegido = '';
                        this.montoVale = 0;
                    }).catch(error => {
                        console.error('Error al obtener vales:', error);
                        this.valesDisponibles = [];
                    });
                },
                buscarValePorCodigo: function () {
                    if (!this.codigoValeBuscado || this.codigoValeBuscado.trim() === '') {
                        this.valeEncontrado = null;
                        this.montoVale = 0;
                        return;
                    }

                    var urlBuscarVale = '/admin/buscarvaleporcod/' + encodeURIComponent(this.codigoValeBuscado.trim());
                    axios.get(urlBuscarVale).then(response => {
                        if (response.data) {
                            this.valeEncontrado = response.data;
                            // Calcular monto a aplicar
                            const totalConDescuento = this.totalSuma;
                            this.montoVale = Math.min(this.valeEncontrado.monto_disponible, totalConDescuento);
                            toastr.success('Vale encontrado: $' + this.valeEncontrado.monto_disponible);
                        } else {
                            this.valeEncontrado = null;
                            this.montoVale = 0;
                            toastr.error('Vale no encontrado o vencido');
                        }
                    }).catch(error => {
                        this.valeEncontrado = null;
                        this.montoVale = 0;
                        toastr.error('Vale no encontrado o no disponible');
                    });
                },
                buscarValePorCodigoDebounce: function () {
                    // Limpiar timeout anterior
                    if (this.buscarValeTimeout) {
                        clearTimeout(this.buscarValeTimeout);
                    }

                    // Si el campo está vacío, limpiar resultado
                    if (!this.codigoValeBuscado || this.codigoValeBuscado.trim() === '') {
                        this.valeEncontrado = null;
                        this.montoVale = 0;
                        return;
                    }

                    // Esperar 500ms después de que el usuario deje de escribir
                    this.buscarValeTimeout = setTimeout(() => {
                        this.buscarValePorCodigo();
                    }, 500);
                },
                searchProducts: debounce(function () {
                    if (!this.newCod) {
                        this.productsFound = [];
                        return;
                    }
                    var url = '/admin/search/' + this.newCod;
                    axios.get(url).then(response => {
                        this.productsFound = response.data;
                    });
                }, 500),
                selectProduct: function (product) {
                    this.productsFound = [];
                    this.newCod = null;
                    this.addSubOrder(product);
                }
            },
            computed: {
                totalSuma: function () {
                    let total = 0;
                    for (let i = 0; i < this.subOrdenes.length; i++) {
                        total += Math.ceil(this.subOrdenes[i].cantidad * this.subOrdenes[i].monto);
                    }
                    if (total > 0) {
                        this.descuento = Math.ceil((this.perc_desc * total) / 100);
                        dec_desc = parseInt(this.dec_desc, 10);
                        this.descuento += isNaN(dec_desc) ? 0 : dec_desc;
                    }
                    if (this.descuento > total) {
                        this.descuento = total;
                    }
                    // Recargo (% ADIC) sobre el total ya descontado
                    let neto = total - this.descuento;
                    let perc_adic = parseFloat(this.perc_adic);
                    this.recargo = (neto > 0 && !isNaN(perc_adic) && perc_adic > 0) ? Math.ceil((perc_adic * neto) / 100) : 0;
                    return neto + this.recargo;
                },
                vuelto: function () {
                    vuelto = this.pagoCon - this.totalSuma;
                    if (vuelto < 0) {
                        vuelto = 0;
                    }
                    return vuelto;
                },
                fiado: function () {
                    if (this.pagoCon > 0) {
                        fiado = this.totalSuma - this.pagoCon;
                        if (fiado < 0) {
                            fiado = 0;
                        }
                        return fiado;
                    }
                },
                aPagar: function () {
                    if (this.pagoCon <= this.totalSuma) {
                        aPagar = this.pagoCon;
                    } else {
                        aPagar = this.totalSuma;
                    }

                    return aPagar;
                },
                pagoEfecTarj: {
                    get: function () {
                        if (this.aPagar >= this.pagoEfec) {
                            return this.aPagar - this.pagoEfec;
                        }
                        else {
                            this.pagoEfecTarj = 0;
                            return 0;
                        }
                    },
                    set: function (newValue) {
                        this.pagoEfec = this.aPagar - newValue;

                        if (this.pagoEfec < 0) {
                            this.pagoEfec = 0;
                        }
                    }
                },
                montoValeSeleccionado: function () {
                    if (this.valeElegido && this.valesDisponibles.length > 0) {
                        const vale = this.valesDisponibles.find(v => v.id == this.valeElegido);
                        return vale ? vale.monto_disponible : 0;
                    }
                    return 0;
                },
                puedeCerrarOrden: function () {
                    // Para formas de pago combinadas con Vale (20-23)
                    if ([20, 21, 22, 23].includes(this.fdpagoElegida)) {
                        // Verificar que haya un vale seleccionado/encontrado
                        if (!this.valeElegido && !this.valeEncontrado) {
                            return false;
                        }

                        // Verificar que los montos sumen al total
                        const totalPagos = (parseFloat(this.pagoEfec) || 0) + (parseFloat(this.pagoVale) || 0);
                        return totalPagos >= this.totalSuma;
                    }

                    // Para forma de pago solo Vale (19)
                    if (this.fdpagoElegida == 19) {
                        return (this.valeElegido || this.valeEncontrado) && this.montoVale >= this.totalSuma;
                    }

                    // Para FIADO (4)
                    if (this.fdpagoElegida == 4) {
                        return true;
                    }

                    // Para otras formas de pago (lógica original)
                    if (this.clienteElegido == 2) {
                        return this.pagoCon >= this.totalSuma;
                    }

                    return this.fiado !== void(0);
                }
            },
            created: function () {
                this.getSubOrdenes();
                this.getClientes();
                this.getFdPago();
            },
            watch: {
                clienteElegido: function(newClienteId) {
                    if (newClienteId && newClienteId !== 2) {
                        this.getValesCliente(newClienteId);
                    } else {
                        this.valesDisponibles = [];
                        this.valeElegido = '';
                        this.montoVale = 0;
                    }
                },
                valeElegido: function(newValeId) {
                    if (newValeId && newValeId !== '') {
                        const vale = this.valesDisponibles.find(v => v.id == newValeId);
                        if (vale) {
                            // Si el vale tiene más saldo que el total, usar solo lo necesario
                            const totalConDescuento = this.totalSuma;
                            this.montoVale = Math.min(vale.monto_disponible, totalConDescuento);
                        }
                    } else {
                        this.montoVale = 0;
                    }
                }
            }
        });
    }
    // Vue.js
    if (document.getElementById("productos")) {

        new Vue({
            el: '#productos',
            delimiters: ['!{', '}!'],
            data: {
                keywords: null,
                input: null,
                products: []
            },
            watch: {
                input: debounce(function (newVal) {
                    this.input = newVal;
                    this.fetch();
                }, 500)
            },
            methods: {
                fetch() {
                    this.keywords = this.input;
                    var url = '/admin/search/' + this.keywords;
                    if (this.keywords.length > 0) {
                        axios.get(url).then(response => this.products = response.data);
                        console.log(this.products)
                    }
                    else {
                        this.products = null;
                    }
                },
                highlight(text) {
                    text = text || '';
                    if (typeof text !== 'string') {
                        text = String(text);
                    }
                    return text.toString().replace(new RegExp(this.keywords, 'gi'), '<b style="color: #62b3ee">$&</b>');
                }
            }
        });
    }
};
