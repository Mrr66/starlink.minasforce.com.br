var load_modules = function load_modules() {

    return true;

};
var max_consumo_hoje = 0;//Começar no maximo para não enviar notificação
var _url = $("#_url").val();
$(function() {
    var g_type = _url + 'dashboard' + '/';

    var ib_graph = $("#ib_graph");


    ib_graph.on('click', '.close', function(){
        $.post( _url+'settings/update_option/silent/', { opt: "ib_u_a", val: "0" })
            .done(function( data ) {

            });
    });

    $.post( g_type + "render/", { inner_graph: "canvas" })
        .done(function( render ) {

            ib_graph.html(render.msg);
        }
    );
    if(location.href.indexOf("dashboard") !== -1){
        var maxSpeed = 300;
        var consumo = 0;
        var mediaConsumo = (consumo/maxSpeed)*100;
        var ctx = document.getElementById("velocimetro").getContext("2d");
        var chartData = {
            title: "maximo",
            labels: ["Consumo de banda atual", "Banda de internet livre"],
            datasets: [{
                data: [mediaConsumo, maxSpeed-mediaConsumo], // Valor da porcentagem, e o resto da porcentagem de 100%
                backgroundColor: [
                    '#38444d', // Color for current speed
                    '#04AA6D'   // Color for remaining arc
                ],
                hoverBackgroundColor: [
                    '#222',
                    '#444'
                ],
                borderWidth: 0
            }]
        };

         Chart.plugins.register({
                    beforeDraw: function(chartInstance) {
                        var ctx = chartInstance.chart.ctx;
                        var width = chartInstance.chart.width;
                        var height = chartInstance.chart.height;
                        var fontSize = (height / 115).toFixed(2);
                        ctx.font = fontSize + "em sans-serif";
                        ctx.textBaseline = "middle";
                        var text =  consumo+" MB/S";
                        var textX = Math.round((width - ctx.measureText(text).width) / 2);
                        // Position vertically in the center, adjusting for the semi-circle
                        var textY = (height / 2) + 96; 
                        ctx.fillStyle = '#444';
                        ctx.fillText(text, textX, textY);
                    }
                });

        var chartOptions = {
            cutoutPercentage: 80, // Espessura do anel
            circumference: Math.PI, // Meio círculo
            rotation: Math.PI,     // Começar na parte inferior
            title: {
                display: true,
                text: 'Velocidade maxima de hoje: 0 mb/s',
                color: "#f60"
            },
            tooltip: {
              enabled: true
            },
            animation: {
                //animateRotate: true,
            }
        };

        var speedChart = new Chart(ctx, {
            type: 'doughnut',
            data: chartData,
            options: chartOptions
        });

    }
    function updateSpeed(newSpeed) {
        consumo = newSpeed;
        const restante = maxSpeed - newSpeed;
        // Update the data in the chart object
        speedChart.data.datasets[0].data[0] = newSpeed;
        speedChart.data.datasets[0].data[1] = restante;
        speedChart.update();
    }

    if (location.href.indexOf("dashboard") !== -1) {
        var ws = new WebSocket("wss://tunel.minasforce.com.br");
        ws.onmessage = function(data){
            if(data.data.type != ''){
                let obj = JSON.parse(data.data);
                if( obj.trafficData != undefined){
                    console.log(obj.trafficData);
                    if(parseFloat(obj.trafficData.tx) > max_consumo_hoje){
                        max_consumo_hoje = parseFloat(obj.trafficData.tx);
                        speedChart.options.title.text = "Velocidade maxima de hoje: " + max_consumo_hoje + " MB/S";
                        save_max_consumo(max_consumo_hoje,  parseFloat(obj.trafficData.rx))
                    }
                    updateSpeed(obj.trafficData.tx);
                    console.log(`TX: ${obj.trafficData.tx} RX: ${obj.trafficData.rx}`)
                }
            }
        }
        setInterval(function(){
            if(ws.readyState == 1)
                ws.send(JSON.stringify({ type: "fetchTraffic" }));
            else
                ws = new WebSocket("wss://tunel.minasforce.com.br");
        }, 500);
    }

    const get_max_consumo_old =  function(){
        let url = `${_url}api`;
        var form = new FormData();
        form.append("method", "get_consumo_internet");
        form.append("key", window.STARLINK_API_KEY || "");

        var settings = {
          "url": url,
          "method": "POST",
          "processData": false,
          "mimeType": "multipart/form-data",
          "headers": {
                "Cookie": "PHPSESSID=97fb0ffc87fbf71c54d4d5e9c49e6391"
          },
          "contentType": false,
          "data": form
        };

        $.ajax(settings).done(function (response) {
            const obj = JSON.parse(response);
            max_consumo_hoje = obj[0].download;
            speedChart.options.title.text = "Velocidade maxima de hoje: " + max_consumo_hoje + " MB/S";
            console.log(max_consumo_hoje);
        });
    }
    get_max_consumo_old();
});



const save_max_consumo =  function(download, upload){
    let url = `${_url}api`;
    var form = new FormData();
    form.append("method", "consumo_internet");
    form.append("download", download);
    form.append("upload", upload);
    form.append("key", window.STARLINK_API_KEY || "");

    var settings = {
      "url": url,
      "method": "POST",
      "processData": false,
      "mimeType": "multipart/form-data",
      "headers": {
            "Cookie": "PHPSESSID=97fb0ffc87fbf71c54d4d5e9c49e6391"
        },
      "contentType": false,
      "data": form
    };

    $.ajax(settings).done(function (response) {
        const obj = JSON.parse(response);
        console.log(obj.download, ") Atualizado");
    });
}
