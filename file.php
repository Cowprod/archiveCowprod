<?php
require_once __DIR__.'/secure.php';
$id=decryptId($_GET['PRF_N_ID'] ?? '', $sEncryptKey);$thumb=isset($_GET['thumb'])&&$_GET['thumb']==='1';
$aFiles=oRs('',__DIR__.'/projectFile/projectFileDownload.sql','PRF_N_ID='.prepNum2Update($id),0,'',$WM_ADMIN_conn);$r=$aFiles[0]??false;if(!$r||!is_file($r['PRF_CH_PATH'])){http_response_code(404);exit('Fichier introuvable');}
$path=$r['PRF_CH_PATH'];$mime=$r['PRF_CH_MIMETYPE'];
if($thumb&&archiveIsImageMime($mime)){
  $cache=archiveProjectCacheDir((int)$r['PRO_N_ID']).'/'.$id.'_320.jpg';
  if(!is_file($cache)||filemtime($cache)<filemtime($path)){
    $data=@file_get_contents($path);$src=$data!==false?@imagecreatefromstring($data):false;
    if($src){$w=imagesx($src);$h=imagesy($src);$nw=min(320,$w);$nh=max(1,(int)round($h*$nw/$w));$dst=imagecreatetruecolor($nw,$nh);imagecopyresampled($dst,$src,0,0,0,0,$nw,$nh,$w,$h);imagejpeg($dst,$cache,85);imagedestroy($dst);imagedestroy($src);$path=$cache;$mime='image/jpeg';}
  }elseif(is_file($cache)){$path=$cache;$mime='image/jpeg';}
}
header('Content-Type: '.$mime);header('Content-Length: '.filesize($path));header('Content-Disposition: inline; filename="'.str_replace('"','',basename($r['PRF_CH_FILENAME'])).'"');readfile($path);