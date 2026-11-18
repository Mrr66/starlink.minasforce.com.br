var _url = `${window.location.origin}/?ng=devices/`;
var urlOrigin = window.location.origin;
var progresbar = document.getElementById("progress-bar");
var progresbar_block = document.getElementById("progress-block");
var okClickClose = false, isDialogOpen = false;
var $modal = $('#ajax-modal');
var onePassErroTvOff = false;
var type_busca_bloco = 0;
var is_exit_page = false;
var ipsPing = [];
var indexPing = 0;
const graphPing = [];

var ws = new WebSocket("wss://tunel.minasforce.com.br");
ws.onmessage = function(data){
    if(data.data.type != ''){
    let obj = JSON.parse(data.data)[0];
    if(obj.type == "ping"){
        if(indexPing < ipsPing.length){
            if (obj.status == "OK"){
                $(`#${clearIp(obj.host)}`).removeClass("offline").addClass("online");
                $(`#ms-${clearIp(obj.host)}`).text(obj.time);
                let device = graphPing.find(p => p.ip === obj.host);
                device.chart.data.labels.push(device.chart.data.labels.length + 1);
                device.chart.data.datasets.forEach((dataset) => {
                        dataset.data.push(parseInt(obj.time.replace("ms","")));
                });
                device.chart.update();    
            }
            else 
                $(`#${obj.host.replaceAll(".", "")}`).removeClass("online").addClass("offline");
            indexPing++;
            processPingData(indexPing);
        }else{
            console.log("PINGOU TODOS OS IPS");
            indexPing = 0;
            processPingData(indexPing);
        }
    }
    console.log(obj);
    }
    else{
        indexPing++;
        processPingData(indexPing);
    }
}

ws.error = function(data){
    console.log("Desconectado conectar novamente em 5 segundos");
}


const ping = function(ip) {
    console.log(`Pinging ${ip}`);
    ws.send(JSON.stringify({ type: "ping", ip: ip }));
}

const processPingData = function(index) {
    if(index >= ipsPing.length)
        indexPing = 0;
    let ip = ipsPing[index].ip;
    if(ip != "192.168.88.5 (Trocar o ip do 233 para o 5)" && ip != "")
        ping(ip);
} 

setInterval(() => {processPingData(indexPing)}, 10000);


const createGraphMs = function(id_canvas, ip) {
    graphPing.push({
        chart: new Chart(id_canvas, {
            type: "line",
            data: {
                labels: 0,
                datasets: [{
                    fill: false,
                    lineTension: 0,
                    backgroundColor: "#18c318",
                    borderColor: "#18c318",
                    data: 0
            }]
        },
        options: {
            legend: {display: false},
            scales: {
                yAxes: [],
            }
        }
        }),
        ip: ip
    });
}

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

function setModeListTvs() {
    let modeListTvs = localStorage.getItem('type_list_blocks');
    localStorage.setItem('is_tvs_loaded', false);
    if (modeListTvs == null) {
        localStorage.setItem('type_list_blocks', 0);
        modeListTvs = 0;
    }

    if (modeListTvs == 0) {
        localStorage.setItem('type_list_blocks', 1)
        location.reload();
    }
    else {
        localStorage.setItem('type_list_blocks', 0);
        location.reload();
    }
}

function select_type_search(type) {
    localStorage.setItem('is_tvs_loaded', false);
    if (type != null)
        localStorage.setItem('search_type', type);
    location.reload();
}

function get_list_tvs() {

    localStorage.setItem('is_tvs_loaded', false);
    let modeListTvs = localStorage.getItem('type_list_blocks');
    let modeSearch = localStorage.getItem('search_type');
    $(".typefilter").text(modeSearch);
    if (modeListTvs == null) {
        modeListTvs = '0';
        localStorage.setItem('type_list_blocks', 0);
    }

    switch (modeSearch) {
        case 'online':
            $(".typefilter").html("Online").css("color", "#75eb67");
            break;

        case 'offline':
            $(".typefilter").html("Off-Line").css("color", "red");
            break;

        default:
            $(".typefilter").html("Todos").css("color", "#000");
            break;
    }

    if (modeSearch == null) {
        modeSearch = "";
        localStorage.setItem('search_type', "");
    }
    $("body").modalmanager("loading");

    eventAjax(`${location.origin}/?ng=devices/get-devices/${modeSearch}/${modeListTvs}`, "", "GET", (json) => {
        try {
            let filterSelected = localStorage.getItem('search_type');
            $(".typefilter").text(filterSelected);
            var obj = JSON.parse(json);
            obj.forEach(item => {
                let div_rodape;

                if (modeListTvs == 0) {
                    let html_1 = "";
                    if (item.online == 1) {
                        html_1 = `<div class="online"></div> - <span id="span-status-text-tv-${item.ip}">Online</span>`;
                    }
                    else {
                        html_1 = `<div class="offline"  id="${clearIp(item.ip)}"></div> - <span id="span-status-text-tv-${item.ip}">Off-line</span>`;
                    }
                    $("#tbody_list_tvs").append(`<tr id="tr-tv-${item.ip}"><td>${html_1}</td>
                        <td>${item.nome}</td>
                        <td>${item.ip}</td>
                        <td>${item.local_dispositivo}</td>
                        <td>${item.situacao}</td>
                        <td>
                            <div class="dropdown pull-right">
                                <a href="javascript:void(0);" class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown"><i class="fa fa-cogs" aria-hidden="true"></i> Opções</a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href="javascript:void(0);" onclick="editar(${item.id})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Editar</a></li>
                                    <li><a href="javascript:void(0);" onclick="openCPE_AP('${item.tunel}')"><i class="fa fa-desktop" aria-hidden="true"></i> Abrir CPE/AP</a></li>
                                    <li><a href="javascript:void(0);" onclick="ping('${item.ip}')"><i class="fa fa-check-circle" aria-hidden="true"></i> Ping</a></li>
                                    <li><a href="javascript:void(0);" onclick="restartAP('${item.ip}')"><i class="fa fa-refresh" aria-hidden="true"></i> Reiniciar AP</a></li>
                                    <li><a href="javascript:void(0);" onclick="verLogs('${item.ip}')"><i class="fa fa-history" aria-hidden="true"></i> Ver Logs</a></li>
                                    <li><a href="javascript:void(0);"  onclick="excluir(${item.id}')"><i class="fa fa-trash" aria-hidden="true"></i> Excluir</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>`);
                    $("#icon-list-mode").addClass("fa-th");
                    $("#icon-list-mode").removeClass("fa-list");
                    $("#list-model-1").hide();
                    $("#list-model-2").fadeIn();
                }
                else {
                    if (item.timeline != null) {
                        item.file_iframe = `/ui/theme/ibilling/spinner_loading_tvs.html`;
                        if (item.file_iframe == null)
                            item.file_iframe = `/ui/theme/ibilling/spinner_loading_tvs.html`;
                    }
                    else {
                        item.file_iframe = "/ui/theme/ibilling/not_block_from_previw_tv.html";
                    }

                    $("#content_box_tvs").append(`
                        <div id="tv-${item.ip}" class="tv">
                            <div class="header-tv">
                                ${item.online == 1 ? `<div class="online" id="${clearIp(item.ip)}"></div>` : `<div class="offline"  id="${clearIp(item.ip)}"></div>`}
                                <div class="nome-tv tooltip-script" title="${item.nome}">${item.nome}</div>
                                <div class="dropdown">
                                    <i style="cursor:pointer;" class="dropdown-toggle dropdown-toggle-tv" data-toggle="dropdown"><i class="fa fa-sort-desc" aria-hidden="true" style="color:#fff"></i></i>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><a href="javascript:void(0);" onclick="editar(${item.id})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Editar</a></li>
                                        <li><a href="javascript:void(0);" onclick="openCPE_AP('${item.tunel}')"><i class="fa fa-desktop" aria-hidden="true"></i> Abrir CPE/AP</a></li>
                                        <li><a href="javascript:void(0);" onclick="ping('${item.ip}')"><i class="fa fa-check-circle" aria-hidden="true"></i> Ping</a></li>
                                        <li><a href="javascript:void(0);" onclick="restartAP('${item.ip}')"><i class="fa fa-refresh" aria-hidden="true"></i> Reiniciar AP</a></li>
                                        <li><a href="javascript:void(0);" onclick="verLogs('${item.ip}')"><i class="fa fa-history" aria-hidden="true"></i> Ver Logs</a></li>
                                        <li><a href="javascript:void(0);"  onclick="excluir('${item.id}')"><i class="fa fa-trash" aria-hidden="true"></i> Excluir</a></li>
                                    </ul>
                                </div>
                            </div>
                        <div class="play_prev">
                            <h3 class="text-white"> Ip: ${item.ip}</h3>
                            <h1 class="text-white" id="ms-${clearIp(item.ip)}"></h1>
                            <canvas id="ms-graph-${item.ip}" style="width:100%; height:100px"></canvas>
                        </div>
                        ${item.situacao === "ATIVO" ? `<div class="rodape-ativo"></div>` : `<div class="rodape-inativo"></div>`}
                    </div>`);

                    $("#icon-list-mode").addClass("fa-list");
                    $("#icon-list-mode").removeClass("fa-th");
                    $("#list-model-2").hide();
                    $("#list-model-1").fadeIn();
                    $(".tooltip-script").tooltip();
                }

                createGraphMs(`ms-graph-${item.ip}`, item.ip);

            });

            localStorage.setItem('is_tvs_loaded', true);
            $("body").modalmanager("loading");
        } catch (error) {
        }
    });

    setTimeout(() => processPingData(0), 3000);
}

get_list_tvs();

$(document).ready(function () {
    var sysrender = $('#application_ajaxrender');
    $("#list-model-2").removeClass("hidden").hide();

    $(document).on('click', '#add-modal', function (e) {
        e.preventDefault();
        $('body').modalmanager('loading');
        $modal.load(_url + 'modal-tv/', function () {
            $modal.modal();
        });
    });

    $(document).on('click', '#list-mode', function (e) {
        e.preventDefault();
        setModeListTvs();
    });

    $(document).keyup(function (e) {
        if ($("#input-search-disp").is(":focus") && (e.keyCode == 13)) {
            $(".modal-footer button")[1].click()
        }
    });
    var timeOutNameDevice = null;
    $(document).on('input', '#device_name', () => {
        clearTimeout(timeOutNameDevice);
        timeOutNameDevice = setTimeout(() => {
            let text = limparTexto($("#device_name").val());
            $("#device_name").val(text);
        }, 700);
    });
});

document.addEventListener("DOMContentLoaded", function () {
    document.addEventListener("visibilitychange", function () {
        if (document.hidden)
            is_exit_page = true;
        else
            is_exit_page = false;
    });
});

function shuffleArray(arr) {
    for (var j, x, i = arr.length; i; j = Math.floor(Math.random() * i), x = arr[--i], arr[i] = arr[j], arr[j] = x);
    return arr;
}


const editar = function (id) {
    $('body').modalmanager('loading');
    $modal.load(_url + 'modal-tv/' + id, function () {
        $("#code").keyup(() => {
            let cod = $("#code").val();
            //$("#code").val(cod.toLocaleUpperCase());
        });
        $modal.modal();
    });
}

function append_item(name, id) {
    $("#timeLine").append(`<option value="${id}"> ${name}</option>`);
}

const excluir = function (id) {
    let html = `<h2 class="text-dark">Deseja Excluir este dispositivo?</h2>`;
    bootbox.confirm(html, function (is) {
        if (is) {
            $("body").modalmanager("loading");
            $.ajax({
                url: _url + "remove/",
                type: "post",
                data: { id: id },
                success: function (data) {
                    $("body").modalmanager("loading");
                    toastr.success("Aplicado com sucesso.");
                    location.reload();
                },
                error: function (error) {
                    toastr.error("Erro " + error.message);
                }
            });
        }
    });
    $(".modal-footer")[0].childNodes[0].innerText = "Não";
    $(".modal-footer")[0].childNodes[1].innerText = "Sim";
}

const save = function (isReload = true, callback = null) {
    var dispositivo = {
        nome: limparTexto($("#name").val()),
        ip: $("#ip").val(),
        tunel: $("#tunel").val(),
        situacao: $("#situacao").is(':checked') == true ? "ATIVO" : "INATIVO",
        ssid: $("#ssid").val(),
        senha: $("#senha").val(),
        dispositivo_usuario: $("#dispositivo_usuario").val(),
        dispositivo_senha: $("#dispositivo_senha").val(),
        local_dispositivo: $("#local_dispositivo").val(),
        latitude: $("#latitude").val(),
        longitude: $("#longitude").val(),
        notas: $("#notas").val(),
        id: $("#id").val()
    }
    $("body").modalmanager("loading");
    var _url = `${window.location.origin}/?ng=devices/`;
    $.ajax({
        url: _url + "add-device/",
        type: "post",
        data: dispositivo,
        success: function (data) {
            $("body").modalmanager("loading");
            var obj = JSON.parse(data);
            if (obj.status == "SUCESSO") {
                toastr.success("Aplicado com sucesso.");
                if ($("#timeLine").val() != undefined && $("#timeLine").val() != "" && dispositivo.situacao == "ATIVO" && $(`.tv-${dispositivo.codigo}`).hasClass("online") == true)
                    opendDialogPublicar($("#timeLine").val())
                else {
                    $('.modal-header button').click();
                    if (isReload != false)
                        location.reload();
                }
            } else {
                toastr.error(obj.msg);
            }
        },
        error: function (error) {
            toastr.error("Erro ", error.message);
        }
    });
};


const erroDialog = function (text) {
    $("#err-dialog-confirm").text(text)
}




const getTimeLineById = function (id, callback) {
    let url = `${urlOrigin}/?ng=timeline/get-timeline/${id}/1`
    $.ajax({
        url: url,
        data: "",
        type: 'POST',
        contentType: false,
        processData: false,
        success: function (data) {
            callback(data)
        }
    });
}

function limparTexto(texto) {
    if (typeof texto !== 'string') {
        return '';
    }

    // 2. Normaliza a string para a forma de decomposição (NFD).
    //    Isso separa os caracteres base de seus acentos.
    //    Ex: 'á' se torna 'a' + '´' (U+0301)
    const textoNormalizado = texto.normalize('NFD');

    // 3. Usa uma expressão regular para remover os acentos (diacríticos).
    //    A faixa Unicode U+0300 a U+036F contém a maioria dos diacríticos combinados.
    const textoSemAcentos = textoNormalizado.replace(/[\u0300-\u036f]/g, '');

    // 4. Remove todos os caracteres que não são letras (a-z, A-Z), números (0-9), espaços, hífens (-) ou underscores (_).
    //    Em seguida, substitui múltiplos espaços por um único e remove espaços das pontas.
    const textoLimpo = textoSemAcentos
        .replace(/[^a-zA-Z0-9 _-]/g, '')
        .replace(/\s+/g, ' ')
        .trim();

    return textoLimpo;
}

const get_graph_data = function () {
    const xValues = [1,2,3,4,5,6];
    const yValues = [7,8,8,9,2,15];

    new Chart("ms-graph-192.168.8", {
      type: "line",
      data: {
        labels: xValues,
        datasets: [{
          fill: false,
          lineTension: 0,
          backgroundColor: "#18c318",
          borderColor: "#18c318",
          data: yValues
        }]
      },
      options: {
        legend: {display: false},
        scales: {
          yAxes: [],
        }
      }
    });
}

const openCPE_AP = function(tunel) {
    window.open(`http://${tunel}`, '_blank');
}

const clearIp = function(ip){
    return ip.replaceAll(".", "");
}