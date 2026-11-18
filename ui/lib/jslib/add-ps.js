$(document).ready(function () {
    $(".progress").hide();
    $("#emsg").hide();
    $("#rform").on('submit', function (e) {
        e.preventDefault();
        $('#ibox_form').block({ message: null });
        var _url = $("#_url").val();
        var formData = new FormData(document.getElementById('rform'));
        $.ajax({
            url: _url + 'ps/add-post/',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false
        })
            .done(function (data) {

                setTimeout(function () {
                    var sbutton = $("#submit");
                    var _url = $("#_url").val();
                    if ($.isNumeric(data)) {

                        location.reload();
                    }
                    else {
                        $('#ibox_form').unblock();

                        $("#emsgbody").html(data);
                        $("#emsg").show("slow");
                    }
                }, 2000);
            })
            .fail(function (xhr) {
                $('#ibox_form').unblock();
                var message = 'Falha ao enviar o produto e a imagem.';
                if (xhr.status === 413) message = 'A imagem é maior que o limite aceito pelo servidor.';
                $("#emsgbody").html(message);
                $("#emsg").show("slow");
            });
    });
});
