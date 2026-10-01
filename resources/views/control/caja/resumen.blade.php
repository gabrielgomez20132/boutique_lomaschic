@extends('control.index')

@section('content3')
@php
    $m = function ($v) {
        $v = (float) $v;
        $txt = number_format($v, 2, ',', '.');
        return '$ ' . (substr($txt, -3) === ',00' ? substr($txt, 0, -3) : $txt);
    };
    $esAdmin = Auth::user()->role_id == 1;
    $total_marcaton = $total_marcaton ?? 0; // cierres guardados antes de separar Marcaton
    $totalCobrado = $ingXprod_efec + $total_tarj + $total_marcaton + $total_transf + $total_mp + $total_cheque;
    $totalGeneral = $total_efec + $total_tarj + $total_marcaton + $total_cheque + $total_transf + $total_mp;
    $totalGastos  = $gastXprov + $gastXserv + $gastosVarios;
    $unidades     = array_sum(array_column($productos, 'cantidad'));
    $fApertura    = $apertura ? date('d/m/Y H:i', strtotime($apertura)) : '-';
    $fCierre      = date('d/m/Y H:i', strtotime($cierre));
@endphp

<style>
    .rc { --verde:#1e9e4a; --rojo:#c0392b; --azul:#1f6fd1; --tinta:#1f2933; --suave:#6b7785; --linea:#e4e8ee; --fondo:#f6f8fb;
          color: var(--tinta); max-width: 980px; margin: 10px auto 40px; }
    .rc * { box-sizing: border-box; }
    .rc-head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;
               border-bottom: 2px solid var(--tinta); padding-bottom: 14px; margin-bottom: 20px; }
    .rc-head h1 { margin:0 0 6px; font-size: 28px; font-weight: 600; }
    .rc-meta { color: var(--suave); font-size: 14px; line-height: 1.6; }
    .rc-meta b { color: var(--tinta); font-weight: 600; }
    .rc-actions { display:flex; gap:8px; }
    .rc-actions .btn { font-size: 15px; padding: 8px 16px; }

    .rc-hero { background: var(--tinta); color:#fff; border-radius: 10px; padding: 22px 26px; margin-bottom: 18px;
               display:flex; justify-content:space-between; align-items:center; gap:20px; flex-wrap:wrap; }
    .rc-hero .lbl { font-size: 13px; text-transform: uppercase; letter-spacing: .08em; opacity: .75; }
    .rc-hero .big { font-size: 44px; font-weight: 700; line-height: 1.1; font-variant-numeric: tabular-nums; }
    .rc-hero .det { font-size: 13px; opacity: .85; line-height: 1.7; text-align: right; font-variant-numeric: tabular-nums; }

    .rc-kpis { display:grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; margin-bottom: 18px; }
    .rc-kpi { background:#fff; border:1px solid var(--linea); border-radius: 10px; padding: 14px 16px; }
    .rc-kpi .lbl { font-size: 12px; color: var(--suave); text-transform: uppercase; letter-spacing: .06em; }
    .rc-kpi .val { font-size: 24px; font-weight: 700; margin-top: 4px; font-variant-numeric: tabular-nums; }
    .rc-kpi.verde .val { color: var(--verde); }

    .rc-grid { display:grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px; }
    @media (max-width: 800px) { .rc-grid { grid-template-columns: 1fr; } .rc-hero .det { text-align:left; } }
    .rc-card { background:#fff; border:1px solid var(--linea); border-radius: 10px; overflow: hidden; }
    .rc-card h3 { margin:0; font-size: 15px; font-weight: 600; padding: 12px 16px; background: var(--fondo);
                  border-bottom: 1px solid var(--linea); }
    .rc-list { width:100%; border-collapse: collapse; font-size: 15px; }
    .rc-list td { padding: 9px 16px; border-bottom: 1px solid var(--linea); }
    .rc-list tr:last-child td { border-bottom: 0; }
    .rc-list td:last-child { text-align:right; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .rc-list tr.cero td { color: #a9b2bd; }
    .rc-list tr.neg td:last-child { color: var(--rojo); }
    .rc-list tr.pos td:last-child { color: var(--verde); }
    .rc-list tr.total td { font-weight: 700; background: var(--fondo); border-top: 2px solid var(--linea); }

    .rc-alert { border-left: 4px solid #e0a100; background: #fff8e1; padding: 12px 16px; border-radius: 6px;
                margin-bottom: 18px; font-size: 15px; }
    .rc-prod th { font-size: 12px; text-transform: uppercase; letter-spacing: .05em; color: var(--suave);
                  padding: 10px 16px; border-bottom: 1px solid var(--linea); font-weight: 600; }
    .rc-prod th.r, .rc-prod td.r { text-align: right; }
    .rc-prod td { padding: 9px 16px; border-bottom: 1px solid var(--linea); font-size: 15px; font-variant-numeric: tabular-nums; }
    .rc-prod .sub { color: var(--suave); font-size: 13px; }
    .rc-empty { padding: 18px 16px; color: var(--suave); }
    .rc-firma { display:none; }

    @media print {
        body * { visibility: hidden; }
        .rc, .rc * { visibility: visible; }
        .rc { position: absolute; left: 0; top: 0; width: 100%; max-width: none; margin: 0; }
        .rc-actions { display: none; }
        .rc-hero { background: #fff !important; color: #000 !important; border: 2px solid #000; }
        .rc-card, .rc-kpi { break-inside: avoid; }
        .rc-firma { display:flex; justify-content: space-between; margin-top: 50px; font-size: 13px; }
        .rc-firma div { width: 40%; border-top: 1px solid #000; text-align: center; padding-top: 6px; }
        @page { margin: 12mm; }
    }
</style>

<div class="rc">
    <div class="rc-head">
        <div>
            <h1>Resumen de cierre de caja @isset($id_cierre)<span style="color:#6b7785;font-weight:400;">#{{ $id_cierre }}</span>@endisset</h1>
            <div class="rc-meta">
                Apertura: <b>{{ $fApertura }}</b> &nbsp;·&nbsp; Cierre: <b>{{ $fCierre }}</b><br>
                Cerró: <b>{{ $cerrado_por }}</b>
            </div>
        </div>
        <div class="rc-actions">
            <button type="button" class="btn btn-primary" onclick="window.print()">
                Imprimir
            </button>
            <a href="{{ route('control.caja.cierres') }}" class="btn btn-default">Ver cierres</a>
            <a href="{{ route('control.caja.inicio') }}" class="btn btn-default">Ir a Caja</a>
        </div>
    </div>

    @if($pendientes_cant > 0)
        <div class="rc-alert">
            <b>Atención:</b> quedaron {{ $pendientes_cant }} venta(s) sin cobrar por {{ $m($pendientes_monto) }}.
            No están incluidas en este resumen.
        </div>
    @endif

    <div class="rc-hero">
        <div>
            <div class="lbl">Efectivo que debe haber en caja</div>
            <div class="big">{{ $m($total_efec) }}</div>
        </div>
        <div class="det">
            Caja inicial {{ $m($caja_inicial) }}<br>
            + Ventas en efectivo {{ $m($ingXprod_efec) }}<br>
            @if($ingXpago_deudas > 0) + Cobro de deudas {{ $m($ingXpago_deudas) }}<br>@endif
            − Gastos {{ $m($totalGastos) }} &nbsp; − Retiros {{ $m($retiros) }}
        </div>
    </div>

    <div class="rc-kpis">
        <div class="rc-kpi">
            <div class="lbl">Ventas</div>
            <div class="val">{{ $cant_ventas }}</div>
        </div>
        <div class="rc-kpi">
            <div class="lbl">Unidades vendidas</div>
            <div class="val">{{ rtrim(rtrim(number_format($unidades, 3, ',', '.'), '0'), ',') }}</div>
        </div>
        <div class="rc-kpi">
            <div class="lbl">Ingresos x mercadería</div>
            <div class="val">{{ $m($ingXmercaderias) }}</div>
        </div>
        @if($esAdmin)
        <div class="rc-kpi verde">
            <div class="lbl">Ganancia</div>
            <div class="val">{{ $m($ganXmercaderias + $ganXservicios) }}</div>
        </div>
        @endif
    </div>

    <div class="rc-grid">
        <div class="rc-card">
            <h3>Cobrado por forma de pago</h3>
            <table class="rc-list">
                @foreach ([
                    'Efectivo'       => $ingXprod_efec,
                    'Tarjetas'       => $total_tarj,
                    'Tarjeta Marcaton' => $total_marcaton,
                    'Transferencias' => $total_transf,
                    'Mercado Pago'   => $total_mp,
                    'Cheques'        => $total_cheque,
                ] as $label => $valor)
                    <tr class="{{ $valor == 0 ? 'cero' : '' }}"><td>{{ $label }}</td><td>{{ $m($valor) }}</td></tr>
                @endforeach
                @if($total_dolares > 0)
                    <tr><td>Dólares</td><td>{{ $m($total_dolares) }}</td></tr>
                @endif
                <tr class="total"><td>Total cobrado</td><td>{{ $m($totalCobrado) }}</td></tr>
            </table>
        </div>

        <div class="rc-card">
            <h3>Movimientos de caja</h3>
            <table class="rc-list">
                <tr class="{{ $caja_inicial == 0 ? 'cero' : '' }}"><td>Caja inicial</td><td>{{ $m($caja_inicial) }}</td></tr>
                <tr class="{{ $ingXpago_deudas == 0 ? 'cero' : 'pos' }}"><td>Cobro de deudas (fiado)</td><td>{{ $m($ingXpago_deudas) }}</td></tr>
                <tr class="{{ $fiado == 0 ? 'cero' : 'neg' }}"><td>Fiado</td><td>{{ $m($fiado) }}</td></tr>
                <tr class="{{ $descuentos == 0 ? 'cero' : 'neg' }}"><td>Descuentos</td><td>{{ $m($descuentos) }}</td></tr>
                <tr class="{{ $gastXprov == 0 ? 'cero' : 'neg' }}"><td>Gastos x proveedores</td><td>{{ $m($gastXprov) }}</td></tr>
                <tr class="{{ $gastXserv == 0 ? 'cero' : 'neg' }}"><td>Gastos x servicios</td><td>{{ $m($gastXserv) }}</td></tr>
                <tr class="{{ $gastosVarios == 0 ? 'cero' : 'neg' }}"><td>Gastos varios</td><td>{{ $m($gastosVarios) }}</td></tr>
                <tr class="{{ $retiros == 0 ? 'cero' : 'neg' }}"><td>Retiros</td><td>{{ $m($retiros) }}</td></tr>
                <tr class="total"><td>Total del turno</td><td>{{ $m($totalGeneral) }}</td></tr>
            </table>
        </div>
    </div>

    <div class="rc-card">
        <h3>Productos vendidos</h3>
        @if(count($productos))
            <table class="rc-list rc-prod">
                <thead>
                    <tr><th>Producto</th><th class="r">Cant.</th><th class="r">Total</th></tr>
                </thead>
                <tbody>
                    @foreach($productos as $p)
                        <tr>
                            <td>
                                {{ $p['nombre'] }}
                                @if($p['talle'] || $p['color'])
                                    <span class="sub">· {{ trim(($p['talle'] ? 'T. '.$p['talle'] : '') . ' ' . ($p['color'] ?? '')) }}</span>
                                @endif
                            </td>
                            <td class="r">{{ rtrim(rtrim(number_format($p['cantidad'], 3, ',', '.'), '0'), ',') }}</td>
                            <td class="r">{{ $m($p['total']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="rc-empty">No se vendieron productos en este turno.</div>
        @endif
    </div>

    <div class="rc-firma">
        <div>Firma de quien entrega</div>
        <div>Firma de quien recibe</div>
    </div>
</div>
@if(request('imprimir'))
<script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });</script>
@endif
@endsection
