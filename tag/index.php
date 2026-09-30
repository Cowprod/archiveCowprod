<?php
require_once __DIR__ . '/../secure.php';
$aCategories=oRs('',__DIR__.'/tagCategory.sql','',0,'',$WM_ADMIN_conn);
$aTags=oRs('',__DIR__.'/tag.sql','',0,'',$WM_ADMIN_conn);
$aTagsByCategory=[];
foreach($aTags as $aTag){$aTagsByCategory[(int)$aTag['TCA_N_ID']][]=$aTag;}
?>
<div id="dTagAdmin">
<div id="dTagMessage" class="alert alert-danger d-none"></div>
<form id="fAddCategory" class="mb-3">
<div class="input-group">
<span class="input-group-text w-25">Catégorie</span>
<input class="form-control" name="TCA_CH_LABEL" required>
<select class="form-select" name="TCA_CH_COLOR" style="max-width:180px">
<?php foreach(['primary','secondary','success','danger','warning','info','light','dark'] as $sColor): ?><option value="<?=$sColor?>"><?=$sColor?></option><?php endforeach; ?>
</select>
<button class="btn btn-success" type="submit"><i class="fa fa-plus-circle me-2"></i>Ajouter</button>
</div>
</form>
<?php foreach($aCategories as $aCategory): $TCA_N_ID=(int)$aCategory['TCA_N_ID']; ?>
<div class="card mb-3" data-category-id="<?=htmlspecialchars(encrypt((string)$TCA_N_ID,$sEncryptKey),ENT_QUOTES,'UTF-8')?>">
<div class="card-header">
<div class="input-group">
<button class="btn btn-danger js-delete-category" type="button"><i class="fa fa-trash"></i></button>
<input class="form-control js-category-text" data-field="TCA_CH_LABEL" value="<?=htmlspecialchars($aCategory['TCA_CH_LABEL'],ENT_QUOTES,'UTF-8')?>">
<select class="form-select js-category-change" data-field="TCA_CH_COLOR" style="max-width:180px">
<?php foreach(['primary','secondary','success','danger','warning','info','light','dark'] as $sColor): ?><option value="<?=$sColor?>" <?=$aCategory['TCA_CH_COLOR']===$sColor?'selected':''?>><?=$sColor?></option><?php endforeach; ?>
</select>
</div>
</div>
<div class="card-body">
<form class="fAddTag mb-3">
<input type="hidden" name="TCA_N_ID" value="<?=htmlspecialchars(encrypt((string)$TCA_N_ID,$sEncryptKey),ENT_QUOTES,'UTF-8')?>">
<div class="input-group">
<span class="input-group-text w-25">Tag</span>
<input class="form-control" name="TAG_CH_LABEL" required>
<button class="btn btn-success" type="submit"><i class="fa fa-plus-circle me-2"></i>Ajouter</button>
</div>
</form>
<?php if(!empty($aTagsByCategory[$TCA_N_ID])): ?>
<table class="table table-bordered table-striped table-sm align-middle mb-0"><tbody>
<?php foreach($aTagsByCategory[$TCA_N_ID] as $aTag): ?>
<tr data-tag-id="<?=htmlspecialchars(encrypt((string)$aTag['TAG_N_ID'],$sEncryptKey),ENT_QUOTES,'UTF-8')?>">
<td class="text-center" style="width:50px"><button class="btn btn-danger btn-sm js-delete-tag" type="button"><i class="fa fa-trash"></i></button></td>
<td><input class="form-control form-control-sm js-tag-text" data-field="TAG_CH_LABEL" value="<?=htmlspecialchars($aTag['TAG_CH_LABEL'],ENT_QUOTES,'UTF-8')?>"></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>
</div>
</div>
<?php endforeach; ?>
</div>
<script>
(function(){
function state(x,s){clearTimeout(x.data('ast'));x.removeClass('autosave-warning autosave-success autosave-danger');if(s==='warning')x.addClass('autosave-warning');if(s==='success'){x.addClass('autosave-success');x.data('ast',setTimeout(function(){x.removeClass('autosave-success')},1500));}if(s==='danger')x.addClass('autosave-danger');}
function err(m){$('#dTagMessage').removeClass('d-none').text(m);}
function post(u,d,ok,ko){$('#dTagMessage').addClass('d-none').text('');$.ajax({url:u,type:'POST',dataType:'json',data:d}).done(function(r){if(r.success===true){if(ok)ok();return;}err(r.message||'Erreur');if(ko)ko();}).fail(function(x){err(x.responseJSON&&x.responseJSON.message?x.responseJSON.message:'Erreur');if(ko)ko();});}
function saveCategory(x){var c=x.closest('[data-category-id]');state(x,'warning');post('/tag/trUpdTagCategory.php',{TCA_N_ID:c.data('category-id'),sField:x.data('field'),sValue:x.val()},function(){state(x,'success');},function(){state(x,'danger');});}
function saveTag(x){var r=x.closest('[data-tag-id]');state(x,'warning');post('/tag/trUpdTag.php',{TAG_N_ID:r.data('tag-id'),sField:x.data('field'),sValue:x.val()},function(){state(x,'success');},function(){state(x,'danger');});}
$('#dTagAdmin .js-category-text').typing({delay:500,start:function(e,x){state(x,'warning');},stop:function(e,x){saveCategory(x);}});
$('#dTagAdmin .js-tag-text').typing({delay:500,start:function(e,x){state(x,'warning');},stop:function(e,x){saveTag(x);}});
$('#dTagAdmin .js-category-change').on('change',function(){saveCategory($(this));});
$('#fAddCategory').on('submit',function(e){e.preventDefault();post('/tag/trUpdTagCategory.php',$(this).serialize(),function(){updDiv('#modalAdminBody','/tag/index.php');});});
$('#dTagAdmin .fAddTag').on('submit',function(e){e.preventDefault();post('/tag/trUpdTag.php',$(this).serialize(),function(){updDiv('#modalAdminBody','/tag/index.php');});});
$('#dTagAdmin .js-delete-tag').on('click',function(){var r=$(this).closest('[data-tag-id]');cowprodConfirm('Supprimer ce tag ?',function(){post('/tag/trSupTag.php',{TAG_N_ID:r.data('tag-id')},function(){r.remove();});});});
$('#dTagAdmin .js-delete-category').on('click',function(){var c=$(this).closest('[data-category-id]');cowprodConfirm('Supprimer cette catégorie ?',function(){post('/tag/trSupTagCategory.php',{TCA_N_ID:c.data('category-id')},function(){c.remove();});});});
})();
</script>
