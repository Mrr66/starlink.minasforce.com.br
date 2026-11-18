{include file="sections/header.tpl"}
<div class="row"><div class="col-lg-12"><div class="ibox float-e-margins">
    <div class="ibox-title"><h5>Fechamento — despesas de {date('m/Y', strtotime($a['competencia']))}</h5></div>
    <div class="ibox-content">
        <div class="alert alert-info">Etapa 2 de 3: informe quem pagou cada gasto. Use “Compartilhado” quando o valor saiu do caixa comum.</div>
        <form method="post" action="{$_url}apuracao/salvar-despesas/">
            <input type="hidden" name="csrf" value="{$csrf}"><input type="hidden" name="apuracao_id" value="{$a['id']}">
            <div class="table-responsive"><table class="table table-bordered table-striped">
                <thead><tr><th>Data</th><th>Descrição</th><th>Categoria</th><th class="text-right">Valor</th><th>Pago por</th></tr></thead>
                <tbody>{foreach $despesas as $d}<tr>
                    <td>{date('d/m/Y', strtotime($d['date']))}</td><td>{$d['description']}</td><td>{$d['category']}</td><td class="text-right">R$ {number_format($d['amount'],2,',','.')}</td>
                    <td><select class="form-control" name="payer_{$d['id']}" required><option value="" {if $d['payer_user_id'] eq -1}selected{/if}>Selecione...</option><option value="0" {if $d['payer_user_id'] eq 0}selected{/if}>Compartilhado</option>{foreach $usuarios_apuracao as $u}<option value="{$u['id']}" {if $d['payer_user_id'] eq $u['id']}selected{/if}>{$u['fullname']}</option>{/foreach}</select></td>
                </tr>{foreachelse}<tr><td colspan="5" class="text-center">Não há despesas nessa competência.</td></tr>{/foreach}</tbody>
            </table></div>
            <div class="clearfix"><a href="{$_url}apuracao/fechamento/{$a['id']}/" class="btn btn-default">Voltar</a><button class="btn btn-primary pull-right" type="submit">Próximo <i class="fa fa-arrow-right"></i></button></div>
        </form>
    </div>
</div></div></div>
{include file="sections/footer.tpl"}
