function initUi()
{
}

function updDiv(dContainer, sUrl)
{
    $.ajaxSetup({cache:false});

    $.get(sUrl)
        .done(function(data) {
            $(dContainer).html(data);
            initUi();
        });
}

function updDivFormPost(dContainer, sUrl, dForm)
{
    $.ajaxSetup({cache:false});

    $.post(sUrl, $(dForm).serialize())
        .done(function(data) {
            $(dContainer).html(data);
            initUi();
        });
}

function replaceDiv(dContainer, sUrl)
{
    $.ajaxSetup({cache:false});

    $.get(sUrl)
        .done(function(data) {
            $(dContainer).replaceWith(data);
            initUi();
        });
}

function appendDiv(dContainer, sUrl)
{
    $.ajaxSetup({cache:false});

    $.get(sUrl)
        .done(function(data) {
            $(dContainer).append(data);
            initUi();
        });
}

function cowprodConfirm(sTexte, fConfirm, sConfirmBtnClass)
{
    if (!sTexte)
        sTexte = 'Etes-vous sûr ?';

    if (!sConfirmBtnClass)
        sConfirmBtnClass = 'btn btn-danger';

    if ($('#dAdminModal').length && typeof bootstrap !== 'undefined') {
        var oModal = document.getElementById('dAdminModal');

        $('#adminModalLabel').text('Confirmation');
        $('#modalAdminBody').html('<p class="mb-0"></p>');
        $('#modalAdminBody p').text(sTexte);
        $('#adminModalFooter')
            .removeClass('d-none')
            .html(
                '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>' +
                '<button type="button" class="' + sConfirmBtnClass + '" id="adminModalConfirm">Confirmer</button>'
            );

        $('#adminModalConfirm').off('click').on('click', function() {
            bootstrap.Modal.getOrCreateInstance(oModal).hide();
            fConfirm();
        });

        bootstrap.Modal.getOrCreateInstance(oModal).show();
        return;
    }

    if (window.confirm(sTexte))
        fConfirm();
}

function delUpdDiv(dContainer, sTexte, sUrl)
{
    cowprodConfirm(sTexte, function() {
        updDiv(dContainer, sUrl);
    });
}

function del(sTexte, sUrl, sConfirmBtnClass)
{
    cowprodConfirm(sTexte, function() {
        document.location.href = sUrl;
    }, sConfirmBtnClass);
}

function delPostForm(dForm, sTexte)
{
    cowprodConfirm(sTexte, function() {
        $(dForm).trigger('submit');
    });
}

var fAdminModalOnClose = null;

function bootBoxAdmin(sTitre, sUrl, sCallBack)
{
    var oModal = document.getElementById('dAdminModal');

    if (!oModal || typeof bootstrap === 'undefined')
        return;

    $('#adminModalLabel').text(sTitre || '');
    $('#adminModalFooter').addClass('d-none').empty();
    $('#modalAdminBody').html(
        '<div class="text-center py-5">' +
        '<div class="spinner-border" role="status"><span class="visually-hidden">Chargement...</span></div>' +
        '</div>'
    );

    fAdminModalOnClose = null;

    if (typeof sCallBack === 'function')
        fAdminModalOnClose = sCallBack;
    else if (typeof sCallBack === 'string' && sCallBack !== '')
        fAdminModalOnClose = function() { eval(sCallBack); };

    $.get(sUrl)
        .done(function(data) {
            $('#modalAdminBody').html(data);
            initUi();
        })
        .fail(function() {
            $('#modalAdminBody').html('<div class="alert alert-danger mb-0">Impossible de charger l’administration.</div>');
        });

    bootstrap.Modal.getOrCreateInstance(oModal).show();
}

$(function() {
    var oModal = document.getElementById('dAdminModal');

    if (oModal) {
        oModal.addEventListener('hidden.bs.modal', function() {
            $('#adminModalFooter').addClass('d-none').empty();
            $('#modalAdminBody').empty();

            if (typeof fAdminModalOnClose === 'function') {
                var fCallBack = fAdminModalOnClose;
                fAdminModalOnClose = null;
                fCallBack();
            }
        });
    }

    initUi();
});
