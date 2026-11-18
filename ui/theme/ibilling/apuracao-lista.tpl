{include file="sections/header.tpl"}
<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title"><h5>Apuração mensal</h5></div>
            <div class="ibox-content">
                <p class="text-muted">Confira as receitas, despesas e o repasse dos lucros de cada competência.</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead><tr><th>Mês da apuração</th><th class="text-right">Dinheiro</th><th class="text-right">Pix</th><th class="text-right">Boleto</th><th class="text-right">Total geral</th><th>Status</th><th class="text-right">Ação</th></tr></thead>
                        <tbody>
                        {foreach $apuracoes as $a}
                            <tr>
                                <td>{date('m/Y', strtotime($a['competencia']))}</td>
                                <td class="text-right">R$ {number_format($a['total_dinheiro'],2,',','.')}</td>
                                <td class="text-right">R$ {number_format($a['total_pix'],2,',','.')}</td>
                                <td class="text-right">R$ {number_format($a['total_boleto'],2,',','.')}</td>
                                <td class="text-right"><strong>R$ {number_format($a['total_receita'],2,',','.')}</strong></td>
                                <td>{if $a['status'] eq 'Fechado'}<span class="label label-success">Fechado</span>{else}<span class="label label-warning">Aberto</span>{/if}</td>
                                <td class="text-right">
                                    {if $a['status'] eq 'Fechado'}
                                        <a class="btn btn-info btn-sm" href="{$_url}apuracao/relatorio/{$a['id']}/"><i class="fa fa-file-text-o"></i> Relatório</a>
                                    {else}
                                        <a class="btn btn-primary btn-sm" href="{$_url}apuracao/fechamento/{$a['id']}/"><i class="fa fa-check-square-o"></i> Fechamento</a>
                                    {/if}
                                </td>
                            </tr>
                        {foreachelse}<tr><td colspan="7" class="text-center">Nenhuma apuração encontrada.</td></tr>{/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
{include file="sections/footer.tpl"}
