$(function() {
    var _url = $("#_url").val();
    $.fn.modal.defaults.width = '700px';
    var $modal = $('#ajax-modal');
    $('[data-toggle="tooltip"]').tooltip();
    var ips = [];
    var index = 0;
    $.getJSON(base_url+"devices/get-devices-watchdog", function(data) {
        ips = data.map( item =>{
            return item.ip;
        });
        setTimeout(()=>(run(0)), 2000);

    });

    var ws = new WebSocket("wss://tunel.minasforce.com.br");
    ws.onmessage = function(data){
            if(data.data.type != ''){
                let obj = JSON.parse(data.data);
                if( obj != undefined && obj.status != "connected"){
                    console.log(obj.ip);
                    let key = obj.ip.replaceAll('.', '_');
                    $("#name_"+key).text(obj.deviceName);
                    let ccq = obj.ccq/10;
                    $("#signal_"+key).text(obj.signal);
                    $("#uptime_"+key).text(formatUptime(obj.uptime));
                    $("#wlanUptime_"+key).text(formatUptime(obj.wlanUptime));
                    $("#apMac_"+key).text(obj.apMac);
                    $("#wlanOpmode_"+key).text(obj.wlanOpmode);
                    $("#essid_"+key).text(obj.essid);
                    $("#name_"+key).text(obj.name);
                    if(ccq > 90){
                        $("#situacao_"+key).html("<b style='color: green'>Sem falhas, com transferência de pacotes sem barreiras.</b>");
                        $("#ccq_"+key).html("<span style='color: green'>"+ccq+"%</span>");
                    }
                    else if(ccq >= 70){
                        $("#situacao_"+key).html("<b style='color: #b18300'>Sem falhas, radio com estabilidade.</b>");
                        $("#ccq_"+key).html("<span style='color: #b18300'>"+ccq+"%</span>");
                    }
                    else if(ccq < 70){
                        $("#situacao_"+key).html("<b style='color: red'>Os pacotes deste radio não esta sendo tranferido por completo</b>");
                        $("#ccq_"+key).html("<span style='color: red'>"+ccq+"%</span>");
                    }
                    else
                        $("#situacao_"+key).html("<b style='color: red'>Sem taxa de transferência.</b>");
                }

                
                if(index < ips.length){
                    index++;
                    run(index);
                }
            }
    }
    
    function run(index){
        ws.send(JSON.stringify({ type: "get_info_radio", ip: ips[index] }));
    }

    function formatUptime(seconds) {
      if (!seconds || isNaN(seconds)) return "0s";
        const d = Math.floor(seconds / (3600 * 24));
        const h = Math.floor((seconds % (3600 * 24)) / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = Math.floor(seconds % 60);
        
        const dDisplay = d > 0 ? d + "d " : "";
        const hDisplay = h > 0 ? h + "h " : "";
        const mDisplay = m > 0 ? m + "m " : "";
        const sDisplay = s > 0 ? s + "s" : "0s";
        
        return (dDisplay + hDisplay + mDisplay + sDisplay).trim();       
    }
        /*setInterval(function(){
            if(ws.readyState == 1)
               ws.send(JSON.stringify({ type: "get_info_radio", ip: "192.168.88.166" }));
            else
                ws = new WebSocket("wss://tunel.minasforce.com.br");
        }, 2500);
        */


    $('.add_minas_force_dog').on('click', function(e){

        e.preventDefault();

        $('body').modalmanager('loading');

        $modal.load( _url + 'devices/modal_add_station', '', function(){

            $modal.modal();

        });
    });


    $modal.on('click', '.modal_submit', function(e){

        e.preventDefault();

        $modal.modal('loading');

        $.post( _url + "devices/add_watchdog_post/", $("#ib_modal_form").serialize())
            .done(function( data ) {

                if ($.isNumeric(data)) {

                    location.reload();

                }

                else {
                    $modal.modal('loading');
                    toastr.error(data);
                }

            });

    });


    $(".cdelete").click(function (e) {
        e.preventDefault();
        var id = this.id;
        bootbox.confirm(_L['are_you_sure'], function(result) {
            if(result){
                var _url = $("#_url").val();
                window.location.href = _url + "delete/company/" + id + '/';
            }
        });
    });


    $(".cedit").click(function (e) {
        e.preventDefault();
        var id = this.id;

        $('body').modalmanager('loading');

        $modal.load( _url + 'devices/modal_add_station/'+ id + '/', '', function(){

            $modal.modal();

        });

    });
});