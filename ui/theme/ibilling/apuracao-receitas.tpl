{include file="sections/header.tpl"}
<div class="row"><div class="col-lg-12"><div class="ibox float-e-margins">
    <div class="ibox-title">
        <form method="post" action="{$_url}apuracao/reprocessar/" class="pull-right" onsubmit="return confirm('Reprocessar a apuração removerá todas as aprovações e classificações de despesas já realizadas. Deseja continuar?');">
            <input type="hidden" name="csrf" value="{$csrf}">
            <input type="hidden" name="apuracao_id" value="{$a['id']}">
            <button type="submit" class="btn btn-warning btn-xs"><i class="fa fa-refresh"></i> Reprocessar receitas</button>
        </form>
        <h5>Fechamento — receitas de {date('m/Y', strtotime($a['competencia']))}</h5>
    </div>
    <div class="ibox-content">
        <div class="alert alert-info">Etapa 1 de 3: confira e aprove cada arrecadação do mês.</div>
        {if $usuario_logado_apuracao neq ''}
            <div class="alert alert-success"><i class="fa fa-user"></i> Você está validando como <strong>{$usuario_logado_apuracao}</strong>. Somente as arrecadações recebidas por você podem ser aprovadas.</div>
        {else}
            <div class="alert alert-danger"><i class="fa fa-lock"></i> Seu usuário não está identificado como Marcos ou Vinícius e não pode aprovar arrecadações.</div>
        {/if}
        {if $total_faturas_pendentes > 0}
            <div class="alert alert-danger">
                <h4><i class="fa fa-exclamation-triangle"></i> Apuração bloqueada</h4>
                Existem <strong>{$total_faturas_pendentes} fatura(s) em aberto</strong> com vencimento nesta competência. A apuração só poderá continuar quando todos os pagamentos forem identificados pelo sistema.
            </div>
            <h4>Faturas aguardando pagamento</h4>
            <div class="table-responsive"><table class="table table-bordered table-striped">
                <thead><tr><th>#</th><th>Cliente</th><th>Vencimento</th><th class="text-right">Valor</th><th>Situação</th><th class="text-center">Ação</th></tr></thead>
                <tbody>{foreach $faturas_pendentes as $f}<tr>
                    <td>{$f['id']}</td>
                    <td>{$f['account']}</td>
                    <td>{date('d/m/Y', strtotime($f['duedate']))}</td>
                    <td class="text-right">R$ {number_format($f['total'],2,',','.')}</td>
                    <td>{if $f['status'] eq 'Partially Paid'}<span class="label label-warning">Parcialmente paga</span>{else}<span class="label label-danger">Pendente</span>{/if}</td>
                    <td class="text-center"><a class="btn btn-info btn-xs" href="{$_url}invoices/view/{$f['id']}/"><i class="fa fa-eye"></i> Ver fatura</a></td>
                </tr>{/foreach}</tbody>
            </table></div>
            <hr>
        {/if}
        <div class="table-responsive"><table class="table table-bordered table-striped">
            <thead><tr><th>Data</th><th>Descrição</th><th>Conta</th><th>Recebido por</th><th>Método</th><th class="text-right">Valor</th><th class="text-center">Situação</th></tr></thead>
            <tbody>{foreach $receitas as $r}<tr>
                <td>{date('d/m/Y', strtotime($r['date']))}</td><td>{$r['description']}</td><td>{$r['account']}</td><td>{if $r['recebido_por'] eq 'Vinícius'}<span class="label label-info">Vinícius</span>{else}<span class="label label-warning">Marcos</span>{/if}</td><td>{$r['method']}</td><td class="text-right">R$ {number_format($r['amount'],2,',','.')}</td>
                <td class="text-center">{if $r['approved']}<span class="label" style="background-color:#18a689;color:#fff;"><i class="fa fa-check"></i> Aprovada</span>{elseif $total_faturas_pendentes > 0}<button class="btn btn-default btn-xs" disabled title="Existem faturas em aberto"><i class="fa fa-lock"></i> Bloqueado</button>{elseif !$r['pode_aprovar']}<button class="btn btn-default btn-xs" disabled title="Somente {$r['recebido_por']} pode aprovar"><i class="fa fa-lock"></i> Aguarda {$r['recebido_por']}</button>{else}<form method="post" action="{$_url}apuracao/aprovar-receita/" style="display:inline"><input type="hidden" name="csrf" value="{$csrf}"><input type="hidden" name="apuracao_id" value="{$a['id']}"><input type="hidden" name="item_id" value="{$r['id']}"><button class="btn btn-success btn-xs" type="submit"><i class="fa fa-check"></i> Aprovar</button></form>{/if}</td>
            </tr>{foreachelse}<tr><td colspan="7" class="text-center">Não há receitas nessa competência.</td></tr>{/foreach}</tbody>
        </table></div>
        <div class="clearfix"><a href="{$_url}apuracao/index/" class="btn btn-default">Voltar</a>{if $total_faturas_pendentes > 0}<button class="btn btn-danger pull-right" disabled><i class="fa fa-lock"></i> Aguardando pagamento das faturas</button>{elseif $pendentes eq 0}<a href="{$_url}apuracao/despesas/{$a['id']}/" class="btn btn-primary pull-right">Próximo <i class="fa fa-arrow-right"></i></a>{else}<button class="btn btn-primary pull-right" disabled>Aprove todas para continuar</button>{/if}</div>
    </div>
</div></div></div>
{include file="sections/footer.tpl"}
