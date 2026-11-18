{include file="sections/header.tpl"}

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="col-md-1">
                    <img src="{$app_url}/application/storage/system/cao.jpg" width="100%">
                </div>
                <div class="col-md-11">
                    O <b>Minas Force Dog</b> é um utilitário avançado de <b>cão de guarda</b> (watchdog) que opera dentro dos rádios Ubiquiti e Mikrotik. Ele realiza análises de sinal, enlace e taxa de transferência de pacotes (CCQ), sendo capaz de ajustar o rádio automaticamente para corrigir ou minimizar problemas. Toda a configuração é realizada através desta tela.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th class="bold">{$_L['Name']}</th>
                            <th class="bold">{$_L['CCQ']}</th>
                            <th class="bold">{$_L['Signal']}</th>
                            <th class="bold">{$_L['Uptime']}</th>
                            <th class="bold">{$_L['UptimeLan']}</th>
                            <th class="bold">{$_L['Ap Mac']}</th>
                            <th class="bold">{$_L['Operação']}</th>
                            <th class="bold">{$_L['Essid']}</th>
                            <th class="bold">{$_L['Status']}</th>
                            <th class="text-center bold">{$_L['Manage']}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $devices as $item}
                            <tr data-id="{$item['id']}">
                                <td id="name_{str_replace('.', '_', $item['ip'])}">{$item['nome']}</td>
                                <td id="ccq_{str_replace('.', '_', $item['ip'])}"></td>
                                <td id="signal_{str_replace('.', '_', $item['ip'])}"></td>
                                <td id="uptime_{str_replace('.', '_', $item['ip'])}"></td>
                                <td id="wlanUptime_{str_replace('.', '_', $item['ip'])}"></td>
                                <td id="apMac_{str_replace('.', '_', $item['ip'])}"></td>
                                <td id="wlanOpmode_{str_replace('.', '_', $item['ip'])}"></td>
                                <td id="essid_{str_replace('.', '_', $item['ip'])}"></td>
                                <td id="situacao_{str_replace('.', '_', $item['ip'])}"></td>
                                <td> <a href="https://station.minasforce.com.br/?ip={($item['ip'] == '192.168.88.20') ? '192.168.88.219' : $item['ip']}" target="__blank"><i class="fa fa-share-square-o" aria-hidden="true"></i> Abrir</td>
                            </tr>
                        {/foreach}

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>



</div>

<div class="md-fab-wrapper">
    <a class="md-fab md-fab-primary waves-effect waves-light add_minas_force_dog" href="#">
        <i class="fa fa-plus"></i>
    </a>
</div>

{include file="sections/footer.tpl"}
