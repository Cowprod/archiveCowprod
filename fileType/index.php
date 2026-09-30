<?php
require_once __DIR__ . '/../secure.php';
$oFileTypes=oRs('',__DIR__.'/fileType.sql','',0,'',$WM_ADMIN_conn);
?>
<div id="dFileTypeAdmin">
<div id="dFileTypeMessage" class="alert alert-danger d-none"></div>
<form id="fFileTypeAdd" class="mb-3">
<div class="input-group">
<span class="input-group-text w-25">Type</span>
<input class="form-control" name="FTY_CH_LABEL" required>
<button class="btn btn-success" type="submit"><i class="fa fa-plus-circle me-2"></i>Ajouter</button>
</div>
</form>
<table class="table table-bordered table-striped table-sm align-middle mb-0"><tbody>
<?php foreach($oFileTypes as $aFileType): ?>
<tr data-id="<?=htmlspecialchars(encrypt((string)$aFileType['FTY_N_ID'],$sEncryptKey),ENT_QUOTES,'UTF-8')?>">
<td class="text-center" style="width:50px"><button class="btn btn-danger btn-sm js-filetype-del" type="button"><i class="fa fa-trash"></i></button></td>
<td><input class="form-control form-control-sm js-filetype-label" value="<?=htmlspecialchars($aFileType['FTY_CH_LABEL'],ENT_QUOTES,'UTF-8')?>"></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div>
<script>
(function(){
function state(x,s){clearTimeout(x.data('ast'));x.removeClass('autosave-warning autosave-success autosave-danger');if(s==='warning')x.addClass('autosave-warning');if(s==='success'){x.addClass('autosave-success');x.data('ast',setTimeout(function(){x.removeClass('autosave-success')},1500));}if(s==='danger')x.addClass('autosave-danger');}
function post(u,d,ok,ko){$('#dFileTypeMessage').addClass('d-none').text('');$.ajax({url:u,type:'POST',dataType:'json',data:d}).done(function(r){if(r.success===true){if(ok)ok();return;}$('#dFileTypeMessage').removeClass('d-none').text(r.message||'Erreur');if(ko)ko();}).fail(function(x){$('#dFileTypeMessage').removeClass('d-none').text(x.responseJSON&&x.responseJSON.message?x.responseJSON.message:'Erreur');if(ko)ko();});}
$('#fFileTypeAdd').on('submit',function(e){e.preventDefault();post('/fileType/trUpdFileType.php',$(this).serialize(),function(){updDiv('#modalAdminBody','/fileType/index.php');});});
$('#dFileTypeAdmin .js-filetype-label').typing({delay:500,start:function(e,x){state(x,'warning');},stop:function(e,x){post('/fileType/trUpdFileType.php',{FTY_N_ID:x.closest('tr').data('id'),FTY_CH_LABEL:x.val()},function(){state(x,'success');},function(){state(x,'danger');});}});
$('#dFileTypeAdmin .js-filetype-del').on('click',function(){var r=$(this).closest('tr');cowprodConfirm('Supprimer ce type de fichier ?',function(){post('/fileType/trSupFileType.php',{FTY_N_ID:r.data('id')},function(){r.remove();});});});
})();
</script>
