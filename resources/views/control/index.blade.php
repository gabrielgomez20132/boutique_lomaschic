@extends('admin')

@section('content2')

<div id="wrapper">

    <!-- Navigation -->
    <!-- /.navbar-header -->
        <div class="sidebar" role="navigation">
            <div class="sidebar-nav navbar-collapse">
                <ul class="nav" id="side-menu">
                    @if( Auth::user()->role_id == 1 )
                    <li>
                        <a href="#">
                            <span class="oi oi-chevron-bottom"></span>  
                            Caja
                        </a>
                            
                        <ul class="nav nav-second-level">
                            <li>
                                <a href="/admin/control/caja/inicio">Inicio</a>
                            </li>

                            <li>
                                <a href="/admin/control/caja/retiros">Retiros</a>
                            </li>

                            <li>
                                <a href="/admin/control/caja/cierres">Cierres de Caja</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#">
                            <span class="oi oi-chevron-bottom"></span>
                            Gastos
                        </a>
                        
                        <ul class="nav nav-second-level">
                            {{-- <li>
                                <a href="/admin/control/gastos/comida">Comida</a>
                            </li>     --}}
                            
                            {{-- <li>
                                <a href="/admin/control/gastos/contador">Contador</a>
                            </li> --}}

                            <li>
                                <a href="/admin/control/gastos/proveedores">Proveedores</a>
                            </li>

                            <li>
                                <a href="/admin/control/gastos/servicios">Servicios</a>
                            </li>
                            
                            <li>
                                <a href="/admin/control/gastos/varios">Varios</a>
                            </li>  
                        </ul>
                    </li>
                    <li>
                        <a href="/admin/control/ingresos/productos">
                            <span class="oi oi-dollar"></span>
                            Ingresos
                        </a>
                    </li>
                    {{-- <li>
                        <a href="/admin/control/sueldos">
                            <span class="oi oi-dollar"></span>
                            Sueldos
                        </a>
                    </li> --}}
                    {{-- <li>
                        <a href="/admin/control/comisiones">
                            <span class="oi oi-dollar"></span>
                            Comisiones
                        </a>
                    </li> --}}
                    {{-- <li>
                        <a href="/admin/control/adelantos">
                            <span class="oi oi-dollar"></span>
                            Adelantos
                        </a>
                    </li> --}}
                    <li>
                        <a href="/admin/control/movimientos">
                            <span class="oi oi-clock"></span>
                            Movimientos
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    <!-- Page Content -->
    <div id="page-wrapper">
        @yield('content3')
    </div>
</div>
    
@endsection
