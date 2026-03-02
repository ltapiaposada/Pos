<?php
declare(strict_types=1);

$salida = $argv[1] ?? __DIR__.'/../docs/manual/Manual_Cliente_POS_Espanol_Profesional.pdf';

$contenido = [
['tipo'=>'portada','titulo'=>'Manual de Usuario del Sistema POS','sub'=>'Guia completa para clientes que nunca han usado un sistema de este tipo'],
['tipo'=>'seccion','titulo'=>'1) Como ingresar por primera vez','bloques'=>[
['sub'=>'Ingreso al panel interno','pasos'=>[
'Abra el navegador y escriba la direccion del sistema.',
'Ingrese su correo y contrasena y pulse Ingresar.',
'Si es su primer acceso, entre a Mi perfil y cambie su contrasena.',
'Confirme que vea el menu lateral con sus modulos asignados.'
]],
['sub'=>'Ingreso de cliente a la tienda en linea','pasos'=>[
'Abra la tienda en linea.',
'Si no tiene cuenta, pulse Registrarse y complete el formulario.',
'Inicie sesion con correo y contrasena.',
'Confirme que aparezca su nombre en la parte superior.'
]],
['aviso'=>'Importante','estilo'=>'advertencia','txt'=>'No comparta credenciales. Cada persona debe usar su propia cuenta.']
,
['sub'=>'Buenas practicas al ingresar','lista'=>[
'Verifique que este en la sucursal correcta antes de operar.',
'Revise que la fecha y hora del sistema sean correctas.',
'Si olvido la contrasena, use el proceso de recuperacion y no cree cuentas duplicadas.'
]]
]],
['tipo'=>'seccion','titulo'=>'2) Como entrar a cada vista desde el menu','bloques'=>[
['sub'=>'Recorrido principal','pasos'=>[
'Inicio > Inicio principal.',
'Flujo diario > Ventas y caja > Punto de venta.',
'Flujo diario > Ventas y caja > Facturas.',
'Flujo diario > Ventas y caja > Caja.',
'Flujo diario > Ventas y caja > Devoluciones.',
'Flujo diario > Ventas y caja > Compras.',
'Gestion comercial > Catalogos > Productos, Categorias, Contactos, Sucursales.',
'Control > Operacion y analitica > Inventario y Reportes.',
'Finanzas > Contabilidad > Plan de cuentas, Libro diario, Registrar gasto, Cuentas por cobrar, Cuentas por pagar, Saldos iniciales, Estado de resultados, Cierre de periodo.',
'Administracion > Seguridad y sistema > Usuarios, Roles y permisos, Configuracion.',
'Pedidos web > Gestion de pedidos de tienda en linea.'
]],
['aviso'=>'Consejo de navegacion','estilo'=>'consejo','txt'=>'Si la pantalla es pequena, abra primero el icono de menu y luego seleccione la opcion.']
,
['sub'=>'Referencia rapida de rutas para soporte','lista'=>[
'Inicio principal: /dashboard',
'Punto de venta: /pos',
'Facturas: /sales',
'Caja: /cash-register',
'Inventario: /inventory',
'Contabilidad: /accounting/*'
]]
]],
['tipo'=>'seccion','titulo'=>'3) Flujo diario recomendado','bloques'=>[
['sub'=>'Orden sugerido de trabajo','pasos'=>[
'Iniciar sesion y revisar alertas.',
'Abrir caja.',
'Vender en Punto de venta.',
'Registrar compras y devoluciones cuando existan.',
'Revisar inventario y reportes.',
'Revisar movimientos contables.',
'Cerrar caja al finalizar la jornada.'
]],
['aviso'=>'Resultado esperado','estilo'=>'ok','txt'=>'Caja cerrada, inventario actualizado y contabilidad al dia.']
,
['sub'=>'Lista de verificacion de cierre','lista'=>[
'No dejar ventas en borrador o incompletas.',
'Confirmar que el efectivo en caja coincide con el sistema.',
'Revisar productos con bajo inventario para reposicion.',
'Validar que no existan pendientes criticos en cuentas por cobrar o por pagar.'
]]
]],
['tipo'=>'seccion','titulo'=>'4) Modulo de ventas y caja','bloques'=>[
['sub'=>'Punto de venta','pasos'=>[
'Entre a Punto de venta.',
'Seleccione sucursal.',
'Busque y agregue productos.',
'Seleccione cliente si aplica.',
'Registre forma de pago y confirme la venta.'
]],
['sub'=>'Punto de venta: recomendaciones para evitar errores','lista'=>[
'Antes de cobrar, confirme cantidades y precios en pantalla.',
'Si aplica descuento, valide autorizacion segun rol.',
'Si el cliente paga en efectivo, confirme el cambio antes de finalizar.',
'Entregue comprobante al cliente y confirme que la venta quedo registrada.'
]],
['sub'=>'Facturas','pasos'=>[
'Entre a Facturas.',
'Use filtros por sucursal o busqueda.',
'Abra una factura para revisar detalle y comprobante.'
]],
['sub'=>'Facturas: que revisar en cada documento','lista'=>[
'Numero de venta y fecha.',
'Cliente asociado.',
'Productos, cantidades e impuestos.',
'Estado de pago.'
]],
['sub'=>'Caja','pasos'=>[
'Entre a Caja.',
'Pulse Abrir caja y registre monto inicial.',
'Registre movimientos de entrada o salida con motivo.',
'Pulse Cerrar caja al final.'
]],
['sub'=>'Caja: control interno recomendado','lista'=>[
'No mezclar dinero personal con dinero de caja.',
'Registrar todos los ingresos y egresos con motivo claro.',
'Cerrar caja con arqueo fisico y firma del responsable.'
]],
['sub'=>'Devoluciones','pasos'=>[
'Entre a Devoluciones.',
'Seleccione la venta origen.',
'Seleccione productos y cantidades.',
'Confirme la devolucion.'
]],
['sub'=>'Devoluciones: politica sugerida','lista'=>[
'Solicitar referencia de la venta original.',
'Validar estado del producto devuelto.',
'Registrar observacion de la causa de devolucion.'
]],
['sub'=>'Compras','pasos'=>[
'Entre a Compras.',
'Pulse Nueva compra.',
'Seleccione proveedor.',
'Agregue productos, costos y metodo de pago.',
'Guarde la compra.'
]],
['sub'=>'Compras: datos minimos obligatorios','lista'=>[
'Proveedor o contacto de compra.',
'Documento o referencia de compra.',
'Productos con costo correcto.',
'Forma de pago y saldo pendiente si aplica.'
]]
]],
['tipo'=>'seccion','titulo'=>'5) Modulo de tienda en linea','bloques'=>[
['sub'=>'Compra del cliente','pasos'=>[
'Abrir tienda en linea.',
'Buscar producto y agregar al carrito.',
'Entrar a Carrito y ajustar cantidades.',
'Entrar a Confirmar compra.',
'Completar direccion y metodo de pago.',
'Confirmar pedido.'
]],
['sub'=>'Mis pedidos','pasos'=>[
'Entrar a Mis pedidos.',
'Abrir pedido para ver estado y detalle.'
]],
['sub'=>'Gestion de pedidos web','pasos'=>[
'Entrar a Pedidos web desde el panel.',
'Abrir pedido.',
'Actualizar estado.',
'Convertir a factura cuando corresponda.'
]],
['sub'=>'Tienda en linea: experiencia recomendada para el cliente','lista'=>[
'Mantener catalogo con nombres claros e imagenes actualizadas.',
'Mostrar precios finales con impuestos para evitar reclamos.',
'Actualizar estado del pedido oportunamente para generar confianza.'
]]
]],
['tipo'=>'seccion','titulo'=>'6) Modulo de catalogos y control','bloques'=>[
['sub'=>'Productos','pasos'=>['Crear producto, definir costos, precio de venta, impuesto y visibilidad en tienda en linea.']],
['sub'=>'Categorias','pasos'=>['Crear categorias para organizar productos.']],
['sub'=>'Contactos','pasos'=>['Registrar clientes y proveedores.']],
['sub'=>'Sucursales','pasos'=>['Crear sucursales para separar operacion y control.']],
['sub'=>'Inventario','pasos'=>['Usar Ajuste de inventario solo cuando exista diferencia fisica y con motivo.']],
['sub'=>'Reportes','pasos'=>['Revisar ventas por dia, por cajero y productos mas vendidos para tomar decisiones.']]
,
['sub'=>'Estandares de calidad de datos','lista'=>[
'Evitar productos duplicados con nombres diferentes.',
'Usar codigos consistentes para facilitar busqueda.',
'Mantener categorias simples y entendibles para todo el equipo.'
]]
]],
['tipo'=>'seccion','titulo'=>'7) Curso contable para principiantes','bloques'=>[
['sub'=>'Conceptos clave','lista'=>[
'Activo: lo que tiene la empresa (caja, bancos, inventario, cuentas por cobrar).',
'Pasivo: lo que debe la empresa (cuentas por pagar).',
'Patrimonio: valor propio del negocio.',
'Ingreso: dinero por ventas.',
'Gasto: dinero de operacion.',
'Costo de venta: valor del inventario que se vendio.'
]],
['aviso'=>'Regla de oro','estilo'=>'ok','txt'=>'Toda operacion debe quedar balanceada: Debe = Haber.'],
['sub'=>'Que registra automaticamente el sistema','lista'=>[
'Venta: ingreso, impuesto, costo de venta y salida de inventario.',
'Compra: entrada de inventario y salida de dinero o cuenta por pagar.',
'Devolucion: reverso de ingreso y ajuste de inventario.',
'Recaudo de cartera: disminuye cuentas por cobrar.',
'Pago a proveedor: disminuye cuentas por pagar.',
'Gasto: salida segun metodo de pago.'
]],
['sub'=>'Como leer reportes contables','pasos'=>[
'Primero revise Libro diario y Movimientos.',
'Luego revise Estado de resultados.',
'Luego valide Cierre de periodo.',
'Finalmente confirme que la informacion coincide con la operacion real.'
]],
['sub'=>'Errores contables comunes y como evitarlos','lista'=>[
'Registrar una compra con fecha incorrecta: revise fecha antes de guardar.',
'No registrar un gasto menor: use siempre Registrar gasto para mantener trazabilidad.',
'No conciliar caja con ventas: compare caja diaria contra ventas del dia.',
'Anular operaciones sin justificacion: documente siempre el motivo.'
]],
['sub'=>'Ejemplo practico completo','lista'=>[
'Venta por 119 (base 100 + impuesto 19), pago en efectivo, costo 60.',
'Debe Caja: 119.',
'Haber Ingreso por ventas: 100.',
'Haber Impuesto generado: 19.',
'Debe Costo de venta: 60.',
'Haber Inventario: 60.',
'Interpretacion: entra dinero, baja inventario y se reconoce utilidad.'
]],
['sub'=>'Glosario rapido','lista'=>[
'Debe: columna de cargos contables.',
'Haber: columna de abonos contables.',
'Saldo: diferencia entre Debe y Haber de una cuenta.',
'Asiento: registro contable de una operacion.'
]]
]],
['tipo'=>'seccion','titulo'=>'8) Modulo de contabilidad paso a paso','bloques'=>[
['sub'=>'Plan de cuentas','pasos'=>['Entre a Plan de cuentas, cree cuentas con codigo, tipo y naturaleza.']],
['sub'=>'Libro diario','pasos'=>['Entre a Libro diario, abra asientos y valide lineas Debe y Haber.']],
['sub'=>'Registrar gasto','pasos'=>['Seleccione cuenta valida, metodo de pago y guarde.']],
['sub'=>'Cuentas por cobrar','pasos'=>['Registre abonos de ventas pendientes y anule si hay error.']],
['sub'=>'Cuentas por pagar','pasos'=>['Registre pagos a proveedores y anulaciones cuando aplique.']],
['sub'=>'Saldos iniciales','pasos'=>['Cargue saldos iniciales y cuenta patrimonial de contrapartida.']],
['sub'=>'Estado de resultados','pasos'=>['Filtre por fecha y analice utilidad neta.']],
['sub'=>'Cierre de periodo','pasos'=>['Defina fechas, valide no cruces y genere cierre.']],
['aviso'=>'Control previo al cierre','estilo'=>'advertencia','txt'=>'No cierre periodo si existen ventas, compras o pagos sin registrar.']
,
['sub'=>'Ruta sugerida para un usuario nuevo','pasos'=>[
'Semana 1: consultar Libro diario y Estado de resultados.',
'Semana 2: registrar gastos supervisados.',
'Semana 3: gestionar cuentas por cobrar y por pagar.',
'Semana 4: ejecutar cierre de periodo con acompanamiento.'
]]
]],
['tipo'=>'seccion','titulo'=>'9) Preguntas frecuentes','bloques'=>[
['sub'=>'No puedo vender','lista'=>['Validar caja abierta.','Validar permisos del usuario.','Validar inventario disponible.']],
['sub'=>'Inventario no coincide','lista'=>['Revisar compras, devoluciones y ajustes manuales.']],
['sub'=>'Reporte contable no cuadra','lista'=>['Revisar fechas, anulaciones y operaciones fuera de periodo.']]
]],
];

$pdf = renderPdf($contenido);
if (!is_dir(dirname($salida))) mkdir(dirname($salida), 0777, true);
file_put_contents($salida, $pdf);
echo "PDF generado en: {$salida}\n";

function renderPdf(array $data): string {
    $pages = []; $p = newPage($pages); $x=44; $y=760; $w=507; $min=72;
    foreach ($data as $sec) {
        if ($sec['tipo']==='portada') { portada($pages[$p], $sec['titulo'], $sec['sub']); $p=newPage($pages); $y=760; continue; }
        titulo($pages,$p,$x,$y,$w,$min,$sec['titulo']);
        foreach ($sec['bloques'] as $b) {
            if (isset($b['sub'])) subtitulo($pages,$p,$x,$y,$w,$min,$b['sub']);
            if (isset($b['pasos'])) listaNumerada($pages,$p,$x,$y,$w,$min,$b['pasos']);
            if (isset($b['lista'])) listaSimple($pages,$p,$x,$y,$w,$min,$b['lista']);
            if (isset($b['aviso'])) aviso($pages,$p,$x,$y,$w,$min,$b['aviso'],$b['txt'],$b['estilo']);
            $y -= 4;
        }
        $y -= 8;
    }
    return buildPdf($pages);
}

function newPage(array &$pages): int { $pages[] = []; return count($pages)-1; }

function portada(array &$cmd, string $t, string $s): void {
    // Portada de estilo suave y profesional.
    $cmd[]="0.985 0.990 0.996 rg"; $cmd[]="0 0 595 842 re f";
    $cmd[]="0.89 0.94 0.98 rg"; $cmd[]="34 86 527 670 re f";
    $cmd[]="0.96 0.98 1 rg"; $cmd[]="52 104 491 634 re f";
    $cmd[]="0.79 0.87 0.96 rg"; $cmd[]="52 700 491 38 re f";
    text($cmd,72,635,'/F2',25,[0.10,0.32,0.52],$t);
    $yy=595; foreach (wrap($s,430,12.5) as $ln){ text($cmd,72,$yy,'/F1',12.5,[0.24,0.31,0.38],$ln); $yy-=17; }
    text($cmd,72,520,'/F2',13.5,[0.12,0.37,0.57],'Manual profesional para operacion diaria, control y contabilidad');
    text($cmd,72,492,'/F1',11,[0.28,0.34,0.40],'Documento orientado a capacitacion paso a paso para usuarios principiantes.');
    text($cmd,72,464,'/F1',11,[0.28,0.34,0.40],'Incluye rutas de acceso por vista, listas de verificacion y buenas practicas.');
}

function titulo(array &$pages,int &$p,float $x,float &$y,float $w,float $min,string $t): void {
    ensure($pages,$p,$y,$min,36); $pages[$p][]="0.88 0.94 1 rg"; $pages[$p][]=sprintf("%.2f %.2f %.2f %.2f re f",$x,$y-24,$w,28);
    text($pages[$p],$x+8,$y-15,'/F2',12.4,[0.06,0.34,0.56],$t); $y-=34;
}
function subtitulo(array &$pages,int &$p,float $x,float &$y,float $w,float $min,string $t): void {
    ensure($pages,$p,$y,$min,24); $pages[$p][]="0.93 0.97 1 rg"; $pages[$p][]=sprintf("%.2f %.2f %.2f %.2f re f",$x,$y-15,$w,18);
    text($pages[$p],$x+7,$y-12,'/F2',10.4,[0.08,0.42,0.62],$t); $y-=22;
}
function listaNumerada(array &$pages,int &$p,float $x,float &$y,float $w,float $min,array $items): void {
    foreach($items as $i=>$it){ $lines=wrap(($i+1).'. '.$it,492,10.2); foreach($lines as $k=>$ln){ ensure($pages,$p,$y,$min,14); text($pages[$p],$x+($k?17:4),$y-10,'/F1',10.2,[0.16,0.20,0.25],$ln); $y-=13.5; } }
}
function listaSimple(array &$pages,int &$p,float $x,float &$y,float $w,float $min,array $items): void {
    foreach($items as $it){ $lines=wrap('- '.$it,492,10.2); foreach($lines as $k=>$ln){ ensure($pages,$p,$y,$min,14); text($pages[$p],$x+($k?16:4),$y-10,'/F1',10.2,[0.17,0.22,0.27],$ln); $y-=13.2; } }
}
function aviso(array &$pages,int &$p,float $x,float &$y,float $w,float $min,string $tit,string $txt,string $est): void {
    $pal=['ok'=>[[0.90,0.98,0.93],[0.10,0.54,0.30]],'consejo'=>[[0.92,0.97,1.00],[0.08,0.43,0.64]],'advertencia'=>[[1.00,0.95,0.88],[0.73,0.39,0.07]]];
    [$bg,$hd] = $pal[$est] ?? $pal['consejo']; $lines=wrap($txt,480,10); $h=26+count($lines)*13; ensure($pages,$p,$y,$min,$h+4);
    $pages[$p][]=sprintf("%.3f %.3f %.3f rg",$bg[0],$bg[1],$bg[2]); $pages[$p][]=sprintf("%.2f %.2f %.2f %.2f re f",$x,$y-$h+6,$w,$h);
    $pages[$p][]=sprintf("%.3f %.3f %.3f rg",$hd[0],$hd[1],$hd[2]); $pages[$p][]=sprintf("%.2f %.2f %.2f %.2f re f",$x,$y-18,$w,18);
    text($pages[$p],$x+8,$y-13,'/F2',10,[1,1,1],$tit); $cy=$y-30; foreach($lines as $ln){ text($pages[$p],$x+8,$cy,'/F1',10,[0.18,0.22,0.27],$ln); $cy-=13; }
    $y-=($h+2);
}
function ensure(array &$pages,int &$p,float &$y,float $min,float $need): void { if (($y-$need)<$min){ $p=newPage($pages); $y=760; } }

function text(array &$cmd,float $x,float $y,string $f,float $sz,array $c,string $t): void {
    $cmd[]='BT'; $cmd[]=$f.' '.number_format($sz,2,'.','').' Tf'; $cmd[]=sprintf('%.3f %.3f %.3f rg',$c[0],$c[1],$c[2]); $cmd[]=sprintf('1 0 0 1 %.2f %.2f Tm',$x,$y); $cmd[]='('.esc($t).') Tj'; $cmd[]='ET';
}
function wrap(string $t,float $w,float $sz): array {
    $words=preg_split('/\\s+/',trim($t))?:[]; if(!$words)return ['']; $out=[];$cur='';
    foreach($words as $wd){ $cand=$cur===''?$wd:$cur.' '.$wd; if(width($cand,$sz)<=$w){$cur=$cand;} else { if($cur!=='')$out[]=$cur; $cur=$wd; } }
    if($cur!=='')$out[]=$cur; return $out;
}
function width(string $t,float $sz): float { $m=['i'=>0.26,'l'=>0.26,'j'=>0.28,'f'=>0.30,'t'=>0.33,'r'=>0.34,' '=>0.28,'m'=>0.83,'w'=>0.83,'M'=>0.90,'W'=>0.92]; $s=0; foreach(preg_split('//u',$t,-1,PREG_SPLIT_NO_EMPTY)?:[] as $ch){ $s+= $m[$ch] ?? 0.54; } return $s*$sz; }
function esc(string $t): string { $cp=iconv('UTF-8','Windows-1252//TRANSLIT',$t); if($cp===false)$cp=$t; $o=''; for($i=0,$l=strlen($cp);$i<$l;$i++){ $ord=ord($cp[$i]); if($ord===40||$ord===41||$ord===92){$o.='\\\\'.$cp[$i]; continue;} if($ord<32||$ord>126){$o.=sprintf('\\\\%03o',$ord); continue;} $o.=$cp[$i]; } return $o; }

function buildPdf(array $pages): string {
    $objs=[];$cat=1;$pgs=2;$f1=3;$f2=4;$n=5;$pgObjs=[];
    foreach($pages as $i=>$inner){ $po=$n++; $co=$n++; $pgObjs[]=$po; $stream=pageStream($inner,$i+1); $objs[$co]="<< /Length ".strlen($stream)." >>\nstream\n".$stream."endstream"; $objs[$po]="<< /Type /Page /Parent {$pgs} 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 {$f1} 0 R /F2 {$f2} 0 R >> >> /Contents {$co} 0 R >>"; }
    $kids=implode(' ',array_map(fn($v)=>$v.' 0 R',$pgObjs)); $objs[$pgs]="<< /Type /Pages /Kids [ {$kids} ] /Count ".count($pgObjs)." >>"; $objs[$cat]="<< /Type /Catalog /Pages {$pgs} 0 R >>"; $objs[$f1]="<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>"; $objs[$f2]="<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>";
    ksort($objs); $pdf="%PDF-1.4\n"; $off=[0]; foreach($objs as $k=>$v){ $off[$k]=strlen($pdf); $pdf.=$k." 0 obj\n".$v."\nendobj\n"; }
    $x=strlen($pdf); $max=max(array_keys($objs)); $pdf.="xref\n0 ".($max+1)."\n0000000000 65535 f \n"; for($i=1;$i<=$max;$i++){ $pdf.=sprintf("%010d 00000 n \n",$off[$i]??0); }
    $pdf.="trailer\n<< /Size ".($max+1)." /Root {$cat} 0 R >>\nstartxref\n{$x}\n%%EOF"; return $pdf;
}
function pageStream(array $inner,int $page): string {
    $c=[]; $c[]='0.965 0.973 0.984 rg'; $c[]='0 0 595 842 re f'; $c[]='0.055 0.329 0.561 rg'; $c[]='0 800 595 42 re f';
    $c[]='BT'; $c[]='/F1 10 Tf'; $c[]='0.905 0.953 1 rg'; $c[]='1 0 0 1 24 814 Tm'; $c[]='('.esc('Manual del Sistema POS - Guia para clientes').') Tj'; $c[]='ET';
    $c[]='BT'; $c[]='/F1 9 Tf'; $c[]='0.39 0.44 0.50 rg'; $c[]='1 0 0 1 44 24 Tm'; $c[]='('.esc('Pagina '.$page).') Tj'; $c[]='ET';
    foreach($inner as $ln)$c[]=$ln; return implode("\n",$c)."\n";
}
