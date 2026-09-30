<?php
require_once __DIR__ . '/../secure.php';
$oUrlTypes=oRs('',__DIR__.'/urlType.sql','',0,'',$WM_ADMIN_conn);
?>
<div id="dUrlTypeAdmin">
<div id="dUrlTypeMessage" class="alert alert-danger d-none"></div>
<form id="fUrlTypeAdd" class="mb-3">
<div class="input-group">
<span class="input-group-text w-25">Type</span>
<input class="form-control" name="UTY_CH_LABEL" required>
<button class="btn btn-success" type="submit"><i class="fa fa-plus-circle me-2"></i>Ajouter</button>
</div>
</form>
<table class="table table-bordered table-striped table-sm align-middle mb-0"><tbody>
<?php foreach($oUrlTypes as $aUrlType): ?>
<tr data-id="<?=htmlspecialchars(encrypt((string)$aUrlType['UTY_N_ID'],$sEncryptKey),ENT_QUOTES,'UTF-8')?>">
<td class="text-center" style="width:50px"><button class="btn btn-danger btn-sm js-urltype-del" type="button"><i class="fa fa-trash"></i></button></td>
<td><input class="form-control form-control-sm js-urltype-label" value="<?=htmlspecialchars($aUrlType['UTY_CH_LABEL'],ENT_QUOTES,'UTF-8')?>"></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div>
<script>
(function(){
function state(x,s){clearTimeout(x.data('ast'));x.removeClass('autosave-warning autosave-success autosave-danger');if(s==='warning')x.addClass('autosave-warning');if(s==='success'){x.addClass('autosave-success');x.data('ast',setTimeout(function(){x.removeClass('autosave-success')},1500));}if(s==='danger')x.addClass('autosave-danger');}
function post(u,d,ok,ko){$('#dUrlTypeMessage').addClass('d-none').text('');$.ajax({url:u,type:'POST',dataType:'json',data:d}).done(function(r){if(r.success===true){if(ok)ok();return;}$('#dUrlTypeMessage').removeClass('d-none').text(r.message||'Erreur');if(ko)ko();}).fail(function(x){$('#dUrlTypeMessage').removeClass('d-none').text(x.responseJSON&&x.responseJSON.message?x.responseJSON.message:'Erreur');if(ko)ko();});}
$('#fUrlTypeAdd').on('submit',function(e){e.preventDefault();post('/urlType/trUpdUrlType.php',$(this).serialize(),function(){updDiv('#modalAdminBody','/urlType/index.php');});});
$('#dUrlTypeAdmin .js-urltype-label').typing({delay:500,start:function(e,x){state(x,'warning');},stop:function(e,x){post('/urlType/trUpdUrlType.php',{UTY_N_ID:x.closest('tr').data('id'),UTY_CH_LABEL:x.val()},function(){state(x,'success');},function(){state(x,'danger');});}});
$('#dUrlTypeAdmin .js-urltype-del').on('click',function(){var r=$(this).closest('tr');cowprodConfirm('Supprimer ce type d URL ?',function(){post('/urlType/trSupUrlType.php',{UTY_N_ID:r.data('id')},function(){r.remove();});});});
})();
</script>
