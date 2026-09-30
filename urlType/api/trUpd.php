<?php
require_once __DIR__.'/../../secure.php'; header('Content-Type: application/json; charset=utf-8');
try{$id=decryptId($_POST['UTY_N_ID'] ?? '', $sEncryptKey);$s=trim((string)($_POST['UTY_CH_LABEL']??''));if($id<=0||$s==='')throw new RuntimeException('Type invalide');
$WM_ADMIN_conn->beginTransaction();historiseTable('T_URLTYPE','UTY',$id,$WM_ADMIN_conn);
$q=$WM_ADMIN_conn->prepare('UPDATE T_URLTYPE SET UTY_CH_LABEL=:l WHERE UTY_N_ID=:id AND UTY_DT_SUPPRESSION IS NULL');$q->execute(['l'=>$s,'id'=>$id]);
if($q->rowCount()!==1)throw new RuntimeException('Type introuvable');$WM_ADMIN_conn->commit();echo json_encode(['success'=>true]);}
catch(Throwable $e){if(isset($WM_ADMIN_conn)&&$WM_ADMIN_conn->inTransaction())$WM_ADMIN_conn->rollBack();http_response_code(400);echo json_encode(['success'=>false,'message'=>$e->getMessage()]);}