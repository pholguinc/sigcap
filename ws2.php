<?php
require_once 'conectar.php';

$no_permitidas= array ("á","é","í","ó","ú","Á","É","Í","Ó","Ú","À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬","Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã", "ÃŠ", "ÃŽ", "Ã", "Ã›", "ü","Ã¶", "Ã–","Ã¯","Ã¤","«","Ò","Ã","Ã","Ã‹","*","%", "'", '"');
$permitidas= array ("a","e","i","o","u","A","E","I","O","U","N","A","E","I","O","U","a","e","i","o","u","c","C","a","e","i","o","u","A","E","I","O","U","u","o","O","i", "a", "e", "U", "I", "A", "E", ".", ".", "", "");

$fechainicio=$_GET['fecha_ini'];
$fechafin=$_GET['fecha_fin'];
$data=array();

$fechainicio1 = $fechainicio;
$fechainicio2 = $fechafin;


/**/

$origenventa='01';
$ctaigv='40111';
$origenpagos='04';

$sentencia = $base_de_datos->query("



select correlativo_exp as VOU,
		c.id as id_cab_cpe,
		c.serie ||'-'|| c.numero serie_nro_doc_cpe,
		(select cd.id_concepto
		 from comprobante_detalles cd 
		 where cd.id_comprobante =c.id
		 limit 1)  cve_producto,
		substring(  case when (select count( *) from comprobante_detalles cd2 where cd2.id_comprobante =c.id and cd2.id_concepto <>26464)=1 then  (select  descripcion from comprobante_detalles cd2 where cd2.id_comprobante =c.id and cd2.id_concepto <>26464 order by item desc limit 1 )
	  						  when (select count( *) from comprobante_detalles cd2 where cd2.id_comprobante =c.id and cd2.id_concepto <>26464)>1 then  (select  descripcion from comprobante_detalles cd2 where cd2.id_comprobante =c.id and cd2.id_concepto <>26464 order by item desc limit 1) || ' - ' || (select  descripcion from comprobante_detalles cd2 where cd2.id_comprobante =c.id  and cd2.id_concepto <>26464 order by item asc limit 1)  end   
					,1,250) nom_producto,
		1  cantidad,
		c.subtotal pre_unitario,
		c.total sub_total,
		'' tipo_isc,
		0 por_isc,
		0 monto_isc,
		(select cd.afect_igv
		 from comprobante_detalles cd 
		 where cd.id_comprobante =c.id
		 limit 1 )  tipo_afec_igv,
		c.impuesto  monto_igv,
		0 pre_total,
		 (select cc.codigo
			from comprobante_detalles cd
				inner join conceptos co on co.id =cd.id_concepto 
				inner join centro_costos cc on cc.id =co.centro_costo	
			where cd.id_comprobante =c.id
			limit 1)   centro_costo,
		(select pp.codigo
			from comprobante_detalles cd
				inner join conceptos co on co.id =cd.id_concepto 	 
				 inner join partida_presupuestales pp on pp.id=co.partida_presupuestal	
			where cd.id_comprobante =c.id
			limit 1)  partida_presupuestal,
		 (select pc.cuenta
			from comprobante_detalles cd
				left join conceptos co on co.id =cd.id_concepto  
				 left join plan_contables pc on pc.id=co.cuenta_contable_debe	
			where cd.id_comprobante =c.id
			limit 1 )  cuenta_contable,
		c.estado_pago ,
		c.fecha ,
		c.tipo ,
        c.tipo_cambio,
		c.destinatario ,
		(select c2.tipo ||' '|| c.serie ||'-'|| c.numero from comprobantes c2 where c2.id=c.id_comprobante_ncnd limit 1) id_comprobante_ncnd ,
		case when length(cod_tributario)=11 then 79 else 
		( select  p.id_tipo_documento from  personas p where p.id=c.id_persona limit 1) end tipo_documento,		

		c.cod_tributario,
		c.total,
		c.impuesto  ,
		c.subtotal,
		 0  operacion_exonerada_documento_venta ,
		(CASE WHEN c.impuesto = 0 THEN c.SubTotal ELSE 0 END)  operacion_inafecta_documento_venta ,
		0  operacion_gratuita_documento_venta,
		(CASE WHEN c.impuesto > 0 THEN c.SubTotal ELSE 0 END)  opercion_gravada_documento_venta,
		c.id_forma_pago,
		c.id_moneda	,
		 (select TOV from origenes where origen='VENTAS') origen,
		 c.id id_comprobante,
		estado_sunat,
		id_comprobante_ncnd nota_relacionad,
		(select  pc.cuenta
			from comprobante_detalles cd
				left join conceptos co on co.id =cd.id_concepto  
				 left join plan_contables pc on pc.id=co.cuenta_contable_al_haber1	
			where cd.id_comprobante =c.id
			limit 1 )  cuenta_haber1,
		(select  pc.cuenta
			from comprobante_detalles cd
				left join conceptos co on co.id =cd.id_concepto  
				 left join plan_contables pc on pc.id=co.cuenta_contable_al_haber2	
			where cd.id_comprobante =c.id
			limit 1 ) cuenta_haber2
from comprobantes c 
	 
--where date(c.fecha)>='$fechainicio' AND date(c.fecha)<='$fechafin'
where c.tipo in ('FT','BV','ND')  and date(c.fecha)>='$fechainicio' AND date(c.fecha)<='$fechafin'

order by vou




");


$mascotas = $sentencia->fetchAll(PDO::FETCH_OBJ);

foreach($mascotas as $obj){

$tipocambiomon=$obj->tipo_cambio;
//$tipocambiomon='1.00';
$reltipodoc='';
$relserie='';
$relfecha='';

//$texto=utf8_decode($texto);
//$ruccliente=utf8_decode($ruccliente);$cadena_formateada = trim($cadena);




$serienumero=$obj->serie_nro_doc_cpe;
$serienumerotipo=$obj->tipo.'-'.$obj->serie_nro_doc_cpe;

$exoneradotot='0.00';
$inafectatot='0.00';

////AQUI LA CABECERA/////
//$n=$n+1;

$moneda="S";
if($obj->id_moneda=='2'){ 
$moneda="D";
}

$nombrecliente='';
if(isset($obj->destinatario)){ $nombrecliente=$obj->destinatario; }

$cadena_formateada= trim($nombrecliente);
//$cadena_formateada=limitar_cadena($cadena_formateada, 60, "");
$texto = str_replace($no_permitidas, $permitidas, $cadena_formateada);
//$tdoccli='6';
$codigocab='01';

$glosa='ASIENTO DE VENTA';

$glosadescp=trim($obj->nom_producto);

if($obj->tipo=='BV'){
//$tdoccli='1';
$codigocab='03';
	
}else if($obj->tipo=='ND'){
$codigocab='08';
}else if($obj->tipo=='NC'){
$codigocab='07';
$glosa='NOTA DE CREDITO';


//echo '|'.$obj->nota_relacionad.'|';

if($obj->nota_relacionad){

$stmtrel= $base_de_datos->prepare("select c.tipo , c.serie, c.numero ,c.fecha 
from comprobantes c 
where c.id='$obj->nota_relacionad' ");
$stmtrel->setFetchMode(PDO::FETCH_ASSOC);
$stmtrel->execute();
$rowrel=$stmtrel->fetch();

$relserie=$rowrel['serie'].'-'.$rowrel['numero'];
$relfecha=date("d/m/Y", strtotime($rowrel['fecha']));

}

$reltipodoc='01';

if($rowrel['tipo']=='BV'){
//$tdoccli='1';
$reltipodoc='03';
}
}

$tdoccli='6';
if($obj->tipo_documento=='78'){
$tdoccli='1';
}else if($obj->tipo_documento=='84'){
$tdoccli='4';
}else if($obj->tipo_documento=='85'){
$tdoccli='7';	
}else if($obj->tipo_documento=='86'){
$tdoccli='0';
}else if($obj->tipo_documento=='259'){
$tdoccli='0';
}

$ruccliente=trim($obj->cod_tributario);


//cast(campo as int) as alias
//$fecha= explode('-', $row7['FECHA_DOCUMENTO_VENTA']);
//$anio=$fecha[2].'/'.$fecha[1].'/'.$fecha[0];

$anio=date("d/m/Y", strtotime($obj->fecha));
$fechaf=date("d/m/Y", strtotime($obj->fecha));

$voucherfinal=$obj->vou;

$cuenta='12121';

if($obj->tipo=='117'){
$debe='0.00';

$debepago='0.00';
$haberpago='0.00';

$haber1='0.00';
$cuenta='4693';

}else if($obj->tipo=='NC'){

$cuenta='12121';//$cuenta='4693'

$debe='0.00';
$haber=$obj->total;
$debe1=$obj->monto_igv;	
$haber1='0.00';

}else{
$debe=$obj->total;
$haber='0.00';
$debe1='0.00';
$haber1=$obj->monto_igv;
}
$igvdetalle=$obj->monto_igv;
if($igvdetalle=='.00'){ $igvdetalle='0.00'; }
$subtotal=$obj->subtotal;

$exoneradotot='0.00';
$exoneradotot=$obj->operacion_exonerada_documento_venta;
if($exoneradotot=='.00'){ $exoneradotot='0.00'; }

$inafectatot=$obj->operacion_inafecta_documento_venta;
if($inafectatot=='.00'){ $inafectatot='0.00'; }
$gratuitatot=$obj->operacion_gratuita_documento_venta;
if($gratuitatot=='.00'){ $gratuitatot='0.00'; }

if($inafectatot>0){ $subtotal='0.00'; }
if($gratuitatot>0){ $subtotal='0.00'; }
if($exoneradotot>0){ $subtotal='0.00'; }

if($obj->estado_sunat=='117'){

$debe='0.00';
$haber='0.00';
$debe1='0.00';
$haber1='0.00';	
$exoneradotot='0.00';
$inafectatot='0.00';
$gratuitatot='0.00';
$subtotal='0.00';
$igvdetalle='0.00';

$glosa='(CON) OPERACION ANULADA';
$texto='COMPROBANTE ANULADO';
//$ruccliente='00000000000';
$tdoccli='0';
}



$data[]=array(
'origen'=>$obj->origen,
'vou'=>''.$voucherfinal,
'fecha'=>$anio,
'cuenta'=>$cuenta,
'debe'=>''.$debe,
'haber'=>''.$haber,
'moneda'=>$moneda,
'tc'=>$tipocambiomon,
'doc'=>$codigocab,
'numero'=>$serienumero,
'fechad'=>$anio,
'fechav'=>$fechaf,
'codigo'=>$ruccliente,
'cc'=>'',
'pre'=>'',
'fe'=>'',
'glosa'=>$glosadescp,
'tl'=>'',
'neto1'=>'',
'neto2'=>'',
'neto3'=>'',
'neto4'=>'',
'neto5'=>'',
'neto6'=>'',
'neto7'=>'',
'neto8'=>'',
'neto9'=>'',
'igv'=>''.$igvdetalle,
'rdoc'=>$reltipodoc,
'rnum'=>$relserie,
'rfec'=>$relfecha,
'snum'=>'',	
'sfec'=>'',
'ruc'=>$ruccliente,
'rs'=>$texto,
'tipo'=>'2',
'tdoci'=>$tdoccli,
'mpago'=>'',
'ape1'=>'',
'ape2'=>'',
'nombre'=>'',
'tbien'=>'',
'refmonto'=>'0.00'
);
//echo 'cuenta igv:'.$ctaigv;

$data[]=array(
'origen'=>$obj->origen,
'vou'=>''.$voucherfinal,//$obj->cuenta_haber1
'fecha'=>$anio,
'cuenta'=>$ctaigv,
'debe'=>''.$debe1,
'haber'=>''.$haber1,
'moneda'=>$moneda,
'tc'=>$tipocambiomon,
'doc'=>$codigocab,
'numero'=>$serienumero,
'fechad'=>$anio,
'fechav'=>$fechaf,
'codigo'=>$ruccliente,
'cc'=>'',
'pre'=>'',
'fe'=>'',
'glosa'=>$glosadescp,
'tl'=>'V',
'neto1'=>''.$subtotal,
'neto2'=>'',
'neto3'=>'',
'neto4'=>'',
'neto5'=>''.$exoneradotot,
'neto6'=>''.$inafectatot,
'neto7'=>'',
'neto8'=>'',
'neto9'=>'',
'igv'=>''.$igvdetalle,
'rdoc'=>$reltipodoc,
'rnum'=>$relserie,
'rfec'=>$relfecha,
'snum'=>'',	
'sfec'=>'',
'ruc'=>$ruccliente,
'rs'=>$texto,
'tipo'=>'2',
'tdoci'=>$tdoccli,
'mpago'=>'',
'ape1'=>'',
'ape2'=>'',
'nombre'=>'',
'tbien'=>'',
'refmonto'=>'0.00'
);


$cuenta70final=$obj->cuenta_contable;
//if($obj->centrocosto_siscont=='100301'){ $cuenta70final='759912'; }

if($obj->tipo=='NC'){
//$glosadescp='CUERPO DE LA NOTA DE CREDITO';
//cd.cantidad  cantidad,
//cd.pu pre_unitario,
$habercuerpo='0.00'; 	
//$debecuerpo=$obj->sub_total;
$debecuerpo=round($obj->cantidad*$obj->pre_unitario,2);
}else{
$debecuerpo='0.00';
//$habercuerpo=$obj->sub_total;
$habercuerpo=round($obj->cantidad*$obj->pre_unitario,2);
$glosa=$glosadescp;
}

if($obj->estado_sunat=='117'){

$debecuerpo='0.00';
$habercuerpo='0.00';	
$exoneradotot='0.00';
$inafectatot='0.00';
$gratuitatot='0.00';
$subtotal='0.00';

$glosadescp='(CON) OPERACION ANULADA';
$texto='COMPROBANTE ANULADO';
//$ruccliente='00000000000';
$tdoccli='0';
}


//DETALLE DE LA VENTA
$data[]=array(
'origen'=>$obj->origen,
'vou'=>''.$voucherfinal,
'fecha'=>$anio,
'cuenta'=>$obj->cuenta_haber2,
'debe'=>''.$debecuerpo,
'haber'=>''.$habercuerpo,
'moneda'=>$moneda,
'tc'=>$tipocambiomon,
'doc'=>$codigocab,
'numero'=>$serienumero,
'fechad'=>$anio,
'fechav'=>$fechaf,
'codigo'=>$ruccliente,
'cc'=>''.$obj->centro_costo,
'pre'=>'01'.$obj->partida_presupuestal,
'fe'=>'',
'glosa'=>$glosadescp,
'tl'=>'',
'neto1'=>'',
'neto2'=>'',
'neto3'=>'',
'neto4'=>'',
'neto5'=>'',
'neto6'=>'',
'neto7'=>'',
'neto8'=>'',
'neto9'=>'',
'igv'=> '',
'rdoc'=>$reltipodoc,
'rnum'=>$relserie,
'rfec'=>$relfecha,
'snum'=>'',	
'sfec'=>'',
'ruc'=>$ruccliente,
'rs'=>$texto,
'tipo'=>'2',
'tdoci'=>$tdoccli,
'mpago'=>'',
'ape1'=>'',
'ape2'=>'',
'nombre'=>'',
'tbien'=>'',
'refmonto'=>'0.00'
);




}








////ORIGEN PAGOS
///ORIGEN PAGOS



$origenventa='01';
$ctaigv='4001';
$origenpagos='08';

$selectpagos="


select correlativo_exp as VOU,
		c.id as id_cab_cpe,
		c.serie ||'-'|| c.numero serie_nro_doc_cpe,
		(select cd.id_concepto
		 from comprobante_detalles cd 
		 where cd.id_comprobante =c.id
		 limit 1)  cve_producto,
		substring(  case when (select count( *) from comprobante_detalles cd2 where cd2.id_comprobante =c.id and cd2.id_concepto <>26464)=1 then  (select  descripcion from comprobante_detalles cd2 where cd2.id_comprobante =c.id and cd2.id_concepto <>26464 order by item desc limit 1 )
	  						  when (select count( *) from comprobante_detalles cd2 where cd2.id_comprobante =c.id and cd2.id_concepto <>26464)>1 then  (select  descripcion from comprobante_detalles cd2 where cd2.id_comprobante =c.id and cd2.id_concepto <>26464 order by item desc limit 1) || ' - ' || (select  descripcion from comprobante_detalles cd2 where cd2.id_comprobante =c.id  and cd2.id_concepto <>26464 order by item asc limit 1)  end   
					,1,250) nom_producto,
		1  cantidad,
		c.subtotal pre_unitario,
		c.total sub_total,
		'' tipo_isc,
		0 por_isc,
		0 monto_isc,
		(select cd.afect_igv
		 from comprobante_detalles cd 
		 where cd.id_comprobante =c.id
		 limit 1 )  tipo_afec_igv,
		c.impuesto  monto_igv,
		0 pre_total,
		 (select cc.codigo
			from comprobante_detalles cd
				inner join conceptos co on co.id =cd.id_concepto 
				inner join centro_costos cc on cc.id =co.centro_costo	
			where cd.id_comprobante =c.id
			limit 1)   centro_costo,
		(select pp.codigo
			from comprobante_detalles cd
				inner join conceptos co on co.id =cd.id_concepto 	 
				 inner join partida_presupuestales pp on pp.id=co.partida_presupuestal	
			where cd.id_comprobante =c.id
			limit 1)  partida_presupuestal,
		 (select pc.cuenta
			from comprobante_detalles cd
				left join conceptos co on co.id =cd.id_concepto  
				 left join plan_contables pc on pc.id=co.cuenta_contable_debe	
			where cd.id_comprobante =c.id
			limit 1 )  cuenta_contable,
		c.estado_pago ,
		c.fecha ,
		c.tipo ,
		c.tipo_cambio,
		c.destinatario ,
		(select c2.tipo ||' '|| c.serie ||'-'|| c.numero from comprobantes c2 where c2.id=c.id_comprobante_ncnd limit 1) id_comprobante_ncnd ,
		case when length(cod_tributario)=11 then 79 else 
		( select  p.id_tipo_documento from  personas p where p.id=c.id_persona limit 1) end tipo_documento,		

		c.cod_tributario,
		c.total,
		c.impuesto  ,
		c.subtotal,
		 0  operacion_exonerada_documento_venta ,
		(CASE WHEN c.impuesto = 0 THEN c.SubTotal ELSE 0 END)  operacion_inafecta_documento_venta ,
		0  operacion_gratuita_documento_venta,
		(CASE WHEN c.impuesto > 0 THEN c.SubTotal ELSE 0 END)  opercion_gravada_documento_venta,
		c.id_forma_pago,
		c.id_moneda	,
		 (select TOV from origenes where origen='VENTAS') origen,
		 c.id id_comprobante,
		estado_sunat,
		id_comprobante_ncnd nota_relacionad,
		(select  pc.cuenta
			from comprobante_detalles cd
				left join conceptos co on co.id =cd.id_concepto  
				 left join plan_contables pc on pc.id=co.cuenta_contable_al_haber1	
			where cd.id_comprobante =c.id
			limit 1 )  cuenta_haber1,
		(select  pc.cuenta
			from comprobante_detalles cd
				left join conceptos co on co.id =cd.id_concepto  
				 left join plan_contables pc on pc.id=co.cuenta_contable_al_haber2	
			where cd.id_comprobante =c.id
			limit 1 ) cuenta_haber2
from comprobantes c 
	 
--where c.tipo in ('FT','BV','ND')  and date(c.fecha)>='$fechainicio' AND date(c.fecha)<='$fechafin'
where c.tipo in ('XXX')  and date(c.fecha)>='$fechainicio' AND date(c.fecha)<='$fechafin'
order by vou


";
//echo $selectpagos;

$sentencia = $base_de_datos->query($selectpagos);
$mascotas = $sentencia->fetchAll(PDO::FETCH_OBJ);

foreach($mascotas as $obj){

$tipocambiomon=$obj->tipo_cambio;
//$tipocambiomon='1.00';
$reltipodoc='';
$relserie='';
$relfecha='';

//$texto=utf8_decode($texto);
//$ruccliente=utf8_decode($ruccliente);$cadena_formateada = trim($cadena);

$serienumero=$obj->serie_nro_doc_cpe;
$serienumerotipo=$obj->tipo.'-'.$obj->serie_nro_doc_cpe;

$exoneradotot='0.00';
$inafectatot='0.00';

////AQUI LA CABECERA/////
//$n=$n+1;

$moneda="S";
if($obj->id_moneda=='2'){ 
$moneda="D";
}

$nombrecliente='';
if(isset($obj->destinatario)){ $nombrecliente=$obj->destinatario; }

$cadena_formateada= trim($nombrecliente);
//$cadena_formateada=limitar_cadena($cadena_formateada, 60, "");
$texto = str_replace($no_permitidas, $permitidas, $cadena_formateada);
//$tdoccli='6';
$codigocab='01';

$glosa='ASIENTO DE VENTA';

$glosadescp=trim($obj->nom_producto);

if($obj->tipo=='BV'){
//$tdoccli='1';
$codigocab='03';
}else if($obj->tipo=='NC'){
$codigocab='07';
$glosa='NOTA DE CREDITO';

$stmtrel= $base_de_datos->prepare("select c.tipo , c.serie, c.numero ,c.fecha 
from comprobantes c 
where c.id='$obj->nota_relacionad' ");
$stmtrel->setFetchMode(PDO::FETCH_ASSOC);
$stmtrel->execute();
$rowrel=$stmtrel->fetch();

$relserie=$rowrel['serie'].'-'.$rowrel['numero'];
$relfecha=date("d/m/Y", strtotime($rowrel['fecha']));

$reltipodoc='01';

if($rowrel['tipo']=='BV'){
//$tdoccli='1';
$reltipodoc='03';
}
}

$tdoccli='6';
if($obj->tipo_documento=='78'){
$tdoccli='1';
}else if($obj->tipo_documento=='84'){
$tdoccli='4';
}else if($obj->tipo_documento=='85'){
$tdoccli='7';	
}else if($obj->tipo_documento=='86'){
$tdoccli='0';
}else if($obj->tipo_documento=='259'){
$tdoccli='0';
}

$ruccliente=trim($obj->cod_tributario);


//cast(campo as int) as alias
//$fecha= explode('-', $row7['FECHA_DOCUMENTO_VENTA']);
//$anio=$fecha[2].'/'.$fecha[1].'/'.$fecha[0];

$anio=date("d/m/Y", strtotime($obj->fecha));
$fechaf=date("d/m/Y", strtotime($obj->fecha));

$voucherfinal=$obj->vou;

$cuenta='12121';

if($obj->tipo=='117'){
$debe='0.00';

$debepago='0.00';
$haberpago='0.00';

$haber1='0.00';
$cuenta='4693';

}else if($obj->tipo=='27'){

$cuenta='4693';

$debe='0.00';
$haber=$obj->total;
$debe1=$obj->monto_igv;	
$haber1='0.00';

}else{
$debe=$obj->total;
$haber='0.00';
$debe1='0.00';
$haber1=$obj->monto_igv;
}
$igvdetalle=$obj->monto_igv;
if($igvdetalle=='.00'){ $igvdetalle='0.00'; }
$subtotal=$obj->total;

$exoneradotot='0.00';
$exoneradotot=$obj->operacion_exonerada_documento_venta;
if($exoneradotot=='.00'){ $exoneradotot='0.00'; }

$inafectatot=$obj->operacion_inafecta_documento_venta;
if($inafectatot=='.00'){ $inafectatot='0.00'; }
$gratuitatot=$obj->operacion_gratuita_documento_venta;
if($gratuitatot=='.00'){ $gratuitatot='0.00'; }

if($inafectatot>0){ $subtotal='0.00'; }
if($gratuitatot>0){ $subtotal='0.00'; }
if($exoneradotot>0){ $subtotal='0.00'; }

if($obj->estado_sunat=='117'){

$debe='0.00';
$haber='0.00';
$debe1='0.00';
$haber1='0.00';	
$exoneradotot='0.00';
$inafectatot='0.00';
$gratuitatot='0.00';
$subtotal='0.00';
$igvdetalle='0.00';

$glosa='(CON) OPERACION ANULADA';
$texto='COMPROBANTE ANULADO';
//$ruccliente='00000000000';
$tdoccli='0';
}



//PAGOS
//FORMADEPAGO_DOCUMENTO_VENTA<>'253'

//echo 'estado-sunat:'.$obj->estado_sunat;

if($obj->estado_sunat!='117'){

if($obj->tipo!='27'){
if($obj->id_forma_pago=='1'){

$ctapagos='101';
$ctapagoshaber='12121';
$condicionpago='O101';
//IdTablaElemento

$stmtcond= $base_de_datos->prepare("

select c.tipo, c.serie ||'-'|| c.numero serie_nro_doc_cpe, cp.fecha, id_moneda ,(select desctablaelemento from equiva_contabilidad where tabla='medio_pago' and idtablaelemento=cp.id_medio::varchar(10)) medio_pago , (select siscont_medio_pago from equiva_contabilidad where tabla='medio_pago' and idtablaelemento=cp.id_medio::varchar(10)) id_medio,nro_operacion, cp.monto,(select TOV from origenes where origen='COBRANZAS') origen, (select siscont_medio_pago from equiva_contabilidad where tabla='forma_pago' and idtablaelemento=c.id_forma_pago::varchar(10))  id_forma_pago
from  comprobante_pagos cp   inner join comprobantes c on cp.id_comprobante =c.id
where  c.tipo in ('FT','BV','ND') and id_comprobante =  '$obj->id_cab_cpe'


");
$stmtcond->setFetchMode(PDO::FETCH_ASSOC);
$stmtcond->execute();
$rowcond=$stmtcond->fetch();

if($rowcond){
if($rowcond['id_forma_pago']=='1'){ $condicionpago='1032'; }
}
$glosapago=$texto;

$stmtrel= $base_de_datos->prepare("

select c.tipo, c.serie ||'-'|| c.numero serie_nro_doc_cpe, cp.fecha, id_moneda ,(select desctablaelemento from equiva_contabilidad where tabla='medio_pago' and idtablaelemento=cp.id_medio::varchar(10)) medio_pago , (select siscont_medio_pago from equiva_contabilidad where tabla='medio_pago' and idtablaelemento=cp.id_medio::varchar(10)) id_medio,nro_operacion, cp.monto,(select TOV from origenes where origen='COBRANZAS') origen, (select siscont_medio_pago from equiva_contabilidad where tabla='forma_pago' and idtablaelemento=c.id_forma_pago::varchar(10))  id_forma_pago
from  comprobante_pagos cp   inner join comprobantes c on cp.id_comprobante =c.id
where  c.tipo in ('FT','BV','ND') and id_comprobante =  '$obj->id_cab_cpe'

");


$stmtrel->setFetchMode(PDO::FETCH_ASSOC);
$stmtrel->execute();
$rowrel=$stmtrel->fetch();

//echo 'cuenta ctapagos:'.$ctapagos;
//consulta medio pago

$medpago='';
if($rowcond){
$medpago=$rowcond['id_medio'];
}

$origenpago='04';
$numerorelacionado='';
if($rowrel){
$origenpago=$rowrel['origen'];
$numerorelacionado=$rowrel['serie_nro_doc_cpe'];
}
$data[]=array(
'origen'=>$origenpago,
'vou'=>''.$voucherfinal,
'fecha'=>$anio,
'cuenta'=>$ctapagos,
'debe'=>''.$debe,
'haber'=>''.$haber,
'moneda'=>$moneda,
'tc'=>$tipocambiomon,
'doc'=>$codigocab,
'numero'=>$numerorelacionado,
'fechad'=>$anio,
'fechav'=>$fechaf,
'codigo'=>$ruccliente,
'cc'=>'',
'pre'=>'',
'fe'=>''.$condicionpago,
'glosa'=>$glosadescp,
//'glosa'=>$glosa,
'tl'=>'V',
'neto1'=>''.$subtotal,
'neto2'=>'',
'neto3'=>'',
'neto4'=>'',
'neto5'=>''.$exoneradotot,
'neto6'=>''.$inafectatot,
'neto7'=>'',
'neto8'=>'',
'neto9'=>'',
'igv'=>''.$igvdetalle,
'rdoc'=>$reltipodoc,
'rnum'=>$relserie,
'rfec'=>$relfecha,
'snum'=>'',	
'sfec'=>'',
'ruc'=>$ruccliente,
'rs'=>$texto,
'tipo'=>'2',
'tdoci'=>$tdoccli,
'mpago'=>$medpago,
'ape1'=>'',
'ape2'=>'',
'nombre'=>'',
'tbien'=>'',
'refmonto'=>'0.00'
);

$data[]=array(
'origen'=>$origenpago,
'vou'=>''.$voucherfinal,
'fecha'=>$anio,
'cuenta'=>$ctapagoshaber,
'debe'=>''.$haber,
'haber'=>''.$debe,
'moneda'=>$moneda,
'tc'=>$tipocambiomon,
'doc'=>$codigocab,
'numero'=>$serienumero,
'fechad'=>$anio,
'fechav'=>$fechaf,
'codigo'=>$ruccliente,
'cc'=>'',
'pre'=>'',
'fe'=>'',
'glosa'=>$glosadescp,
//'glosa'=>$glosa,
'tl'=>'',
'neto1'=>'',
'neto2'=>'',
'neto3'=>'',
'neto4'=>'',
'neto5'=>'',
'neto6'=>'',
'neto7'=>'',
'neto8'=>'',
'neto9'=>'',
'igv'=>''.$igvdetalle,
'rdoc'=>$reltipodoc,
'rnum'=>$relserie,
'rfec'=>$relfecha,
'snum'=>'',	
'sfec'=>'',
'ruc'=>$ruccliente,
'rs'=>$texto,
'tipo'=>'2',
'tdoci'=>$tdoccli,
'mpago'=>'',
'ape1'=>'',
'ape2'=>'',
'nombre'=>'',
'tbien'=>'',
'refmonto'=>'0.00'
);



}	
}
}


/*
$sqlcont=" update comprobantes set  migra_conta='N' where  id='$obj->id_cab_cpe' ";
//echo $sqlcont;
$sql = $base_de_datos->prepare($sqlcont);
$sql->execute();

update planilla_delegado_detalles set  migra_conta='S' where  id=607


*/



}


















/**
 * PLANILLAS
 */
//where c.fecha>='$fechainicio' AND c.fecha<='$fechafin'



$sentencia = $base_de_datos->query(" 



select pdd.secuencua_vou as vou, a.id, a.id_persona,  p.numero_ruc, 
case when p.desc_cliente_sunat is null then p.apellido_paterno ||' '|| p.apellido_materno ||' '|| p.nombres else p.desc_cliente_sunat end desc_cliente_sunat, a.cuenta, 
case when a.debe = 0 then 0 else a.debe end debe, 
case when a.haber = 0 then 0 else a.haber end haber,
a.equivalente, 
a.glosa, case when  a.centro_costo is null then '' else a.centro_costo end ,case when   a.presupuesto is null then '' else   a.presupuesto end, a.codigo_financiero, a.medio_pago, a.id_tipo_documento, a.serie, a.numero, a.fecha_documento, 
a.fecha_vencimiento, a.id_moneda, a.tipo_cambio, a.id_estado_doc, a.estado, a.id_asiento_planilla, a.id_periodo_comision, a.id_periodo_comision_detalle,
case when a.id_tipo = 1 then 'PROVISION' else 'CANCELACION' end tipo, a.orden, a.numero_comprobante, a.id_grupo
from asiento_planillas a
inner join personas p on p.id = a.id_persona
--inner join plan_contables c on c.cuenta = a.cuenta
inner join planilla_delegado_detalles pdd on a.id_planilla_delegado_detalle  =pdd.id 
where
	fecha_documento  between '$fechainicio' and '$fechafin'
	and pdd.migra_conta='N'
--And a.id_periodo_comision = 1050
and a.id_tipo=1
order by vou, numero_comprobante, orden



");
$mascotas = $sentencia->fetchAll(PDO::FETCH_OBJ);

foreach($mascotas as $siscont){


$tipoodocumento='6';

$moneda='D';
if($siscont->id_moneda=='1'){ $moneda='S'; }

$tipoodocumentofact='01';
if($siscont->id_tipo_documento=='1'){ $tipoodocumentofact='03'; }

$origen='06';


$centrocostos=$siscont->centro_costo;
$controlpresupuestos=$siscont->presupuesto;

$formapago='';
$mediopago='';


$tipoodocumentofact='13';
if($siscont->cuenta=='1692'){
	$origen='09';
	$centrocostos='';
	$controlpresupuestos='';
	
	$formapago=$siscont->codigo_financiero;
	$mediopago=$siscont->medio_pago;
	$tipoodocumentofact='02';
	
	}

	
//$siscont->glosa.$siscont->tipo,

	$data[]=array(
	'origen'=>$origen,
	'vou'=> ''.$siscont->vou,
	'fecha'=>date("d/m/Y", strtotime($siscont->fecha_documento)),
	'cuenta'=>$siscont->cuenta,
	'debe'=>''.round($siscont->debe, 2),
	'haber'=>''.round($siscont->haber, 2),
	'moneda'=>$moneda,
	'tc'=>$siscont->tipo_cambio,
	'doc'=>$tipoodocumentofact,
	'numero'=>$siscont->numero_comprobante,
	'fechad'=>date("d/m/Y", strtotime($siscont->fecha_documento)),
	'fechav'=>date("d/m/Y", strtotime($siscont->fecha_vencimiento)),
	'codigo'=>$siscont->numero_ruc,
	'cc'=>$centrocostos,
	'pre'=>$controlpresupuestos,
	'fe'=>''.$formapago,
	'glosa'=>$siscont->glosa,
	'tl'=>'',
	'neto1'=>'',
	'neto2'=>'',
	'neto3'=>'',
	'neto4'=>'',
	'neto5'=>'',
	'neto6'=>'',
	'neto7'=>'',
	'neto8'=>'',
	'neto9'=>'',
	'igv'=>'',
	'rdoc'=>'',
	'rnum'=>'',
	'rfec'=>'',
	'snum'=>'',	
	'sfec'=>'',
	'ruc'=>$siscont->numero_ruc,
	'rs'=>$siscont->desc_cliente_sunat,
	'tipo'=>'5',
	'tdoci'=>$tipoodocumento,
	'mpago'=>''.$mediopago,
	'ape1'=>'',
	'ape2'=>'',
	'nombre'=>'',
	'tbien'=>'',
	'refmonto'=>'0.00'
	);


}



$tipoodocumentofact='02';
$tipoodocumentofactcancela='13';

$sentencia = $base_de_datos->query(" 


select pdd.secuencua_vou as vou, a.id, a.id_persona,  p.numero_ruc, 
case when p.desc_cliente_sunat is null then p.apellido_paterno ||' '|| p.apellido_materno ||' '|| p.nombres else p.desc_cliente_sunat end desc_cliente_sunat, a.cuenta, 
case when a.debe = 0 then 0 else a.debe end debe, 
case when a.haber = 0 then 0 else a.haber end haber,
a.equivalente, 
a.glosa, case when  a.centro_costo is null then '' else a.centro_costo end ,case when   a.presupuesto is null then '' else   a.presupuesto end, a.codigo_financiero, a.medio_pago, a.id_tipo_documento, a.serie, a.numero, a.fecha_documento, 
a.fecha_vencimiento, a.id_moneda, a.tipo_cambio, a.id_estado_doc, a.estado, a.id_asiento_planilla, a.id_periodo_comision, a.id_periodo_comision_detalle,
case when a.id_tipo = 1 then 'PROVISION' else 'CANCELACION' end tipo, a.orden, a.numero_comprobante, a.id_grupo
from asiento_planillas a
inner join personas p on p.id = a.id_persona
--inner join plan_contables c on c.cuenta = a.cuenta
inner join planilla_delegado_detalles pdd on a.id_planilla_delegado_detalle  =pdd.id 
where
	fecha_documento  between '$fechainicio' and '$fechafin'
	and pdd.migra_conta='N'
--And a.id_periodo_comision = 1050
and a.id_tipo=2
order by vou, numero_comprobante, orden



");
$mascotas = $sentencia->fetchAll(PDO::FETCH_OBJ);

foreach($mascotas as $siscont){


$tipoodocumento='6';

$moneda='D';
if($siscont->id_moneda=='1'){ $moneda='S'; }

$tipoodocumentofact='01';
if($siscont->id_tipo_documento=='1'){ $tipoodocumentofact='03'; }

$origen='06';


$centrocostos=$siscont->centro_costo;
$controlpresupuestos=$siscont->presupuesto;

$tipoodocumentofactcancela='02';

if($siscont->tipo=='CANCELACION'){
$origen='09';
$centrocostos='';
$controlpresupuestos='';
$tipoodocumentofactcancela='13';
}

$data[]=array(
'origen'=>$origen,
'vou'=>''.$siscont->vou,
'fecha'=>date("d/m/Y", strtotime($siscont->fecha_documento)),
'cuenta'=>$siscont->cuenta,
'debe'=>''.round($siscont->debe, 2),
'haber'=>''.round($siscont->haber, 2),
'moneda'=>$moneda,
'tc'=>$siscont->tipo_cambio,
'doc'=>$tipoodocumentofactcancela,
'numero'=>$siscont->numero_comprobante,
'fechad'=>date("d/m/Y", strtotime($siscont->fecha_documento)),
'fechav'=>date("d/m/Y", strtotime($siscont->fecha_vencimiento)),
'codigo'=>$siscont->numero_ruc,
'cc'=>$centrocostos,
'pre'=>$controlpresupuestos,
'fe'=>'',
'glosa'=>$siscont->glosa,
'tl'=>'',
'neto1'=>'',
'neto2'=>'',
'neto3'=>'',
'neto4'=>'',
'neto5'=>'',
'neto6'=>'',
'neto7'=>'',
'neto8'=>'',
'neto9'=>'',
'igv'=>'',
'rdoc'=>'',
'rnum'=>'',
'rfec'=>'',
'snum'=>'',	
'sfec'=>'',
'ruc'=>$siscont->numero_ruc,
'rs'=>$siscont->desc_cliente_sunat,
'tipo'=>'5',
'tdoci'=>$tipoodocumento,
'mpago'=>'',
'ape1'=>'',
'ape2'=>'',
'nombre'=>'',
'tbien'=>'',
'refmonto'=>'0.00'
);


}







/**
 * PLANILLAS
 */

header('Content-Type: application/json');	
echo json_encode($data);

?>