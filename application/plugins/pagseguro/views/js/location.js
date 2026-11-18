$(function() {

    var _url = $("#_url").val();

    var ib_box = $("#ib_box");
    var ib_form = $("#ib_form");
    var ib_submit = $("#ib_submit");
    var resp = $("#resp");


    ib_submit.on('click', function(e) {


        e.preventDefault();
        ib_box.block({message: block_msg});

        $.post( _url + "pagseguro/init/get_location/", { access_token: $("#access_token").val() })
            .done(function( data ) {

                resp.html(data);

                ib_box.unblock();


            });

    });

    resp.on('click', '.sq_set', function(e){

        e.preventDefault();

        ib_box.block({message: block_msg});

        var location_id = $(this).attr("data-locationId");

        var accessToken = $(this).attr("data-accessToken");



        $.post( _url + "pagseguro/init/set_location/", { access_token: accessToken, location_id: location_id  })
            .done(function( data ) {

               window.location.replace(_url + "settings/pg-conf/" + data + "/");




            });



    });

});