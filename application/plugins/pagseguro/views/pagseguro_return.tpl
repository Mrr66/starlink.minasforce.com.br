<!DOCTYPE html>
<html lang="pt_BR">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pagamento da Fatura {$d['id']}</title>

    <link href="{$_theme}/lib/fa/css/font-awesome.min.css" rel="stylesheet">
    <link href="{$_theme}/lib/icheck/skins/all.css" rel="stylesheet">

    {if isset($xheader)}
        {$xheader}
    {/if}
    <style>
        body{
            background-color: #FAFAFA;
        }
    </style>
</head>

<body>
        <div class="container">
                <div class="py-5 text-center">
                        <h2 class="h2 mt-none mb-sm text-dark text-bold">Pagamento da Fatura #{$d['id']}</h2>
                      </div>
            <div class="row">
            <form id="pagseg_form" method="POST" action="{$form_action['url']}">
                <input type="hidden" id="itemAmount1" name="itemAmount1" value="{$d['subtotal']}">
                <input type="hidden" id="itemQuantity1" name="itemQuantity1" value="1">
                <input type="hidden" id="itemId1" name="itemId1" value="{$d['id']}">
                <input type="hidden" id="reference" name="reference" value="REF{$d['id']}">
                <input type="hidden" id="itemDescription1" name="itemDescription1" value="Pagamento da Fatura: #{$d['id']}">
            <div class="row">
                <div id="primeiro_form" class="col-sm-12 p-3 mb-5 card">
                    <div class="card-body">
                            <div id="notice" class="alert alert-danger" role="alert"></div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                        <label for="senderName">Nome</label>
                                        <input type="text" class="form-control" id="senderName" placeholder="Nome" required>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="senderEmail">E-mail</label>
                                        <input type="email" class="form-control" id="senderEmail" placeholder="E-mail" required>
                                    </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                        <label for="senderCPF">CPF</label>
                                        <input type="text" class="form-control" id="senderCPF" placeholder="CPF" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="senderPhone">Telefone</label>
                                        <input type="text" class="form-control" id="senderPhone" placeholder="Telefone" required>
                                    </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-8">
                                        <label for="billingAddressStreet">Endereço</label>
                                        <input type="text" class="form-control" id="billingAddressStreet" placeholder="Endereço">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="billingAddressNumber">Número</label>
                                        <input type="text" class="form-control" id="billingAddressNumber" placeholder="Número">
                                    </div>
                                    <div class="form-group col-md-6">
                                           <label for="billingAddressComplement">Complemento</label>
                                        <input type="text" class="form-control" id="billingAddressComplement" placeholder="Complemento">
                                    </div>
                                    <div class="form-group col-md-6">
                                            <label for="billingAddressDistrict">Bairro</label>
                                         <input type="text" class="form-control" id="billingAddressDistrict" placeholder="Bairro">
                                     </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="billingAddressCity">Cidade</label>
                                    <input type="text" class="form-control" id="billingAddressCity" value="Sao Paulo">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="billingAddressState">Estado</label>
                                    <select id="billingAddressState" class="form-control">
                                            <option value="">Selecione uma opção…</option>
                                            <option value="AC">Acre</option>
                                            <option value="AL">Alagoas</option>
                                            <option value="AP">Amapá</option>
                                            <option value="AM">Amazonas</option>
                                            <option value="BA">Bahia</option>
                                            <option value="CE">Ceará</option>
                                            <option value="DF">Distrito Federal</option>
                                            <option value="ES">Espírito Santo</option>
                                            <option value="GO">Goiás</option>
                                            <option value="MA">Maranhão</option>
                                            <option value="MT">Mato Grosso</option>
                                            <option value="MS">Mato Grosso do Sul</option>
                                            <option value="MG">Minas Gerais</option>
                                            <option value="PA">Pará</option>
                                            <option value="PB">Paraíba</option>
                                            <option value="PR">Paraná</option>
                                            <option value="PE">Pernambuco</option>
                                            <option value="PI">Piauí</option>
                                            <option value="RJ">Rio de Janeiro</option>
                                            <option value="RN">Rio Grande do Norte</option>
                                            <option value="RS">Rio Grande do Sul</option>
                                            <option value="RO">Rondônia</option>
                                            <option value="RR">Roraima</option>
                                            <option value="SC">Santa Catarina</option>
                                            <option value="SP">São Paulo</option>
                                            <option value="SE">Sergipe</option>
                                            <option value="TO">Tocantins</option>
                                    </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="billingAddressPostalCode">CEP</label>
                                        <input type="text" class="form-control" id="billingAddressPostalCode">
                                    </div>
                            </div>
                        </div>
                    </div>
                    <button id="pagseg_redirect" type="button" class="btn btn-primary btn-lg btn-block">EFETUAR PAGAMENTO</button>
                </form>
                </div>
                </div>
            </div>
        </div>

</div>

<!-- Mainly scripts -->
<script src="{$_theme}/js/jquery-1.10.2.js"></script>
<script src="{$_theme}/js/jquery-ui-1.10.4.min.js"></script>
<script>
    var _L = [];
    var config_animate = 'No';
    {if ($_c['animate']) eq '1'}
    var config_animate = 'Yes';
    {/if}
    {$jsvar}
</script>
<script src="{$_theme}/js/bootstrap.min.js"></script>
<script src="{$_theme}/js/jquery.metisMenu.js"></script>
<script src="{$_theme}/js/jquery.slimscroll.min.js"></script>
<script src="{$url_mask_js}"></script>
<!-- Custom and plugin javascript -->
<script src="{$_theme}/lib/moment.js"></script>

<script src="{$_theme}/js/app.js"></script>
<script src="{$_theme}/js/pace.min.js"></script>
<script src="{$_theme}/lib/progress.js"></script>
<script src="{$_theme}/lib/bootbox.min.js"></script>

<!-- iCheck -->
<script src="{$_theme}/lib/icheck/icheck.min.js"></script>

{if isset($xfooter)}
    {$xfooter}
{/if}
<script>
    jQuery(document).ready(function() {
        // initiate layout and plugins

        {if isset($xjq)}
        {$xjq}
        {/if}

    });

</script>
</body>

</html>