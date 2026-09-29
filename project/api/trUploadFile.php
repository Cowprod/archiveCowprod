<?php
require_once __DIR__.'/../../secure.php';header('Content-Type: application/json; charset=utf-8');
try{
$pid=(int)($_POST['PRO_N_ID']??0);$tid=(int)($_POST['FTY_N_ID']??0);$label=trim((string)($_POST['PRF_CH_LABEL']??''));$year=archiveValidateYear($_POST['PRF_N_YEAR']??null);
archiveAssertProject($WM_ADMIN_conn,$pid);archiveAssertFileType($WM_ADMIN_conn,$tid);
if(!isset($_FILES['file'])||$_FILES['file']['error']!==UPLOAD_ERR_OK)throw new RuntimeException('Fichier absent ou upload incomplet');
if(!is_uploaded_file($_FILES['file']['tmp_name']))throw new RuntimeException('Upload invalide');
$dir=archiveProjectStorageDir($pid);$name=archiveUniqueStorageName((string)$_FILES['file']['name']);$path=$dir.'/'.$name;
if(!move_uploaded_file($_FILES['file']['tmp_name'],$path))throw new RuntimeException('Impossible d’enregistrer le fichier');
try{$id=archiveInsertProjectFile($WM_ADMIN_conn,$pid,$tid,$path,(string)$_FILES['file']['name'],$label===''?null:$label,$year,'upload');}
catch(Throwable $e){@unlink($path);throw $e;}
echo json_encode(['success'=>true,'PRF_N_ID'=>$id]);
}catch(Throwable $e){http_response_code(400);echo json_encode(['success'=>false,'message'=>$e->getMessage()]);}