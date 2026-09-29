<?php
require_once __DIR__.'/../../secure.php'; header('Content-Type: application/json; charset=utf-8');
try{$id=(int)($_POST['UTY_N_ID']??0);if($id<=0)throw new RuntimeException('Type invalide');
$q=$WM_ADMIN_conn->prepare('SELECT COUNT(*) FROM T_PROJECTURL WHERE UTY_N_ID=:id AND PRU_DT_SUPPRESSION IS NULL');$q->execute(['id'=>$id]);if((int)$q->fetchColumn()>0)throw new RuntimeException('Ce type est utilisé par une ou plusieurs URLs');
$WM_ADMIN_conn->beginTransaction();historiseTable('T_URLTYPE','UTY',$id,$WM_ADMIN_conn);
$q=$WM_ADMIN_conn->prepare('UPDATE T_URLTYPE SET UTY_DT_SUPPRESSION=NOW(),UTY_CH_SUPPRESSION=:s WHERE UTY_N_ID=:id AND UTY_DT_SUPPRESSION IS NULL');$q->execute(['s'=>sSignature(),'id'=>$id]);
if($q->rowCount()!==1)throw new RuntimeException('Type introuvable');$WM_ADMIN_conn->commit();echo json_encode(['success'=>true]);}
catch(Throwable $e){if(isset($WM_ADMIN_conn)&&$WM_ADMIN_conn->inTransaction())$WM_ADMIN_conn->rollBack();http_response_code(400);echo json_encode(['success'=>false,'message'=>$e->getMessage()]);}