$(function() {

    var _url = $("#_url").val();

    $.fn.modal.defaults.width = '700px';
    
    var $modal = $('#ajax-modal');

    $('[data-toggle="tooltip"]').tooltip();

    $('.add_company').on('click', function(e){
        e.preventDefault();

        $('body').modalmanager('loading');
        var num_invoice = $("#num_invoice").val();
        $modal.load( _url + 'contacts/modal_add_nfe/'+num_invoice, '', function(){
            $modal.modal();
        });
    });


    $modal.on('click', '.modal_submit', function(e){
       
        e.preventDefault();
        var num_invoice = $("#num_invoice").val();
        var status = $("#status").val();
        var notaFiscal = $("#notaFiscal").val();
        var encodedString = btoa(notaFiscal);
        var path = _url + "contacts/update_nfe/"+num_invoice+"/"+status+"/"+encodedString;
        $.post(path)
            .done(function( data ) {
                if (data.length > 400) {
                    toastr.success("Nota fiscal lançado com sucesso.");
                }

                else {
                    toastr.error("Erro ao lançar a nota.");
                }

            });

    });


});