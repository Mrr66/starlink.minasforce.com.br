<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>
        {$_L['Notice']}
    </h3>
</div>
<div class="modal-body">

    <form class="form-horizontal" id="ib_modal_form">

         <form>
           <p>
                <label for="ex2">* <b style="color: red">Status</b> 0 Criado, 1 Aberto, 2 Processando, 3 Concluído, 4 Expirado</label>
           </p>
            <div class="form-group row">
             <div class="col-xs-2">
                <label for="ex2">Status</label>
                <input class="form-control" name="status" id="status" type="number" value="{$d->status}">
              </div>
              <div class="col-xs-2">
                <label for="ex1">N° Fatura</label>
                <input class="form-control" id="codeInvoice" type="text" name="codeInvoice" value="{$d->codeInvoice}" disabled />
              </div>
              
              <div class="col-xs-2">
                <label for="ex3">Valor</label>
                <input class="form-control" name="value" id="value" type="text" value="{$d->value}" disabled>
              </div>
              <div class="col-xs-6">
                <label for="ex3">Link Pagamento</label>
                <input class="form-control" name="pay" id="pay" type="text" value="{$d->pay}" disabled>
              </div>
            </div>

            <div class="form-group row">
              <div class="col-xs-3">
                <label for="ex1">Aberta</label>
                <input class="form-control" id="open" type="text" name="open" value="{date( $_c['df'], strtotime($d->open))}" disabled />
              </div>
              <div class="col-xs-3">
                <label for="ex2">Fechada</label>
                <input class="form-control" name="close" id="close" type="text" value="{date( $_c['df'], strtotime(date("Y-m-d")))}" disabled>
              </div>
              <div class="col-xs-3">
                <label for="ex3">Vencimento</label>
                <input class="form-control" name="dueDate" id="dueDate" type="text" value="{date( $_c['df'], strtotime($d->dueDate))}" disabled>
              </div>
            </div>
            <div class="form-group row">
              <div class="col-xs-12">
                <label for="ex1">Link da fatura</label>
                <input class="form-control" id="open" type="text" name="open" value="{$d->urlInvoice}" disabled />
              </div>
            </div>
            <div class="form-group row">
              <div class="col-xs-6">
                <label for="ex1">Cliente Id</label>
                <input class="form-control" id="open" type="text" name="open" value="{$d->clienteId}" disabled />
              </div>
              <div class="col-xs-6">
                <label for="ex1">Id da fatura no Conexão Super</label>
                <input class="form-control" id="open" type="text" name="open" value="{$d->id}" disabled />
              </div>
            </div>
            <div class="form-group row">
              <div class="col-xs-12">
                <label for="ex1">Link da nota fiscal</label>
                <input class="form-control" id="notaFiscal" type="text" name="notaFiscal" value="{$d->notaFiscal->url}" />
              </div>
            </div>
        </form>

    </form>

</div>
<div class="modal-footer">
    <button type="button" data-dismiss="modal" class="btn btn-danger">{$_L['Cancel']}</button>
    <a href="#" class="btn btn-info modal_submit" id="modal_submit"><i class="fa fa-check"></i> {$_L['Save']}</a>
</div>