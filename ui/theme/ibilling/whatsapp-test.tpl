{include file="sections/header.tpl"}

<div class="row">
    <div class="col-md-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Testar envio pelo WhatsApp</h5>
            </div>
            <div class="ibox-content">
                <div id="emsg" class="alert alert-info" style="display: none;">
                    <span id="emsgbody"></span>
                </div>

                <form action="{$_url}whatsapp/test_send" method="post" class="form-horizontal" id="ibox_form">
                    <div class="form-group">
                        <label for="number" class="col-md-2 control-label">Número completo</label>
                        <div class="col-md-10">
                            <input type="tel" name="number" id="number" class="form-control"
                                   placeholder="Ex.: 5531987654321" inputmode="numeric"
                                   autocomplete="tel" required>
                            <span class="help-block">Informe código do país, DDD e número.</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="message" class="col-md-2 control-label">{$_L['Text']}</label>
                        <div class="col-md-10">
                            <textarea name="message" id="message" class="form-control" rows="6"
                                      maxlength="4000" required></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-offset-2 col-md-10">
                            <button class="btn btn-info" type="submit" id="submit_test">
                                <i class="fa fa-whatsapp"></i> {$_L['Send']}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}
