@extends('control.index')

@section('content3')
@php
    $m = function ($v) {
        $txt = number_format((float) $v, 2, ',', '.');
        return '$ ' . (substr($txt, -3) === ',00' ? substr($txt, 0, -3) : $txt);
    };
@endphp

<style>
    .cc { --tinta:#1f2933; --suave:#6b7785; --linea:#e4e8ee; --fondo:#f6f8fb; color: var(--tinta); max-width: 1100px; }
    .cc-head { display:flex; justify-content:space-between; align-items:flex-end; gap:16px; flex-wrap:wrap;
               border-bottom: 2px solid var(--tinta); padding-bottom: 12px; margin: 10px 0 18px; }
    .cc-head h1 { margin:0; font-size: 28px; font-weight: 600; }
    .cc-head .sub { color: var(--suave); font-size: 14px; margin-top: 4px; }
    .cc-card { background:#fff; border:1px solid var(--linea); border-radius: 10px; overflow: hidden; }
    .cc-table { width:100%; border-collapse: collapse; font-size: 15px; }
    .cc-table th { font-size: 12px; text-transform: uppercase; letter-spacing: .05em; color: var(--suave); font-weight: 600;
                   padding: 11px 14px; background: var(--fondo); border-bottom: 1px solid var(--linea); text-align: left; white-space: nowrap; }
    .cc-table td { padding: 11px 14px; border-bottom: 1px solid var(--linea); vertical-align: middle; font-variant-numeric: tabular-nums; }
    .cc-table tr:last-child td { border-bottom: 0; }
    .cc-table tr:hover td { background: #fafbfd; }
    .cc-table .r { text-align: right; }
    .cc-table .c { text-align: center; }
    .cc-fecha { font-weight: 600; }
    .cc-hora { color: var(--suave); font-size: 13px; }
    .cc-num { color: var(--suave); }
    .cc-efec { font-weight: 700; }
    .cc-acc { white-space: nowrap; text-align: right; }
    .cc-acc .btn { padding: 5px 10px; }
    .cc-empty { padding: 30px 16px; text-align:center; color: var(--suave); }
    .cc-pag { margin-top: 14px; }
</style>

<div class="cc">
    <div class="cc-head">
        <div>
            <h1>{{ $titulo }}</h1>
            <div class="sub">Historial de cierres. Podés ver el detalle de cada uno o volver a imprimirlo.</div>
        </div>
    </div>

    <div class="cc-card">
        @if($cierres->count())
            <table class="cc-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Turno</th>
                        <th>Cerró</th>
                        <th class="c">Ventas</th>
                        <th class="r">Efectivo en caja</th>
                        <th class="r">Total del turno</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cierres as $c)
                        <tr>
                            <td class="cc-num">{{ $c->id }}</td>
                            <td class="cc-fecha">{{ date('d/m/Y', strtotime($c->cierre)) }}</td>
                            <td class="cc-hora">
                                {{ $c->apertura ? date('H:i', strtotime($c->apertura)) : '--:--' }}
                                &rarr; {{ date('H:i', strtotime($c->cierre)) }}
                                @if($c->apertura && date('Y-m-d', strtotime($c->apertura)) != date('Y-m-d', strtotime($c->cierre)))
                                    <br><small>(abrió el {{ date('d/m', strtotime($c->apertura)) }})</small>
                                @endif
                            </td>
                            <td>{{ $c->cerrado_por }}</td>
                            <td class="c">{{ $c->cant_ventas }}</td>
                            <td class="r cc-efec">{{ $m($c->total_efectivo) }}</td>
                            <td class="r">{{ $m($c->total_turno) }}</td>
                            <td class="cc-acc">
                                <a href="{{ route('control.caja.cierres.show', $c->id) }}" class="btn btn-success" title="Ver">
                                    <span class="oi oi-eye"></span>
                                </a>
                                <a href="{{ route('control.caja.cierres.show', $c->id) }}?imprimir=1" target="_blank" class="btn btn-primary" title="Imprimir">
                                    <span class="oi oi-print"></span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="cc-empty">Todavía no hay cierres registrados. Van a aparecer acá a partir del próximo cierre de caja.</div>
        @endif
    </div>

    <div class="cc-pag">
        {{ $cierres->links('pagination::default') }}
    </div>
</div>
@endsection
