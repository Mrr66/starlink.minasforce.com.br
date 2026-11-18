{include file="sections/header.tpl"}
<div class="row"><div class="col-lg-12"><div class="ibox float-e-margins">
    <div class="ibox-title"><h5>Relatório da apuração — {date('m/Y', strtotime($a['competencia']))}</h5></div>
    <div class="ibox-content">
        {if $a['status'] eq 'Fechado'}<div class="alert alert-success"><i class="fa fa-lock"></i> Apuração finalizada em {date('d/m/Y H:i', strtotime($a['finalized_at']))}. Este relatório não pode mais ser alterado.</div>{else}<div class="alert alert-info">Etapa 3 de 3: revise os valores antes de finalizar.</div>{/if}
        <div class="row">
            <div class="col-md-3"><div class="well"><small>Receita total</small><h3>R$ {number_format($a['total_receita'],2,',','.')}</h3></div></div>
            <div class="col-md-3"><div class="well"><small>Despesas</small><h3>R$ {number_format($a['total_despesa'],2,',','.')}</h3></div></div>
            <div class="col-md-3"><div class="well"><small>Lucro líquido</small><h3>R$ {number_format($calculo['lucro_liquido'],2,',','.')}</h3></div></div>
            <div class="col-md-3"><div class="well"><small>Outras receitas</small><h3>R$ {number_format($a['total_outras_receitas'],2,',','.')}</h3></div></div>
        </div>
        <h4>Repasse dos sócios</h4>
        <div class="table-responsive"><table class="table table-bordered">
            <thead><tr><th>Usuário</th><th class="text-right">Receitas já recebidas</th><th class="text-right">Parte do lucro</th><th class="text-right">Gastos pagos</th><th class="text-right">Acerto</th></tr></thead>
            <tbody>{foreach $calculo['repasses'] as $r}<tr><td>{$r['fullname']|default:$r['user_name']}</td><td class="text-right">R$ {number_format($r['receitas_recebidas'],2,',','.')}</td><td class="text-right">R$ {number_format($r['lucro'],2,',','.')}</td><td class="text-right">R$ {number_format($r['despesas_pagas'],2,',','.')}</td><td class="text-right">{if $r['valor_repasse'] >= 0}<span class="label" style="background:#18a689;color:#fff;">Deve receber</span> <strong>R$ {number_format($r['valor_repasse'],2,',','.')}</strong>{else}<span class="label label-danger">Deve repassar</span> <strong>R$ {number_format(abs($r['valor_repasse']),2,',','.')}</strong>{/if}</td></tr>{/foreach}</tbody>
        </table></div>
        <p class="text-muted">Cálculo: receitas na conta “Vinícius Gomes” são atribuídas a Vinícius; as demais são atribuídas a Marcos. O acerto considera o valor já recebido, a parte do lucro e os gastos pagos por cada um.</p>
        <div class="clearfix"><a href="{$_url}apuracao/index/" class="btn btn-default">Voltar à lista</a>{if $a['status'] neq 'Fechado'}<button type="button" id="abrir-confirmacao-apuracao" class="btn btn-success pull-right"><i class="fa fa-check"></i> Finalizar apuração de lucros</button>{/if}</div>
    </div>
</div></div></div>
{if $a['status'] neq 'Fechado'}
<style>
    #confirmar-finalizacao-apuracao {
        display: none;
        position: fixed;
        z-index: 2147483646;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background: rgba(0, 0, 0, .65);
        align-items: flex-start;
        justify-content: center;
        padding-top: 70px;
        pointer-events: auto;
    }
    #confirmar-finalizacao-apuracao.aberto { display: flex !important; }
    .apuracao-confirmacao-caixa {
        position: relative;
        z-index: 2147483647;
        width: 600px;
        max-width: calc(100% - 30px);
        background: #fff;
        border-radius: 4px;
        box-shadow: 0 8px 35px rgba(0, 0, 0, .55);
        pointer-events: auto;
    }
    .apuracao-confirmacao-cabecalho,
    .apuracao-confirmacao-corpo,
    .apuracao-confirmacao-rodape { padding: 15px 20px; }
    .apuracao-confirmacao-cabecalho { border-bottom: 1px solid #e5e5e5; }
    .apuracao-confirmacao-cabecalho h4 { margin: 0; }
    .apuracao-confirmacao-corpo { padding-top: 20px; padding-bottom: 20px; }
    .apuracao-confirmacao-rodape { border-top: 1px solid #e5e5e5; text-align: right; }
    .apuracao-confirmacao-fechar { float: right; border: 0; background: transparent; font-size: 22px; line-height: 18px; }
</style>
<div id="confirmar-finalizacao-apuracao" aria-hidden="true">
    <div class="apuracao-confirmacao-caixa" role="dialog" aria-modal="true" aria-labelledby="titulo-confirmacao-apuracao">
        <div class="apuracao-confirmacao-cabecalho"><button type="button" class="apuracao-confirmacao-fechar" aria-label="Fechar">&times;</button><h4 id="titulo-confirmacao-apuracao">Confirmar finalização</h4></div>
        <div class="apuracao-confirmacao-corpo"><div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Após confirmar, não será possível corrigir esta apuração.</div><p>Tem certeza de que deseja finalizar e enviar os valores por WhatsApp?</p></div>
        <div class="apuracao-confirmacao-rodape"><button type="button" class="btn btn-default apuracao-confirmacao-cancelar">Cancelar</button> <form method="post" action="{$_url}apuracao/finalizar/" style="display:inline-block;margin:0;"><input type="hidden" name="csrf" value="{$csrf}"><input type="hidden" name="apuracao_id" value="{$a['id']}"><button type="submit" class="btn btn-danger">Sim, finalizar apuração</button></form></div>
    </div>
</div>
<script type="text/javascript">
    (function () {
        var abrir = document.getElementById('abrir-confirmacao-apuracao');
        var confirmacao = document.getElementById('confirmar-finalizacao-apuracao');
        var fechar = confirmacao.querySelector('.apuracao-confirmacao-fechar');
        var cancelar = confirmacao.querySelector('.apuracao-confirmacao-cancelar');

        function abrirConfirmacao() {
            confirmacao.className = 'aberto';
            confirmacao.setAttribute('aria-hidden', 'false');
        }
        function fecharConfirmacao() {
            confirmacao.className = '';
            confirmacao.setAttribute('aria-hidden', 'true');
        }

        abrir.onclick = abrirConfirmacao;
        fechar.onclick = fecharConfirmacao;
        cancelar.onclick = fecharConfirmacao;
        confirmacao.onclick = function (evento) {
            if (evento.target === confirmacao) fecharConfirmacao();
        };
        document.onkeydown = function (evento) {
            evento = evento || window.event;
            if (evento.keyCode === 27) fecharConfirmacao();
        };
    })();
</script>
{/if}
{include file="sections/footer.tpl"}
