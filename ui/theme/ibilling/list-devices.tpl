{include file="sections/header.tpl"}
<head>
    <link href="{$_theme}/css/jquery-ui.css" rel="stylesheet">
    <script src="{$_theme}/js/jquery-1.10.2.js"></script>
    <script src="{$_theme}/js/jquery-3.6.0.min.js"></script>
    <style>
    
    #page-wrapper{
            overflow-x: auto !important;
    }
    .ibox-tools a {
        cursor: pointer;
        margin-left: 5px;
        margin-top: -110%;
        padding: 5px;
    }
    .ui-dialog.ui-widget.ui-widget-content{
        position: fixed !important;
    }
    .content{
        width:100%;
        height:100%;
    }

    #ms-graph{
        width:100%;
        height:100px;
    }

    .tv{
        width: 15em;
        height: 15em;
        background: linear-gradient(#021e1d, #000044);
        filter: drop-shadow(6px 6px 1px rgba(0, 0, 0, 0.35));
        transition: all 0.5s;
        float: left;
        margin: 1% 1.2%;
        border-radius: 6px;
    }
    .tv:hover {
        -webkit-filter: drop-shadow(15px 10px 5px rgba(0, 0, 0, .5));
        filter: drop-shadow(15px 10px 5px rgba(0, 0, 0, .5));
        -webkit-transform: scale(1.008);
        transform: scale(1.008);
    }
    .rodape-ativo{
        height: 10px;
        width: 100%;
        background: #75eb67;
        border-bottom-right-radius: 5px;
        border-bottom-left-radius: 5px;
    }
    .rodape-inativo{
        height: 10px;
        width: 100%;
        background: #ff0000ff;
        border-bottom-right-radius: 5px;
        border-bottom-left-radius: 5px;
    }

    .dropdown-toggle-tv{
        margin-right: 3%;
        float: right;
    }

    .code-tv{
        margin-top: -13%;
        text-align: center;
        color: #fff;
        font-size: 20px;
        height: 10px;
    }

    .nome-tv{
        max-width: 100%;
        height: 100%;
        color: #fff;
        font-size: 13px;
        text-overflow: ellipsis;
        word-wrap: break-word;
        overflow: hidden;
        text-shadow: 1px 1px 1px #222;
        font-weight: bold;
        text-align: center;
    }
    .header-tv {
        display: flex;
        justify-content: space-between;
        padding: 5px;
        align-items: center;
        height: 32px;
    }
    .online {
        min-width: 20px;
        min-height: 20px;
        width: 20px;
        height: 20px;
        background: #18c318;
        box-shadow: 1px 1px 8px #75eb67;
        border-radius: 10px;
    }
    .progress-publicacao{
        width: 94%;
    }
    .sem-internet {
        min-width: 20px;
        min-height: 20px;
        width: 20px;
        height: 20px;
        background: #ffc107;
        box-shadow: 1px 1px 8px #ffc107;
        border-radius: 10px;
    }
    .status-ping-tv {
        min-width: 20px;
        min-height: 20px;
        width: 20px;
        height: 20px;
        background: #ffc107;
        box-shadow: 1px 1px 8px #ffc107;
        border-radius: 10px;
    }
    .offline {
        min-width: 20px;
        min-height: 20px;
        width: 20px;
        height: 20px;
        background: #b50202;
        box-shadow: 1px 1px 8px #b50202;
        border-radius: 10px;
    }
    .play_prev{
        width: 100%;
        height: calc(100% - 42px);
    }
    .play_prev iframe {
        width: 100%;
        height: 100%;
        border: none;
    }
    img {
        vertical-align: middle;
        max-height: 107%;
    }
    .input-search-disp{
        border-radius: 8px;
    }
    .div-filter-dispositivo ul {
        margin-top: -34%;
        margin-left: 5%;
    }

    .div-filter-dispositivo ul li {
        margin: 5%;
        cursor: pointer;
    }

    .div-filter-dispositivo ul li:hover {
        -webkit-transform: scale(1.1.1);
        transform: scale(1.05);
    }

    .tslect li {
        padding: 5px;
        cursor: pointer;
    }
    .text-white{
        color: #fff;
        text-align: center;
    }
    </style>
</head>

<div class="row">

    <div class="col-md-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5><i class="fa fa-desktop"></i> {$_L['Devices']}</h5>
                <div class="ibox-tools" style="gap: 3px;display: flex;padding-right: 10px;">
                    <button href="javascript:void(0);" title="Alterar o modo de visualização das dispositivo" class="btn btn-success btn-xs mg-1" id="list-mode"><i id="icon-list-mode" class="fa fa-list"></i></button>
                    <div>
                        <button href="javascript:void(0);" title="Filtrar dispositivo" class="btn btn-primary btn-xs mg-1 dropdown-toggle" type="button" data-toggle="dropdown"><i class="fa fa-search"></i> {$_L['Filter by']}: <span class="typefilter"></span></button>
                        <ul class="dropdown-menu tslect">
                            <li style="color: black;" onclick="select_type_search(``);">Todas</li>
                            <li style="color: #75eb67;" onclick="select_type_search(`online`);">Online</li>
                            <li style="color: red;" onclick="select_type_search(`offline`);">Off-Line</li>
                            <li style="color: black;" onclick="select_type_search(`Casa do cliente`);">Casa do cliente</li>
                            <li style="color: black;" onclick="select_type_search(`Repetidora`);">Repetidora</li>
                        </ul>
                    </div>
                    <button href="javascript:void(0);" title="Adicionar um novo dispositivo" class="btn btn-success mg-1 btn-xs" id="add-modal"><i class="fa fa-desktop"></i> {$_L['Add']}</button>
                </div>
            </div>
            <div class="ibox-content">
                <div class="row" id="list-model-1">
                    <div class="tv-ativo" id="content_box_tvs">
                    </div>
                </div>
                <div id="list-model-2" class="hidden">
                    <div class="table-responsive">
                        <table class="table table-bordered roles no-margin">
                            <thead>
                            <tr>
                                <th class="w-25">Status</th>
                                <th class="w-10">{$_L['Name']}</th>
                                <th class="text-center bold">IP</th>
                                <th class="text-center bold">Endereço</th>
                                <th class="text-center bold">Situação</th>
                                <th class="text-center bold" style="width:0;">{$_L['Action']}</th>
                            </tr>
                            </thead>
                            <tbody id="tbody_list_tvs">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<div id="dialog-confirm" title="">
    <h3 class="style-text-dialog  w3-animate-opacity" id="text-dialog-confirm"></h3>
    <h3 id="err-dialog-confirm"></h3>
    <div class="progress-publicacao hidden">
        <div class="w3-light-grey w3-round-xlarge">
          <div id="progress-bar" class="w3-container w3-blue w3-round-xlarge w3-center" style="width:0%">0%</div>
        </div>
        <h4 id="nameDownloadBlock" style="font-size: 12px;">Download bloco</h4>
        <div class="w3-light-grey w3-tiny">
          <div id="progress-block" class="w3-container w3-blue w3-round-xlarge w3-center" style="width:0%">0%</div>
        </div>
    </div>
</div>
<span class="hidden" id="return-search">{$search}</span>
{include file="sections/footer.tpl"}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>