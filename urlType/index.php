<?php
require_once __DIR__ . '/../secure.php';
$oUrlTypes=$WM_ADMIN_conn->query('SELECT T_URLTYPE.UTY_N_ID,T_URLTYPE.UTY_CH_LABEL FROM T_URLTYPE WHERE T_URLTYPE.UTY_DT_SUPPRESSION IS NULL ORDER BY T_URLTYPE.UTY_CH_LABEL')->fetchAll();
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.8/dist/quartz/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" rel="stylesheet"></head>
<body><div class="container-fluid py-3"><h1 class="h5 mb-3">Types d’URL</h1><div id="dMessage" class="alert alert-danger d-none"></div>
<form id="fAdd" class="mb-3"><div class="input-group"><span class="input-group-text">Type</span><input class="form-control" name="UTY_CH_LABEL" placeholder="Libellé" required><button class="btn btn-outline-success"><i class="fa fa-plus-circle me-2"></i>Ajouter</button></div></form>
<table class="table table-bordered table-striped table-sm align-middle"><tbody>
<?php foreach($oUrlTypes as $a): ?><tr data-id="<?= (int)$a['UTY_N_ID'] ?>"><td class="text-center" style="width:50px"><button class="btn btn-outline-danger btn-sm js-del" type="button"><i class="fa fa-trash"></i></button></td><td><input class="form-control form-control-sm js-label" value="<?= htmlspecialchars($a['UTY_CH_LABEL'],ENT_QUOTES,'UTF-8') ?>"></td></tr><?php endforeach; ?>
</tbody></table></div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script><script src="/assets/js/jquery.typing-0.2.0.js"></script>
<script>$(function(){function post(u,d,ok){$.post(u,d).done(function(r){if(r.success){if(ok)ok();}else $('#dMessage').removeClass('d-none').text(r.message||'Erreur');}).fail(function(x){$('#dMessage').removeClass('d-none').text(x.responseJSON&&x.responseJSON.message?x.responseJSON.message:'Erreur');});}
$('#fAdd').on('submit',function(e){e.preventDefault();post('/urlType/api/trAdd.php',$(this).serialize(),function(){location.reload();});});
$('.js-label').typing({delay:500,stop:function(e,x){post('/urlType/api/trUpd.php',{UTY_N_ID:x.closest('tr').data('id'),UTY_CH_LABEL:x.val()});}});
$('.js-del').on('click',function(){var r=$(this).closest('tr');if(confirm('Supprimer ce type d’URL ?'))post('/urlType/api/trDelete.php',{UTY_N_ID:r.data('id')},function(){r.remove();});});});</script></body></html>