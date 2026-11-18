{include file="sections/header.tpl"}
    
    <style>
        #map {
            height: 790px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .info-window {
            font-family: sans-serif;
            line-height: 1.4;
        }
        .online{
            background-color: #04cc79ff;
            color: #f3f3f3ff;
            font-family: 'Open Sans';
            font-size: 11px;
            font-weight: 600;
            padding-bottom: 4px;
            padding-left: 6px;
            padding-right: 6px;
            text-shadow: none;
        }
        .offline{
            background-color: #a70202ff;
            color: #e2e2e2ff;
            font-family: 'Open Sans';
            font-size: 11px;
            font-weight: 600;
            padding-bottom: 4px;
            padding-left: 6px;
            padding-right: 6px;
            text-shadow: none;
        }
        
    </style>
</head>
<body class="bg-light">
        <div class="row">
            <div class="col-md-10">
                <div id="map"></div>
            </div>
            <div class="col-md-2">
                <div class="card">
                    <div class="card-header bg-white font-weight-bold">Lista de Equipamentos</div>
                    <ul class="list-group list-group-flush" id="lista-antenas" style="max-height: 800px; overflow: auto"></ul>
                </div>
            </div>
        </div>
</body>
    
<script>
{literal}
const eventAjax = function (url, formData, type, callback) {
    $.ajax({
        url: url,
        data: formData,
        type: type,
        contentType: false,
        processData: false,
        success: function (r) {
            callback(r);
        }
    });
}

function renderMap(antenas) {
    const bounds = new google.maps.LatLngBounds();
    const map = new google.maps.Map(document.getElementById("map"), {
        mapTypeId: 'hybrid' 
    });
    
    antenas.forEach(antena => {
        if(antena.latitude == "" || antena.latitude == 0)
            return;
        const antenaCoord = { lat: parseFloat(antena.latitude), lng: parseFloat(antena.longitude) };
        bounds.extend(antenaCoord);

        // Define as cores baseadas no status
        const corStatus = antena.online === "1" ? "#00FF00" : "#FF0000"; // Verde ou Vermelho
        const classeTextoStatus = antena.online === "1" ? "text-success" : "text-danger";

        // Marcador
        const marker = new google.maps.Marker({
            position: antenaCoord,
            map: map,
            title: antena.nome,
            // Opcional: ícone muda de cor se quiser, mas manteremos o padrão por enquanto
            icon: antena.local_dispositivo === 'Repetidora' ? 'https://starlink.minasforce.com.br/application/storage/system/tower.png' : 
                  (antena.online === "1" ? 'http://maps.google.com/mapfiles/ms/icons/green-dot.png' : 'http://maps.google.com/mapfiles/ms/icons/red-dot.png')
        });

        // Desenho da Linha
        if (antena.ssid) {
            const pai = antenas.find(a => a.nome === antena.ssid);
            if (pai) {
                new google.maps.Polyline({
                    path: [antenaCoord, { lat: parseFloat(pai.latitude), lng: parseFloat(pai.longitude) }],
                    geodesic: true,
                    strokeColor: corStatus, // AQUI: A linha assume a cor do status
                    strokeOpacity: 1.0,
                    strokeWeight: antena.local_dispositivo === 'Repetidora' ? 4 : 2,
                    map: map
                });
            }
        }

        // Lista lateral com JQuery
        $("#lista-antenas").append(`
            <li class="list-group-item">
                <span class="badge ${antena.online === "1" ? 'online bg-success' : 'offonline bg-danger'} float-end">
                    ${antena.online === "1" ? 'ONLINE' : 'OFF-LINE'}
                </span>
                <strong>${antena.nome}</strong><br>
                <code class="${classeTextoStatus} fw-bold">${antena.ip}</code>
            </li>
        `);
    });

    map.fitBounds(bounds);
}

function initMap(){
    eventAjax(`${location.origin}/?ng=devices/get-devices/`, "", "GET", (json) => {
        console.log(json);
        const list = JSON.parse(json);
        const obj = list.filter(i => i.situacao != "INATIVO")
        renderMap(obj);
        console.log(obj);
    });
}
{/literal}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCBjp43XadnhXGZhVCRYXebo3PYrnmG3N8&callback=initMap" async defer></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

{include file="sections/footer.tpl"}
