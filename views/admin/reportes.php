<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reportes mensuales — Karaoke Zona Mix</title>
<?= themeHeadTag() ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<?= cssTag() ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="<?= htmlspecialchars(asset('js/chart-theme.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.4/dist/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/admin_sidebar.php'; ?>
<div class="admin-content">
 <div class="admin-topbar flex-wrap gap-2"><div><h4 class="mb-1">📊 Reportes mensuales</h4><small class="text-muted">Resumen financiero, pagos reales, usuarios, las 13 salas, días y horarios.</small></div>
 <div class="d-flex flex-wrap gap-2 no-print"><button type="button" onclick="exportarExcel()" class="btn btn-outline-primary btn-sm">⬇ Exportar Excel (.xlsx)</button><button type="button" onclick="descargarPDF()" class="btn btn-primary btn-sm">⬇ Descargar reporte PDF</button></div></div>
 <form class="card report-card p-3 filters no-print" method="get" action="<?= BASE_URL ?>/index.php"><input type="hidden" name="r" value="admin/reportes"><div class="row g-3 align-items-end">
 <div class="col-sm-4 col-lg-3"><label class="form-label fw-semibold" for="mes">Mes</label><select class="form-select" name="mes" id="mes"><?php foreach([1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'] as $num=>$nombre): if($anio < (int)date('Y') || $num <= (int)date('n')): ?><option value="<?= $num ?>" <?= $mes===$num?'selected':'' ?>><?= $nombre ?></option><?php endif; endforeach; ?></select></div>
 <div class="col-sm-4 col-lg-3"><label class="form-label fw-semibold" for="anio">Año</label><select class="form-select" name="anio" id="anio"><?php for($y=2000;$y<=(int)date('Y');$y++): ?><option value="<?= $y ?>" <?= $anio===$y?'selected':'' ?>><?= $y ?></option><?php endfor; ?></select></div>
 <div class="col-sm-4 col-lg-3"><button type="submit" class="btn btn-primary w-100">Generar reporte</button></div><div class="col-lg-3"><a class="btn btn-outline-secondary w-100" href="<?= BASE_URL ?>/index.php?r=admin/reportes">Mes actual</a></div></div>
 <div class="alert alert-info mt-3 mb-0 py-2"><strong>Periodo del reporte:</strong> <?= htmlspecialchars($mesNombre.' '.$anio) ?> (<?= date('d/m/Y',strtotime($desde)) ?> al <?= date('d/m/Y',strtotime($hasta)) ?>). Incluye todas las reservas cuya fecha cae dentro del mes.</div></form>
 <?php $n=fn($k)=>(int)($resumen[$k]??0); ?>
 <div id="reporteContenido">
 <h5 class="mb-3">A) Resumen general — <?= htmlspecialchars($mesNombre.' '.$anio) ?></h5>
 <div class="row g-3 mb-4">
 <div class="col-6 col-xl-3"><div class="metric purple"><div class="k">Total de reservas</div><div class="v"><?= number_format($n('total'),0,',','.') ?></div><div class="s">Todos los estados</div></div></div>
 <div class="col-6 col-xl-3"><div class="metric green"><div class="k">Dinero realmente recibido</div><div class="v"><?= $money($resumen['dinero_recibido']??0) ?></div><div class="s">Suma de reservas del mes</div></div></div>
 <div class="col-6 col-xl-3"><div class="metric blue"><div class="k">Efectivo recibido</div><div class="v"><?= $money($resumen['recibido_efectivo']??0) ?></div><div class="s">Método efectivo</div></div></div>
 <div class="col-6 col-xl-3"><div class="metric purple"><div class="k">QR recibido</div><div class="v"><?= $money($resumen['recibido_qr']??0) ?></div><div class="s">Se identifica con transferencia</div></div></div>
 <div class="col-6 col-xl-3"><div class="metric orange"><div class="k">Pendientes</div><div class="v"><?= $n('pendientes') ?> <small>(<?= number_format((float)$resumen['pendientes_pct'],2,',','.') ?>%)</small></div></div></div>
 <div class="col-6 col-xl-3"><div class="metric green"><div class="k">Confirmadas</div><div class="v"><?= $n('confirmadas') ?> <small>(<?= number_format((float)$resumen['confirmadas_pct'],2,',','.') ?>%)</small></div></div></div>
 <div class="col-6 col-xl-3"><div class="metric blue"><div class="k">Finalizadas</div><div class="v"><?= $n('finalizadas') ?> <small>(<?= number_format((float)$resumen['finalizadas_pct'],2,',','.') ?>%)</small></div></div></div>
 <div class="col-6 col-xl-3"><div class="metric red"><div class="k">Canceladas</div><div class="v"><?= $n('canceladas') ?> <small>(<?= number_format((float)$resumen['canceladas_pct'],2,',','.') ?>%)</small></div></div></div>
 </div>
 <div class="row g-3 mb-4">
 <div class="col-md-4"><div class="card report-card h-100"><div class="card-header">Método de pago: efectivo vs QR</div><div class="chart-wrap"><canvas id="chartMetodos" aria-label="Distribución de reservas por método de pago"></canvas></div><div class="small-note px-3 pb-3">Basado en <code>reservas.metodo_pago</code>, solo con porcentaje elegido de 50 % o 100 %. Transferencia se presenta como QR.</div></div></div>
 <div class="col-md-4"><div class="card report-card h-100"><div class="card-header">Porcentaje de pago elegido</div><div class="chart-wrap"><canvas id="chartPorcentajePago" aria-label="Distribución de reservas por porcentaje de pago"></canvas></div><div class="small-note px-3 pb-3">Usa el porcentaje guardado; si falta, lo identifica solo cuando el monto recibido coincide con la mitad o el total. Los demás quedan como sin elegir.</div></div></div>
 <div class="col-md-4"><div class="card report-card h-100"><div class="card-header">Estado de reservas</div><div class="chart-wrap"><canvas id="chartEstados" aria-label="Reservas confirmadas, pendientes, canceladas y rechazadas"></canvas></div><div class="small-note px-3 pb-3">Incluye pendientes, confirmadas, canceladas y rechazadas.</div></div></div>
 </div>
 <div class="card report-card mb-4"><div class="card-header">Datos por estado de reserva</div><div class="table-responsive"><table class="table table-striped report-table"><thead><tr><th>Estado</th><th>Cantidad</th><th>Porcentaje</th></tr></thead><tbody><?php foreach($porEstado as $e): ?><tr><td><?= htmlspecialchars($e['etiqueta']) ?></td><td><?= (int)$e['total'] ?></td><td><?= number_format((float)$e['porcentaje'],2,',','.') ?>%</td></tr><?php endforeach; ?></tbody></table></div><div class="small-note p-3">Los porcentajes usan como denominador todas las reservas del mes, incluidas las canceladas.</div></div>
 <div class="card report-card mb-4"><div class="card-header">Datos por método de pago</div><div class="table-responsive"><table class="table table-striped report-table"><thead><tr><th>Método</th><th>Reservas</th><th>Pago 50 %</th><th>Pago 100 %</th><th>Dinero recibido</th></tr></thead><tbody><?php foreach($pagosMetodo as $pm): ?><tr><td><?= htmlspecialchars($pm['metodo']) ?></td><td><?= (int)$pm['reservas'] ?></td><td><?= (int)$pm['cantidad_50'] ?></td><td><?= (int)$pm['cantidad_100'] ?></td><td><?= $money($pm['dinero_recibido']) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
 <div class="card report-card mb-4"><div class="card-header">Datos del porcentaje de pago</div><div class="table-responsive"><table class="table table-striped report-table"><thead><tr><th>Porcentaje elegido</th><th>Cantidad de reservas</th><th>Porcentaje del total</th></tr></thead><tbody><?php
 $clasificarPorcentaje = static function(array $r): ?int {
   $p=(int)($r['porcentaje_pago']??0); if(in_array($p,[50,100],true)) return $p;
   $total=(float)($r['total']??0); $pagado=(float)($r['monto_pagado']??0);
   if($total>0 && $pagado>0 && $pagado >= $total) return 100;
   if($total>0 && $pagado>0 && abs($pagado-ceil($total/2))<0.01) return 50;
   return null;
 };
 $cant50=count(array_filter($porReserva,static fn($r)=>$clasificarPorcentaje($r)===50));
 $cant100=count(array_filter($porReserva,static fn($r)=>$clasificarPorcentaje($r)===100));
 $cantSin=max(0,count($porReserva)-$cant50-$cant100);
 foreach([['50 %',$cant50],['100 %',$cant100],['Sin elegir / no identificable',$cantSin]] as [$et,$cant]): ?><tr><td><?= $et ?></td><td><?= $cant ?></td><td><?= count($porReserva)?number_format($cant*100/count($porReserva),2,',','.'): '0,00' ?>%</td></tr><?php endforeach; ?></tbody></table></div></div>

 <h5 class="mt-4 mb-3">B) Reporte de usuarios</h5>
 <div class="card report-card"><div class="card-header">Gráfica del ranking mensual de usuarios</div><div class="chart-wrap"><canvas id="chartUsuarios" aria-label="Usuarios con más reservas"></canvas></div></div>
 <div class="card report-card"><div class="card-header">Todos los usuarios con reservas, ordenados de mayor a menor</div><div class="table-responsive"><table class="table table-striped report-table"><thead><tr><th>#</th><th>Usuario</th><th>Correo</th><th>Reservas</th><th>Valor total de reservas</th><th>Dinero recibido</th></tr></thead><tbody><?php if(!$top10): ?><tr><td colspan="6" class="text-center text-muted">No hay reservas de clientes este mes.</td></tr><?php endif; ?><?php foreach($top10 as $i=>$u): ?><tr><td><?= $i+1 ?></td><td><?= htmlspecialchars($u['usuario']) ?></td><td><?= htmlspecialchars($u['email']) ?></td><td><strong><?= (int)$u['reservas'] ?></strong></td><td><?= $money($u['monto_total']) ?></td><td><?= $money($u['dinero_recibido']) ?></td></tr><?php endforeach; ?></tbody></table></div></div>


 <h5 class="mt-4 mb-3">C) Ranking completo de las 13 salas</h5>
 <div class="card report-card"><div class="card-header">Todas las salas, ordenadas por reservas</div><div class="chart-wrap"><canvas id="chartSalas" aria-label="Ranking de reservas por sala"></canvas></div><div class="table-responsive"><table class="table table-striped report-table"><thead><tr><th>Posición</th><th>Sala</th><th>Estado</th><th>Reservas</th><th>Valor de reservas</th><th>Dinero recibido</th><th>% del total de reservas</th><th>% del ingreso recibido</th></tr></thead><tbody><?php if(!$porSala): ?><tr><td colspan="8" class="text-center text-danger">No hay salas registradas; se esperaban 13 salas.</td></tr><?php endif; ?><?php foreach($porSala as $i=>$s): ?><tr><td><?= $i+1 ?></td><td><strong><?= htmlspecialchars($s['nombre']) ?></strong></td><td><span class="status-pill <?= (int)$s['activa']===1?'active-pill':'maint-pill' ?>"><?= htmlspecialchars($s['estado_sala']) ?></span></td><td><?= (int)$s['reservas'] ?></td><td><?= $money($s['valor_reservas']) ?></td><td><?= $money($s['ingresos']) ?></td><td><?= number_format((float)$s['porcentaje_reservas'],2,',','.') ?>%</td><td><?= number_format((float)$s['porcentaje_ingresos'],2,',','.') ?>%</td></tr><?php endforeach; ?></tbody></table></div><div class="small-note p-3">Salas con 0 reservas aparecen al final. En este sistema, <code>activa=0</code> se interpreta como mantenimiento; la base no distingue mantenimiento de una inactivación por otro motivo. <?= $salaTop ? 'Sala más reservada: '.htmlspecialchars($salaTop['nombre']).' ('.(int)$salaTop['reservas'].' reservas).' : 'Ninguna sala recibió reservas este mes.' ?></div></div>
 <div class="card report-card"><div class="card-header">Usuarios por cada sala (cantidad y monto total)</div><?php foreach($porSala as $s): $usuariosSala=$usuariosPorSala[(int)$s['sala_id']]??[]; ?><div class="room-title"><?= htmlspecialchars($s['nombre']) ?> — <?= (int)$s['reservas'] ?> reserva(s) · <?= htmlspecialchars($s['estado_sala']) ?></div><?php if(!$usuariosSala): ?><div class="p-3 small-note">Sin reservas este mes.</div><?php else: ?><div class="table-responsive"><table class="table table-striped report-table"><thead><tr><th>Usuario</th><th>Correo</th><th>Reservas en esta sala</th><th>Valor total reservado</th><th>Dinero recibido</th></tr></thead><tbody><?php foreach($usuariosSala as $u): ?><tr><td><?= htmlspecialchars($u['usuario']) ?></td><td><?= htmlspecialchars($u['email']) ?></td><td><?= (int)$u['reservas'] ?></td><td><?= $money($u['monto_total']) ?></td><td><?= $money($u['dinero_recibido']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?><?php endforeach; ?></div>

 <h5 class="mt-4 mb-3">D) Día de la semana con más reservas</h5><div class="card report-card"><div class="card-header">Reservas por día (lunes a domingo)</div><div class="chart-wrap"><canvas id="chartDias" aria-label="Reservas por día de la semana"></canvas></div><div class="table-responsive"><table class="table table-striped report-table"><thead><tr><th>Día</th><th>Reservas</th></tr></thead><tbody><?php foreach($porDia as $d): ?><tr><td><?= htmlspecialchars($d['dia']) ?></td><td><?= (int)$d['total'] ?></td></tr><?php endforeach; ?></tbody></table></div><div class="small-note p-3"><?php if($diasTop): ?>Día(s) con más reservas: <strong><?= htmlspecialchars(implode(', ',array_column($diasTop,'dia'))) ?></strong> (<?= (int)$diasTop[0]['total'] ?>).<?php else: ?>No hay reservas en el mes seleccionado.<?php endif; ?></div></div>

 <h5 class="mt-4 mb-3">E) Horario con más reservas</h5><div class="card report-card"><div class="card-header">Reservas por franja de inicio de una hora</div><div class="chart-wrap"><canvas id="chartHoras" aria-label="Reservas por franja horaria"></canvas></div><div class="table-responsive"><table class="table table-striped report-table"><thead><tr><th>Franja de inicio</th><th>Reservas</th></tr></thead><tbody><?php if(!$porHorario): ?><tr><td colspan="2" class="text-center text-muted">No hay reservas en este mes.</td></tr><?php endif; ?><?php foreach($porHorario as $h): ?><tr><td><?= htmlspecialchars($h['franja']) ?></td><td><?= (int)$h['total'] ?></td></tr><?php endforeach; ?></tbody></table></div><div class="small-note p-3"><?php if($horasTop): ?>Franja(s) con más reservas: <strong><?= htmlspecialchars(implode(', ',array_column($horasTop,'franja'))) ?></strong> (<?= (int)$horasTop[0]['total'] ?>).<?php else: ?>No hay reservas en el mes seleccionado.<?php endif; ?></div></div>

 <h5 class="mt-4 mb-3">Detalle completo para auditoría</h5><div class="card report-card"><div class="card-header">Todas las reservas del mes, más recientes primero</div><div class="table-responsive"><table class="table table-striped report-table"><thead><tr><th>ID</th><th>Fecha</th><th>Horario</th><th>Usuario</th><th>Correo</th><th>Sala</th><th>Estado</th><th>Método</th><th>Plan elegido</th><th>Total</th><th>Recibido</th><th>Saldo</th><th>Estado de pago real</th></tr></thead><tbody><?php if(!$porReserva): ?><tr><td colspan="13" class="text-center text-muted">No hay reservas en el mes.</td></tr><?php endif; ?><?php foreach($porReserva as $r): ?><tr><td><?= (int)$r['reserva_id'] ?></td><td class="nowrap"><?= date('d/m/Y',strtotime($r['fecha'])) ?></td><td class="nowrap"><?= substr($r['hora_inicio'],0,5) ?>–<?= substr($r['hora_fin'],0,5) ?></td><td><?= htmlspecialchars($r['usuario']) ?></td><td><?= htmlspecialchars($r['email']) ?></td><td><?= htmlspecialchars($r['sala']) ?></td><td><?= htmlspecialchars(ucfirst($r['estado'])) ?></td><td><?= htmlspecialchars($r['metodo_pago']==='transferencia'?'QR / transferencia':ucfirst($r['metodo_pago'])) ?></td><td><?= $r['porcentaje_pago']===null ? 'Sin elegir' : (int)$r['porcentaje_pago'].'%' ?></td><td><?= $money($r['total']) ?></td><td><?= $money($r['monto_pagado']) ?></td><td><?= $money($r['saldo']) ?></td><td><?= htmlspecialchars($r['pago_estado']) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
 </div></div>
<script>
const reportData = {
 month: <?= json_encode($mesNombre.' '.$anio,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>,
 summary: <?= json_encode($resumen,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>,
 statuses: <?= json_encode($porEstado,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>,
 paymentPercentages: <?= json_encode(['50 %'=>$cant50 ?? 0,'100 %'=>$cant100 ?? 0,'Sin elegir'=>$cantSin ?? 0],JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>,
 methods: <?= json_encode($pagosMetodo,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>,
 top10: <?= json_encode($top10,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>,
 loyalty: <?= json_encode($usuariosFidelizacion,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>,
 rooms: <?= json_encode($porSala,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>,
 roomUsers: <?= json_encode($usuariosPorSala,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>,
 days: <?= json_encode($porDia,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>,
 hours: <?= json_encode($porHorario,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>,
 reservations: <?= json_encode($porReserva,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>
};
window.addEventListener('beforeprint',()=>{ if(window.Chart) Object.values(Chart.instances).forEach(c=>{c.resize();}); });
window.addEventListener('afterprint',()=>{ if(window.Chart) Object.values(Chart.instances).forEach(c=>{c.resize();}); });
if(window.Chart) Chart.defaults.animation = false;
kzmApplyChartTheme();
const fmtMoney = n => 'Bs '+Number(n||0).toLocaleString('es-BO',{minimumFractionDigits:2,maximumFractionDigits:2});
const methods = reportData.methods;
const metodoEfectivo=methods.find(x=>String(x.metodo_pago||'').trim().toLowerCase()==='efectivo')||{}; const metodoQR=methods.find(x=>['transferencia','qr','transferencia qr'].includes(String(x.metodo_pago||'').trim().toLowerCase()))||{};
function pieOptions(){return {responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{generateLabels:kzmPieLegend,font:{size:12,weight:'bold'},boxWidth:14}},tooltip:{callbacks:{label:c=>c.label+': '+Number(c.raw||0)+' reserva(s)'}}}};}
const axisTitle=t=>({display:true,text:t,font:{weight:'bold',size:12}});
const barH=yT=>({responsive:true,maintainAspectRatio:false,indexAxis:'y',layout:{padding:{right:28}},plugins:{legend:{display:false}},scales:{x:{beginAtZero:true,grace:'12%',ticks:{precision:0},title:axisTitle('Cantidad de reservas')},y:{ticks:{autoSkip:false,font:{size:12,weight:'bold'}},title:axisTitle(yT)}}});
const barV=xT=>({responsive:true,maintainAspectRatio:false,layout:{padding:{top:22}},plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grace:'12%',ticks:{precision:0},title:axisTitle('Cantidad de reservas')},x:{ticks:{autoSkip:false,maxRotation:60,minRotation:0,font:{size:11}},title:axisTitle(xT)}}});
function tallWrap(id,rows){const w=document.getElementById(id).closest('.chart-wrap');w.classList.add('chart-tall');const h=Math.max(260,rows*32+100);w.style.height=h+'px';w.style.minHeight=h+'px';}
tallWrap('chartUsuarios',reportData.top10.length);tallWrap('chartSalas',reportData.rooms.length);
new Chart(document.getElementById('chartMetodos'),{type:'pie',data:{labels:['Efectivo','Transferencia QR'],datasets:[{data:[Number(metodoEfectivo.reservas||0),Number(metodoQR.reservas||0)],backgroundColor:kzmChartColors(['success','primary']),borderColor:kzmChartColor('surface'),borderWidth:2}]},options:pieOptions()});
new Chart(document.getElementById('chartPorcentajePago'),{type:'pie',data:{labels:['Pago del 50 %','Pago del 100 %','Sin elegir / no identificable'],datasets:[{data:[Number(reportData.paymentPercentages['50 %']||0),Number(reportData.paymentPercentages['100 %']||0),Number(reportData.paymentPercentages['Sin elegir']||0)],backgroundColor:kzmChartColors(['warning','info','neutral']),borderColor:kzmChartColor('surface'),borderWidth:2}]},options:pieOptions()});
const statusOrder=['pendiente','confirmada','cancelada','rechazada']; const statusRows=statusOrder.map(k=>reportData.statuses.find(x=>String(x.estado).toLowerCase()===k)).filter(Boolean);
new Chart(document.getElementById('chartEstados'),{type:'pie',data:{labels:statusRows.map(x=>x.etiqueta),datasets:[{data:statusRows.map(x=>Number(x.total||0)),backgroundColor:kzmChartColors(['warning','success','danger','primary']),borderColor:kzmChartColor('surface'),borderWidth:2}]},options:pieOptions()});

new Chart(document.getElementById('chartUsuarios'),{type:'bar',data:{labels:reportData.top10.map(x=>x.usuario),datasets:[{label:'Reservas',backgroundColor:kzmChartColor('primary'),data:reportData.top10.map(x=>Number(x.reservas))}]},options:barH('Usuario')});
new Chart(document.getElementById('chartSalas'),{type:'bar',data:{labels:reportData.rooms.map(x=>x.nombre),datasets:[{label:'Reservas',backgroundColor:kzmChartColor('primary'),data:reportData.rooms.map(x=>Number(x.reservas))}]},options:barH('Sala')});
new Chart(document.getElementById('chartDias'),{type:'bar',data:{labels:reportData.days.map(x=>x.dia),datasets:[{label:'Reservas',backgroundColor:kzmChartColor('primary'),data:reportData.days.map(x=>Number(x.total))}]},options:barV('Día de la semana')});
new Chart(document.getElementById('chartHoras'),{type:'bar',data:{labels:reportData.hours.map(x=>x.franja),datasets:[{label:'Reservas',backgroundColor:kzmChartColor('primary'),data:reportData.hours.map(x=>Number(x.total))}]},options:barV('Franja horaria de inicio')});
function descargarPDF(){
 if(!window.jspdf||!window.jspdf.jsPDF){alert('No se pudo cargar el generador PDF. Comprueba tu conexión e inténtalo de nuevo.');return;}
 const {jsPDF}=window.jspdf; const doc=new jsPDF({orientation:'landscape',unit:'mm',format:'a4',compress:true});
 const pageW=doc.internal.pageSize.getWidth(), pageH=doc.internal.pageSize.getHeight(), margin=9; let y=12;
 const paper=kzmPaperColors().rgb, purple=paper.brand, gray=paper.muted;
 const chartIds=['chartEstados','chartMetodos','chartPorcentajePago','chartSalas','chartDias','chartHoras','chartUsuarios']; const imgs=kzmCaptureCharts(chartIds);
 function title(t){if(y>pageH-20){doc.addPage();y=12;}doc.setFont('helvetica','bold');doc.setFontSize(12.5);doc.setTextColor(0,0,0);doc.text(t,margin,y);y+=6;}
 function table(head,rows,opts={}){if(!doc.autoTable){alert('No se pudo cargar el módulo de tablas PDF. Comprueba tu conexión e inténtalo de nuevo.');return;}doc.autoTable({head:[head],body:rows,startY:y,margin:{left:margin,right:margin,top:12,bottom:10},theme:'grid',styles:{font:'helvetica',fontSize:8.5,cellPadding:2,overflow:'linebreak',valign:'middle',textColor:[0,0,0],lineColor:paper.line,lineWidth:.2},headStyles:{fillColor:paper.headFill,textColor:[0,0,0],fontStyle:'bold',fontSize:8.5,lineColor:paper.line,lineWidth:.2},alternateRowStyles:{fillColor:paper.altRow},rowPageBreak:'avoid',pageBreak:'auto',horizontalPageBreak:true,horizontalPageBreakRepeat:[0],...opts});y=doc.lastAutoTable.finalY+7;}
 function addChart(id,label,w=82,h=49){const c=document.getElementById(id);if(!c)return;if(c.width&&c.height)h=Math.min(w*c.height/c.width,pageH-32);if(y+h+10>pageH){doc.addPage();y=12;}title(label);try{const img=imgs[id];if(!img)throw new Error('sin imagen');doc.addImage(img,'PNG',margin,y,w,h,undefined,'FAST');y+=h+5;}catch(e){doc.setFontSize(8);doc.setTextColor(...gray);doc.text('Gráfica no disponible en este navegador.',margin,y+4);y+=9;}}
 doc.setFont('helvetica','bold');doc.setFontSize(18);doc.setTextColor(0,0,0);doc.text('Karaoke Zona Mix — Reporte administrativo',margin,y);y+=8;doc.setFont('helvetica','normal');doc.setFontSize(10.5);doc.setTextColor(0,0,0);doc.text('Periodo: '+reportData.month+' | Generado: '+new Date().toLocaleDateString('es-BO'),margin,y);y+=8;
 title('Resumen general');table(['Indicador','Valor'],Object.entries(reportData.summary).map(([k,v])=>[String(k).replaceAll('_',' '),typeof v==='number'?v.toLocaleString('es-BO'):String(v??'')]));
 // Dos gráficas de pagos y una de estados, en un mismo bloque de página para evitar escalas enormes.
 if(y+75>pageH){doc.addPage();y=12;} title('Distribución de pagos y estados');
 const chartY=y; const chartW=84, chartH=Math.min(62,Math.round(84*(document.getElementById('chartEstados').height||260)/(document.getElementById('chartEstados').width||380))); [['chartEstados','Estado de reservas'],['chartMetodos','Método de pago'],['chartPorcentajePago','Porcentaje de pago']].forEach((it,i)=>{const c=document.getElementById(it[0]);if(c){doc.setFont('helvetica','bold');doc.setFontSize(9.5);doc.setTextColor(0,0,0);doc.text(it[1],margin+i*92, y);try{doc.addImage(imgs[it[0]],'PNG',margin+i*92,y+3,chartW,chartH,undefined,'FAST');}catch(e){}}});y=chartY+chartH+10;
 title('Estado de las reservas');table(['Estado','Cantidad','Porcentaje'],reportData.statuses.map(x=>[x.etiqueta,x.total,Number(x.porcentaje||0).toFixed(2)+' %']));
 title('Métodos de pago');table(['Método','Reservas con 50 % o 100 %','Pago 50 %','Monto recibido al 50 %','Pago 100 %','Monto recibido al 100 %','Total recibido'],reportData.methods.map(x=>[x.metodo,x.reservas,x.cantidad_50,fmtMoney(x.monto_50),x.cantidad_100,fmtMoney(x.monto_100),fmtMoney(x.dinero_recibido)]));
 title('Porcentaje de pago');table(['Porcentaje elegido','Cantidad de reservas'],[['50 %',reportData.paymentPercentages['50 %']],['100 %',reportData.paymentPercentages['100 %']],['Sin elegir',reportData.paymentPercentages['Sin elegir']]]);
 title('Ranking gráfico de usuarios'); addChart('chartUsuarios','Usuarios con más reservas',180,70);
 title('Usuarios con reservas — ordenados de mayor a menor');table(['#','Usuario','Correo','Reservas','Valor total','Recibido'],reportData.top10.map((x,i)=>[i+1,x.usuario,x.email,x.reservas,fmtMoney(x.monto_total),fmtMoney(x.dinero_recibido)]));
 addChart('chartSalas','Gráfica: salas más reservadas',180,70);
 title('Ranking de salas');table(['#','Sala','Estado','Reservas','Valor reservado','Recibido','% reservas','% ingresos'],reportData.rooms.map((x,i)=>[i+1,x.nombre,x.estado_sala,x.reservas,fmtMoney(x.valor_reservas),fmtMoney(x.ingresos),Number(x.porcentaje_reservas||0).toFixed(2)+' %',Number(x.porcentaje_ingresos||0).toFixed(2)+' %']));
 addChart('chartDias','Gráfica: reservas por día de la semana',180,70); title('Reservas por día de la semana');table(['Día','Reservas'],reportData.days.map(x=>[x.dia,x.total]));
 addChart('chartHoras','Gráfica: reservas por hora',180,70); title('Reservas por horario');table(['Franja horaria','Reservas'],reportData.hours.map(x=>[x.franja,x.total]));
 title('Detalle de todas las reservas');table(['ID','Fecha','Horario','Usuario','Correo','Sala','Estado','Método','Pago %','Total','Recibido','Saldo','Estado pago'],reportData.reservations.map(x=>[x.reserva_id,x.fecha,(String(x.hora_inicio||'').slice(0,5)+'-'+String(x.hora_fin||'').slice(0,5)),x.usuario,x.email,x.sala,x.estado,x.metodo_pago==='transferencia'?'QR':x.metodo_pago,(x.porcentaje_pago==null?'Sin elegir':x.porcentaje_pago+' %'),fmtMoney(x.total),fmtMoney(x.monto_pagado),fmtMoney(x.saldo),x.pago_estado]),{styles:{fontSize:7,cellPadding:1.5,textColor:[0,0,0]},headStyles:{fontSize:7,textColor:[0,0,0],fillColor:paper.headFill,fontStyle:'bold'},horizontalPageBreak:true,horizontalPageBreakRepeat:[0,1]});
 const pages=doc.getNumberOfPages();for(let i=1;i<=pages;i++){doc.setPage(i);doc.setFont('helvetica','normal');doc.setFontSize(8);doc.setTextColor(0,0,0);doc.text('Karaoke Zona Mix · '+reportData.month,margin,pageH-4);doc.text('Página '+i+' de '+pages,pageW-margin,pageH-4,{align:'right'});}
 doc.save('reporte_karaoke_'+<?= json_encode($anio.'-'.str_pad((string)$mes,2,'0',STR_PAD_LEFT)) ?>+'.pdf');
}

async function exportarExcel(){
 if(!window.ExcelJS){alert('No se pudo cargar el generador Excel. Comprueba tu conexión.');return;}
 const paper=kzmPaperColors().argb; const chartIds=['chartEstados','chartMetodos','chartPorcentajePago','chartSalas','chartDias','chartHoras','chartUsuarios']; const imgs=kzmCaptureCharts(chartIds);
 const wb=new ExcelJS.Workbook();wb.creator='Karaoke Zona Mix';wb.created=new Date();
 const ws=wb.addWorksheet('Reporte mensual');ws.columns=[{width:28},{width:24},{width:20},{width:20},{width:20},{width:20},{width:20},{width:20}];
 const BLACK={argb:'FF000000'};const thin={style:'thin',color:{argb:'FF6E6E6E'}};const box={top:thin,left:thin,bottom:thin,right:thin};
 const header=(sh,title)=>{sh.addRow([title]);sh.getRow(sh.lastRow.number).font={bold:true,size:14,color:BLACK};};
 const addTable=(sh,title,heads,rows)=>{header(sh,title);const h=sh.addRow(heads);h.font={bold:true,size:11,color:BLACK};h.alignment={vertical:'middle',wrapText:true};h.fill={type:'pattern',pattern:'solid',fgColor:{argb:paper.headFill}};h.eachCell(c=>{c.border=box;});rows.forEach(r=>{const row=sh.addRow(r);row.font={size:11,color:BLACK};row.alignment={vertical:'middle',wrapText:true};row.eachCell({includeEmpty:true},(c,n)=>{if(n<=heads.length)c.border=box;});});sh.addRow([]);};
 addTable(ws,'PERIODO',['Periodo','Generado'],[[reportData.month,new Date().toLocaleDateString('es-BO')]]);
 const sections=[
  ['ESTADOS',['Estado','Cantidad','Porcentaje'],reportData.statuses.map(x=>[x.etiqueta,x.total,x.porcentaje+'%'])],
  ['METODO DE PAGO',['Método','Reservas','Pago 50%','Pago 100%','Recibido'],reportData.methods.map(x=>[x.metodo,x.reservas,x.cantidad_50,x.cantidad_100,x.dinero_recibido])],
  ['PORCENTAJE DE PAGO',['Porcentaje','Cantidad'],[['50 %',reportData.paymentPercentages['50 %']],['100 %',reportData.paymentPercentages['100 %']],['Sin elegir',reportData.paymentPercentages['Sin elegir']]]],
  ['SALAS',['Sala','Reservas','Valor reservado','Recibido'],reportData.rooms.map(x=>[x.nombre,x.reservas,x.valor_reservas,x.ingresos])],
  ['DIAS',['Día','Reservas'],reportData.days.map(x=>[x.dia,x.total])],
  ['HORAS',['Franja horaria','Reservas'],reportData.hours.map(x=>[x.franja,x.total])],
  ['RANKING USUARIOS',['Posición','Usuario','Correo','Reservas','Monto total','Recibido'],reportData.top10.map((x,i)=>[i+1,x.usuario,x.email,x.reservas,x.monto_total,x.dinero_recibido])]
 ];

 for(let i=0;i<sections.length;i++){const [name,heads,rows]=sections[i];const sh=wb.addWorksheet(name.slice(0,31));sh.columns=heads.map((_,j)=>({width:j===0?28:20}));addTable(sh,name,heads,rows);if(imgs[chartIds[i]]){const img=wb.addImage({base64:imgs[chartIds[i]],extension:'png'});const cv=document.getElementById(chartIds[i]);sh.addImage(img,{tl:{col:0,row:sh.lastRow.number+1},ext:{width:700,height:Math.round(700*cv.height/cv.width)}});}}
 const detalle=wb.addWorksheet('Detalle reservas');detalle.columns=Array.from({length:14},(_,i)=>({width:i===3?28:18}));addTable(detalle,'DETALLE DE RESERVAS',['ID','Fecha','Inicio','Fin','Usuario','Correo','Sala','Estado','Método','Pago %','Total','Recibido','Saldo','Estado pago'],reportData.reservations.map(x=>[x.reserva_id,x.fecha,x.hora_inicio,x.hora_fin,x.usuario,x.email,x.sala,x.estado,x.metodo_pago,x.porcentaje_pago,x.total,x.monto_pagado,x.saldo,x.pago_estado]));
 const buf=await wb.xlsx.writeBuffer();const blob=new Blob([buf],{type:'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});const url=URL.createObjectURL(blob);const a=document.createElement('a');a.href=url;a.download='reporte_karaoke_'+new Date().toISOString().slice(0,7)+'.xlsx';a.click();URL.revokeObjectURL(url);
}

function csvCell(v){let s=String(v??'');if(/^[=+\-@\t\r]/.test(s))s="'"+s;return '"'+s.replace(/"/g,'""')+'"';}
function csvSection(name,head,rows){return [[name],head,...rows].map(row=>row.map(csvCell).join(';')).join('\r\n');}
function exportarCSV(){
 const sections=[];
 sections.push(csvSection('PERIODO',['Periodo','Desde','Hasta'],[[reportData.month,<?= json_encode($desde) ?>,<?= json_encode($hasta) ?>]]));
 sections.push(csvSection('RESUMEN',['Indicador','Valor'],Object.entries(reportData.summary)));
 sections.push(csvSection('ESTADOS',['Estado','Cantidad','Porcentaje'],reportData.statuses.map(x=>[x.etiqueta,x.total,x.porcentaje])));
 sections.push(csvSection('RESERVAS POR METODO DE PAGO',['Método','Reservas con porcentaje de pago 50% o 100%'],reportData.methods.map(x=>[x.metodo,x.reservas])));
 sections.push(csvSection('RANKING DE USUARIOS CON RESERVAS',['Usuario','Correo','Reservas','Monto total','Dinero recibido'],reportData.top10.map(x=>[x.usuario,x.email,x.reservas,x.monto_total,x.dinero_recibido])));
 sections.push(csvSection('FIDELIZACION 0 O 1 RESERVA',['Usuario','Correo','Reservas','Monto total','Dinero recibido'],reportData.loyalty.map(x=>[x.usuario,x.email,x.reservas,x.monto_total,x.dinero_recibido])));
 sections.push(csvSection('13 SALAS',['ID','Sala','Estado','Reservas','Valor reservas','Ingresos recibidos','% reservas','% ingresos'],reportData.rooms.map(x=>[x.sala_id,x.nombre,x.estado_sala,x.reservas,x.valor_reservas,x.ingresos,x.porcentaje_reservas,x.porcentaje_ingresos])));
 const roomUserRows=[];reportData.rooms.forEach(s=>{const arr=reportData.roomUsers[s.sala_id]||[];if(!arr.length)roomUserRows.push([s.nombre,'Sin reservas este mes','','','','']);else arr.forEach(u=>roomUserRows.push([s.nombre,u.usuario,u.email,u.reservas,u.monto_total,u.dinero_recibido]));});
 sections.push(csvSection('USUARIOS POR SALA',['Sala','Usuario','Correo','Reservas en sala','Valor reservado','Dinero recibido'],roomUserRows));
 sections.push(csvSection('DIAS',['Día','Reservas'],reportData.days.map(x=>[x.dia,x.total])));
 sections.push(csvSection('HORARIOS',['Franja','Reservas'],reportData.hours.map(x=>[x.franja,x.total])));
 sections.push(csvSection('DETALLE DE RESERVAS',['ID','Fecha','Inicio','Fin','Usuario','Correo','Sala','Estado','Método','Plan %','Total','Recibido','Saldo','Estado de pago'],reportData.reservations.map(x=>[x.reserva_id,x.fecha,x.hora_inicio,x.hora_fin,x.usuario,x.email,x.sala,x.estado,x.metodo_pago,x.porcentaje_pago,x.total,x.monto_pagado,x.saldo,x.pago_estado])));
 const blob=new Blob(['\ufeff'+sections.join('\r\n\r\n')],{type:'text/csv;charset=utf-8;'});const url=URL.createObjectURL(blob);const a=document.createElement('a');a.href=url;a.download='reporte_karaoke_'+<?= json_encode($anio.'-'.str_pad((string)$mes,2,'0',STR_PAD_LEFT)) ?>+'.csv';document.body.appendChild(a);a.click();a.remove();setTimeout(()=>URL.revokeObjectURL(url),1000);
}
</script></body></html>
