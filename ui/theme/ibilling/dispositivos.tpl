<style>
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}
.text-white{
    color: #fff;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
#txt-situacao {
    font-size: 14px;
    margin-left: 1px;
    margin-top: 1px;
    padding: 1px;
}
</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>
        Cadastre um novo AP/CPE para monitorá-lo e controlar em qualquer lugar
    </h3>
</div>
<div class="modal-body">
    <div class="form-group">
        <div class="row">
            <div class="col-md-5">
                <label class="h4 control-label" for="name">* Nome do dispositivo:</label>
                <div class="input-group">
                    <div class="input-group-addon">
                        <span class="fa fa fa-desktop"></span>
                    </div>
                    <input type="text" name="name" id="name" value="{$device->nome}" class="form-control" required/>
                </div>                                        
            </div>
            <div class="col-md-5">
                <label class="h4 control-label" for="ip">* IP do dispositivo:</label>
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-sitemap" aria-hidden="true"></i>
                    </div>
                    <input type="text" name="ip" id="ip" value="{$device->ip}" style="text-transform:uppercase" class="form-control" />
                </div>                                        
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <label class="h4 control-label" for="ssid">* SSID CONECTED:</label>
                <div class="input-group">
                    <div class="input-group-addon">
                        <span class="fa fa fa-wifi"></span>
                    </div>
                    <input type="text" name="ssid" id="ssid" value="{$device->ssid}" class="form-control" required/>
                </div>                                        
            </div>
            <div class="col-md-5">
                <label class="h4 control-label" for="code">Senha wireless:</label>
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-lock" aria-hidden="true"></i>
                    </div>
                    <input type="text" name="senha" id="senha" value="{$device->senha}" style="text-transform:uppercase" class="form-control" />
                </div>                                        
            </div>
        </div>
         <div class="row">
            <div class="col-md-5">
                <label class="h4 control-label" for="dispositivo_usuario">* Login dispositivo:</label>
                <div class="input-group">
                    <div class="input-group-addon">
                        <span class="fa fa-bolt"></span>
                    </div>
                    <input type="text" name="dispositivo_usuario" id="dispositivo_usuario" value="{$device->dispositivo_usuario}" class="form-control" required/>
                </div>                                        
            </div>
            <div class="col-md-5">
                <label class="h4 control-label" for="dispositivo_senha">Senha dispositivo:</label>
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-bolt" aria-hidden="true"></i>
                    </div>
                    <input type="text" name="dispositivo_senha" id="dispositivo_senha" value="{$device->dispositivo_senha}" style="text-transform:uppercase" class="form-control" />
                </div>                                        
            </div>
        </div>

        <div class="row">
            <fieldset>
                <legend>Localização do dispositivo</legend>
                <div class="col-md-5">
                    <label class="h4 control-label" for="latitude">* Latitude:</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <span class="fa fa-map-marker"></span>
                        </div>
                        <input type="text" name="latitude" id="latitude" value="{$device->latitude}" class="form-control" required/>
                    </div>                                        
                </div>
                <div class="col-md-5">
                    <label class="h4 control-label" for="longitude">Longitude:</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-map-marker" aria-hidden="true"></i>
                        </div>
                        <input type="text" name="longitude" id="longitude" value="{$device->longitude}" style="text-transform:uppercase" class="form-control" />
                    </div>                                        
                </div>
            </fieldset>
        </div>

        <div class="row">
        <hr/>
            <div class="col-md-10">
            <label class="h4 control-label" for="tunel">* Endereço do tunel:</label>
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-globe" aria-hidden="true"></i>
                    </div>
                    <input type="text" name="tunel" id="tunel" value="{$device->tunel}" class="form-control"/>
                </div>                                        
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <label class="h4 control-label" for="situacao">* Situação: <span id="txt-situacao">{if $device->situacao eq "ATIVO"}ATIVO{else}INATIVO{/if}</span>  </label>
                <div class="input-group">
                        <label class="switch">
                            {if $device->situacao eq "ATIVO"}
                                <input type="checkbox" id="situacao" checked>
                            {else}
                                <input type="checkbox" id="situacao">
                            {/if}
                            <span class="slider round"></span>
                        </label>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-5">
                    <label class="h4 control-label" for="local_dispositivo">* Local do dispositivo:</label>
                    <select id="local_dispositivo" name="local_dispositivo" class="form-control">
                            <option value="">Selecione o local da instalação </option>
                            {if $device->id eq 0}
                                <option value="Casa do cliente">Casa do cliente </option>
                                <option value="Repetidora">Repetidora </option>
                                <option value="Maquina Virtual">Maquina Virtual </option>
                            {else}
                                <option value="Casa do cliente" {if $device->local_dispositivo eq "Casa do cliente"} selected {/if}>Casa do cliente </option>
                                <option value="Repetidora" {if $device->local_dispositivo eq "Repetidora"} selected {/if}>Repetidora </option>
                                <option value="Maquina Virtual" {if $device->local_dispositivo eq "Maquina Virtual"} selected {/if}>Maquina Virtual </option>
                            {/if}
                    </select>
                </div>
            </div>
            <input type="hidden" id="id" value="{$device->id}">    
        </div>
        <div class="row">
            <div class="form-group">
                <div class="col-md-10">
                    <label class="h4 control-label" for="notas">Notas do dispositivo:</label>
                    <textarea class="form-control" id="notas" style="background: #04f5be26; border-radius: 10px; height: 79px">{$device->notas}</textarea>
                </div>
            </div>
        </div>
        <hr/>
    </div>
</div>

<div class="modal-footer">
    <button type="button" data-dismiss="modal" class="btn btn-danger">{$_L['No']}</button>
    <a href="#" class="btn btn-primary" id="modal_submit_confirm" onclick="save()"><i class="fa fa-check"></i> {$_L['Save']}</a>
</div>

<script>
    $('.toggle-on-off').click(function() {
        $(this).find('.btn').toggleClass('active');  
        if ($(this).find('.btn-primary').size()>0) {
            $(this).find('.btn').toggleClass('btn-primary');
        }
    })

    $('#situacao').click(function() {
        var situacao = this.checked == true ? "ATIVO" : "INATIVO";
        $('#txt-situacao').text(situacao);
    });

    $('#reproducao').click(function() {
        var mode = this.checked == true ? "ATIVO" : "INATIVO";
        $('#txt-reproducao').text(mode);
    });
</script>