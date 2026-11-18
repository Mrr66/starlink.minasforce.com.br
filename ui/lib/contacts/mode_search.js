$(function () {
    $('.footable').footable();
    let users = null;
    var ws = new WebSocket("wss://tunel.minasforce.com.br");
        ws.onmessage = function(data){
            if(data.data.size == undefined){
                let obj = JSON.parse(data.data);
                if( obj.type == "pppoeList"){
                    console.log(obj.data);
                    users.forEach(item => {
                        let device = obj.data.find(p => p['caller-id'] == item.macAddress);
                        if(device != undefined){
                            console.log(device.uptime, device.address)
                            let key  = `#address-${item.macAddress}`.replaceAll(":","");
                            $(key).text(device.address);
                            key  = `#uptime-${item.macAddress}`.replaceAll(":","");
                            $(key).text(device.uptime);
                            key  = `#device-${item.macAddress}`.replaceAll(":","");
                            $(key).css("color", "green");
                        }
                    });
                }
            }
        }
       setInterval(function(){
            if(ws.readyState == 1)
                ws.send(JSON.stringify({ type: "fetchPPPoE"}));
            else
                ws = new WebSocket("wss://tunel.minasforce.com.br");
        }, 1500);

    eventAjax(`${location.origin}/?ng=contacts/get-devices/`, "", "GET", (json) => {
        users = JSON.parse(json)
    })
});

const eventAjax = function (url, formData, type, callback) {
    $.ajax({
        url: url,
        data: formData,
        type: type,
        contentType: false,
        processData: false,
        success: function (r) {
            callback(r);
            ipsPing = JSON.parse(r);
        }
    });
}