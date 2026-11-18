$(document).ready(function () {
    $("#emsg").hide();
    var $ib_form_submit = $("#submit");
    var $ib_form_submit_test = $("#submit_test");

    $ib_form_submit.click(function (e) {
        e.preventDefault();
        $('#ibox_form').block({ message: null });
        var _url = $("#_url").val();
        $.post(_url + 'whatsapp/config-post/', {

            clientId : $('#clientId').val(), 
            secretId : $('#secretId').val(),
            group : $('#group').val()
        })
            .done(function (data) {
                var sbutton = $("#submit");
                var _url = $("#_url").val();
                if ($.isNumeric(data)) {

                    location.reload();
                }
                else {
                    $('#ibox_form').unblock();
                    var body = $("html, body");
                    body.animate({scrollTop:0}, '1000', 'swing');
                    $("#emsgbody").html(data);
                    $("#emsg").show("slow");
                }
            });
    });

    $ib_form_submit_test.click(function (e) {
        e.preventDefault();
        $('#ibox_form').block({ message: null });
        var _url = $("#_url").val();
        $.post(_url + 'whatsapp/test_send/', {

            number : $('#number').val(),
            message : $('#message').val()      
        })
            .done(function (data) {
                $('#ibox_form').unblock();
                var body = $("html, body");
                body.animate({scrollTop:0}, '1000', 'swing');
                $("#emsgbody").html(data);
                $("#emsg").show("slow");
            })
            .fail(function () {
                $('#ibox_form').unblock();
                $("#emsgbody").html('Não foi possível enviar a mensagem. Tente novamente.');
                $("#emsg").removeClass('alert-info').addClass('alert-danger').show("slow");
            });
    });
});
