{if $apuracao_pendente}
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-warning" style="border-left:5px solid #f8ac59;box-shadow:0 1px 3px rgba(0,0,0,.12);padding:18px 20px;">
            <a href="{$_url}apuracao/index/" class="btn btn-warning pull-right" style="margin-left:15px;">
                <i class="fa fa-calculator"></i> Fazer fechamento
            </a>
            <h4 style="margin-top:0;"><i class="fa fa-exclamation-triangle"></i> Fechamento mensal pendente</h4>
            <span>É necessário realizar a apuração e o fechamento de <strong>{date('m/Y', strtotime($apuracao_competencia))}</strong>.</span>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
{/if}

