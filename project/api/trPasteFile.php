<?php
require_once __DIR__.'/../../secure.php';header('Content-Type: application/json; charset=utf-8');
try{
$pid=decryptId($_POST['PRO_N_ID'] ?? '', $sEncryptKey);$tid=decryptId($_POST['FTY_N_ID'] ?? '', $sEncryptKey);archiveAssertProject($WM_ADMIN_conn,$pid);archiveAssertFileType($WM_ADMIN_conn,$tid);
if(!isset($_FILES['file'])||$_FILES['file']['error']!==UPLOAD_ERR_OK)throw new RuntimeException('Image absente');
$mime=archiveMimeType($_FILES['file']['tmp_name']);if(!archiveIsImageMime($mime))throw new RuntimeException('Le presse-papiers ne contient pas une image');
$ext=['image/png'=>'png','image/jpeg'=>'jpg','image/webp'=>'webp','image/gif'=>'gif'][$mime]??'img';$original='presse-papiers_'.date('Ymd_His').'.'.$ext;
$dir=archiveProjectStorageDir($pid);$path=$dir.'/'.archiveUniqueStorageName($original);
if(!move_uploaded_file($_FILES['file']['tmp_name'],$path))throw new RuntimeException('Impossible d’enregistrer l’image');
try{$id=archiveInsertProjectFile($WM_ADMIN_conn,$pid,$tid,$path,$original,null,null,'clipboard');}catch(Throwable $e){@unlink($path);throw $e;}
echo json_encode(['success'=>true,'PRF_N_ID'=>$id]);
}catch(Throwable $e){http_response_code(400);echo json_encode(['success'=>false,'message'=>$e->getMessage()]);}