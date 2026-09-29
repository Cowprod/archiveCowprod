<?php
require_once __DIR__.'/../../secure.php'; header('Content-Type: application/json; charset=utf-8');
try{$s=trim((string)($_POST['UTY_CH_LABEL']??''));if($s==='')throw new RuntimeException('Le libellé est obligatoire');
$q=$WM_ADMIN_conn->prepare('INSERT INTO T_URLTYPE (UTY_CH_LABEL,UTY_DT_CREATION,UTY_CH_CREATION) VALUES (:l,NOW(),:c)');
$q->execute(['l'=>$s,'c'=>sSignature()]);echo json_encode(['success'=>true]);}
catch(Throwable $e){http_response_code(400);echo json_encode(['success'=>false,'message'=>$e->getMessage()]);}