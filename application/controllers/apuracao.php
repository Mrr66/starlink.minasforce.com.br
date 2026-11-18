<?php

require_once 'application/plugins/whatsapp/whatsApp.php';

_auth();

$ui->assign('_title', 'Apuração - ' . $config['CompanyName']);
$ui->assign('_st', 'Apuração');
$ui->assign('_application_menu', 'invoices');
$user = User::_info();
$ui->assign('user', $user);

function apuracao_schema()
{
    ORM::execute("CREATE TABLE IF NOT EXISTS `sys_apuracoes` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `competencia` date NOT NULL,
        `status` enum('Aberto','Fechado') NOT NULL DEFAULT 'Aberto',
        `total_dinheiro` decimal(18,2) NOT NULL DEFAULT '0.00',
        `total_pix` decimal(18,2) NOT NULL DEFAULT '0.00',
        `total_boleto` decimal(18,2) NOT NULL DEFAULT '0.00',
        `total_outras_receitas` decimal(18,2) NOT NULL DEFAULT '0.00',
        `total_receita` decimal(18,2) NOT NULL DEFAULT '0.00',
        `total_despesa` decimal(18,2) NOT NULL DEFAULT '0.00',
        `lucro_liquido` decimal(18,2) NOT NULL DEFAULT '0.00',
        `created_by` int(11) NOT NULL DEFAULT '0',
        `finalized_by` int(11) NOT NULL DEFAULT '0',
        `created_at` datetime DEFAULT NULL,
        `finalized_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `competencia` (`competencia`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    ORM::execute("CREATE TABLE IF NOT EXISTS `sys_apuracao_receitas` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `apuracao_id` int(11) NOT NULL,
        `transaction_id` int(11) NOT NULL,
        `approved` tinyint(1) NOT NULL DEFAULT '0',
        `approved_by` int(11) NOT NULL DEFAULT '0',
        `approved_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `apuracao_transacao` (`apuracao_id`,`transaction_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    ORM::execute("CREATE TABLE IF NOT EXISTS `sys_apuracao_despesas` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `apuracao_id` int(11) NOT NULL,
        `transaction_id` int(11) NOT NULL,
        `payer_user_id` int(11) NOT NULL DEFAULT '-1',
        PRIMARY KEY (`id`),
        UNIQUE KEY `apuracao_transacao` (`apuracao_id`,`transaction_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    ORM::execute("CREATE TABLE IF NOT EXISTS `sys_apuracao_repasses` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `apuracao_id` int(11) NOT NULL,
        `user_id` int(11) NOT NULL,
        `user_name` varchar(200) NOT NULL,
        `receitas_recebidas` decimal(18,2) NOT NULL DEFAULT '0.00',
        `despesas_pagas` decimal(18,2) NOT NULL DEFAULT '0.00',
        `lucro` decimal(18,2) NOT NULL DEFAULT '0.00',
        `valor_repasse` decimal(18,2) NOT NULL DEFAULT '0.00',
        PRIMARY KEY (`id`),
        UNIQUE KEY `apuracao_usuario` (`apuracao_id`,`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    // Atualiza instalações que criaram a tabela antes da inclusão do controle de recebimentos.
    try {
        ORM::execute("ALTER TABLE `sys_apuracao_repasses` ADD `receitas_recebidas` decimal(18,2) NOT NULL DEFAULT '0.00' AFTER `user_name`");
    } catch (Exception $e) {
        // A coluna já existe.
    }
}

function apuracao_csrf()
{
    if (empty($_SESSION['apuracao_csrf'])) {
        if (function_exists('random_bytes')) {
            $_SESSION['apuracao_csrf'] = bin2hex(random_bytes(24));
        } else {
            $_SESSION['apuracao_csrf'] = sha1(uniqid(mt_rand(), true));
        }
    }
    return $_SESSION['apuracao_csrf'];
}

function apuracao_check_csrf()
{
    $sent = _post('csrf');
    if ($sent === '' || !isset($_SESSION['apuracao_csrf']) || !hash_equals($_SESSION['apuracao_csrf'], $sent)) {
        ib_die('Sessão expirada. Atualize a página e tente novamente.');
    }
}

function apuracao_periodo($competencia)
{
    $inicio = date('Y-m-01', strtotime($competencia));
    $fim = date('Y-m-t', strtotime($inicio));
    return array($inicio, $fim);
}

function apuracao_usuarios()
{
    $todos = ORM::for_table('sys_users')->where('status', 'Active')->order_by_asc('fullname')->find_many();
    $usuarios = array();
    foreach ($todos as $u) {
        $email = strtolower(trim($u['email']));
        $username = strtolower(trim($u['username']));
        $nome = strtolower(trim($u['fullname']));
        if ($email === 'rafaeljacuri@gmail.com' || $email === 'viiniciusgomes32@gmail.com' ||
            $username === 'rafaeljacuri@gmail.com' || $username === 'viiniciusgomes32@gmail.com' ||
            $nome === 'marcos rafael rodrigues' || $nome === 'vinicius gomes') {
            $usuarios[] = $u;
        }
    }
    return $usuarios;
}

function apuracao_metodo($metodo)
{
    $m = strtolower(trim($metodo));
    if (strpos($m, 'pix') !== false) return 'pix';
    if (strpos($m, 'boleto') !== false) return 'boleto';
    if (strpos($m, 'dinheiro') !== false || strpos($m, 'cash') !== false) return 'dinheiro';
    return 'outras';
}

function apuracao_recebedor_conta($conta)
{
    $conta = strtolower(trim($conta));
    // Toda conta que contenha o nome Vinícius pertence ao caixa de Vinícius.
    // As demais contas pertencem ao caixa de Marcos.
    if (strpos($conta, 'vinicius') !== false || strpos($conta, 'vinícius') !== false) {
        return 'Vinícius';
    }
    return 'Marcos';
}

function apuracao_identidade_usuario($usuario)
{
    $identidade = strtolower(trim($usuario['fullname'] . ' ' . $usuario['username'] . ' ' . $usuario['email']));
    if (strpos($identidade, 'rafaeljacuri@gmail.com') !== false || strpos($identidade, 'marcos rafael') !== false) {
        return 'Marcos';
    }
    if (strpos($identidade, 'viiniciusgomes32@gmail.com') !== false || strpos($identidade, 'vinicius gomes') !== false || strpos($identidade, 'vinícius gomes') !== false) {
        return 'Vinícius';
    }
    return '';
}

function apuracao_sincronizar($apuracao)
{
    if ($apuracao['status'] === 'Fechado') return;
    list($inicio, $fim) = apuracao_periodo($apuracao['competencia']);
    $receitas = ORM::for_table('sys_transactions')->where('type', 'Income')->where_gte('date', $inicio)->where_lte('date', $fim)->where_not_equal('status', 'Void')->find_many();
    $despesas = ORM::for_table('sys_transactions')->where('type', 'Expense')->where_gte('date', $inicio)->where_lte('date', $fim)->where_not_equal('status', 'Void')->find_many();
    $totais = array('dinheiro' => 0, 'pix' => 0, 'boleto' => 0, 'outras' => 0, 'receita' => 0, 'despesa' => 0);

    foreach ($receitas as $t) {
        $item = ORM::for_table('sys_apuracao_receitas')->where('apuracao_id', $apuracao['id'])->where('transaction_id', $t['id'])->find_one();
        if (!$item) {
            $item = ORM::for_table('sys_apuracao_receitas')->create();
            $item->apuracao_id = $apuracao['id'];
            $item->transaction_id = $t['id'];
            $item->save();
        }
        $valor = (float) $t['amount'];
        $totais[apuracao_metodo($t['method'])] += $valor;
        $totais['receita'] += $valor;
    }
    foreach ($despesas as $t) {
        $item = ORM::for_table('sys_apuracao_despesas')->where('apuracao_id', $apuracao['id'])->where('transaction_id', $t['id'])->find_one();
        if (!$item) {
            $item = ORM::for_table('sys_apuracao_despesas')->create();
            $item->apuracao_id = $apuracao['id'];
            $item->transaction_id = $t['id'];
            $item->payer_user_id = -1;
            $item->save();
        }
        $totais['despesa'] += (float) $t['amount'];
    }
    $apuracao->total_dinheiro = $totais['dinheiro'];
    $apuracao->total_pix = $totais['pix'];
    $apuracao->total_boleto = $totais['boleto'];
    $apuracao->total_outras_receitas = $totais['outras'];
    $apuracao->total_receita = $totais['receita'];
    $apuracao->total_despesa = $totais['despesa'];
    $apuracao->lucro_liquido = $totais['receita'] - $totais['despesa'];
    $apuracao->save();
}

function apuracao_anterior($user_id)
{
    $competencia = date('Y-m-01', strtotime('first day of last month'));
    $a = ORM::for_table('sys_apuracoes')->where('competencia', $competencia)->find_one();
    if (!$a) {
        $a = ORM::for_table('sys_apuracoes')->create();
        $a->competencia = $competencia;
        $a->status = 'Aberto';
        $a->created_by = $user_id;
        $a->created_at = date('Y-m-d H:i:s');
        $a->save();
    }
    apuracao_sincronizar($a);
    return $a;
}

function apuracao_carregar($id)
{
    $a = ORM::for_table('sys_apuracoes')->find_one((int) $id);
    if (!$a) ib_die('Apuração não encontrada.');
    return $a;
}

function apuracao_receitas($apuracao_id)
{
    return ORM::for_table('sys_apuracao_receitas')->table_alias('ar')
        ->select_many('ar.id', 'ar.approved', 'ar.approved_at')
        ->select('t.id', 'transaction_id')->select('t.date')->select('t.description')->select('t.account')->select('t.method')->select('t.amount')->select('t.ref')
        ->join('sys_transactions', array('ar.transaction_id', '=', 't.id'), 't')
        ->where('ar.apuracao_id', $apuracao_id)->order_by_asc('t.date')->find_array();
}

function apuracao_despesas($apuracao_id)
{
    return ORM::for_table('sys_apuracao_despesas')->table_alias('ad')
        ->select_many('ad.id', 'ad.payer_user_id')
        ->select('t.id', 'transaction_id')->select('t.date')->select('t.description')->select('t.account')->select('t.category')->select('t.method')->select('t.amount')
        ->join('sys_transactions', array('ad.transaction_id', '=', 't.id'), 't')
        ->where('ad.apuracao_id', $apuracao_id)->order_by_asc('t.date')->find_array();
}

function apuracao_faturas_pendentes($apuracao)
{
    list($inicio, $fim) = apuracao_periodo($apuracao['competencia']);
    $faturas = ORM::for_table('sys_invoices')
        ->where_gte('duedate', $inicio)
        ->where_lte('duedate', $fim)
        ->where_not_equal('status', 'Paid')
        ->where_not_equal('status', 'Cancelled')
        ->order_by_asc('duedate')
        ->find_array();
    return $faturas;
}

function apuracao_calculo($apuracao, $usuarios)
{
    $despesas = apuracao_despesas($apuracao['id']);
    $receitas = apuracao_receitas($apuracao['id']);
    $qtd = count($usuarios);
    $lucro_base = (float) $apuracao['total_receita'] - (float) $apuracao['total_despesa'];
    $parte = $qtd > 0 ? $lucro_base / $qtd : 0;
    $id_marcos = 0;
    $id_vinicius = 0;
    foreach ($usuarios as $u) {
        $identidade = strtolower($u['fullname'] . ' ' . $u['username'] . ' ' . $u['email']);
        if (strpos($identidade, 'marcos') !== false || strpos($identidade, 'rafaeljacuri') !== false) $id_marcos = (int) $u['id'];
        if (strpos($identidade, 'vinicius') !== false || strpos($identidade, 'viinicius') !== false) $id_vinicius = (int) $u['id'];
    }
    $recebidas = array($id_marcos => 0, $id_vinicius => 0);
    foreach ($receitas as $r) {
        $eh_vinicius = apuracao_recebedor_conta($r['account']) === 'Vinícius';
        $recebedor = $eh_vinicius ? $id_vinicius : $id_marcos;
        if ($recebedor > 0) $recebidas[$recebedor] += (float) $r['amount'];
    }
    $despesas_compartilhadas = 0;
    foreach ($despesas as $d) {
        if ((int) $d['payer_user_id'] === 0) $despesas_compartilhadas += (float) $d['amount'];
    }
    $repasses = array();
    foreach ($usuarios as $u) {
        $pago = 0;
        foreach ($despesas as $d) {
            if ((int) $d['payer_user_id'] === (int) $u['id']) $pago += (float) $d['amount'];
        }
        $receita_recebida = isset($recebidas[(int) $u['id']]) ? $recebidas[(int) $u['id']] : 0;
        $ajuste_caixa = ((int) $u['id'] === $id_marcos) ? $despesas_compartilhadas : 0;
        $valor_repasse = $parte + $pago + $ajuste_caixa - $receita_recebida;
        $repasses[] = array('id' => $u['id'], 'fullname' => $u['fullname'], 'phonenumber' => $u['phonenumber'], 'receitas_recebidas' => $receita_recebida, 'despesas_pagas' => $pago, 'despesas_compartilhadas' => $ajuste_caixa, 'lucro' => $parte, 'valor_repasse' => $valor_repasse);
    }
    return array('lucro_liquido' => $lucro_base, 'repasses' => $repasses, 'despesas' => $despesas, 'despesas_compartilhadas' => $despesas_compartilhadas);
}

apuracao_schema();
$csrf = apuracao_csrf();
$ui->assign('csrf', $csrf);
$action = isset($routes[1]) && $routes[1] !== '' ? $routes[1] : 'index';

switch ($action) {
    case 'index':
        apuracao_anterior($user['id']);
        $apuracoes = ORM::for_table('sys_apuracoes')->order_by_desc('competencia')->find_array();
        $ui->assign('apuracoes', $apuracoes);
        $ui->display('apuracao-lista.tpl');
        break;

    case 'fechamento':
        $a = apuracao_carregar(route(2));
        apuracao_sincronizar($a);
        if ($a['status'] === 'Fechado') r2(U . 'apuracao/relatorio/' . $a['id'] . '/');
        $receitas = apuracao_receitas($a['id']);
        $usuario_logado_apuracao = apuracao_identidade_usuario($user);
        foreach ($receitas as $chave => $receita) {
            $receitas[$chave]['recebido_por'] = apuracao_recebedor_conta($receita['account']);
            $receitas[$chave]['pode_aprovar'] = $receitas[$chave]['recebido_por'] === $usuario_logado_apuracao;
        }
        $faturas_pendentes = apuracao_faturas_pendentes($a);
        $pendentes = 0;
        foreach ($receitas as $r) if (!$r['approved']) $pendentes++;
        $ui->assign('a', $a);
        $ui->assign('receitas', $receitas);
        $ui->assign('usuario_logado_apuracao', $usuario_logado_apuracao);
        $ui->assign('pendentes', $pendentes);
        $ui->assign('faturas_pendentes', $faturas_pendentes);
        $ui->assign('total_faturas_pendentes', count($faturas_pendentes));
        $ui->display('apuracao-receitas.tpl');
        break;

    case 'aprovar-receita':
        apuracao_check_csrf();
        $a = apuracao_carregar(_post('apuracao_id'));
        if ($a['status'] === 'Fechado') ib_die('Esta apuração já foi fechada.');
        if (count(apuracao_faturas_pendentes($a)) > 0) {
            r2(U . 'apuracao/fechamento/' . $a['id'] . '/', 'e', 'Não é possível aprovar arrecadações enquanto houver faturas em aberto.');
        }
        $item = ORM::for_table('sys_apuracao_receitas')->where('apuracao_id', $a['id'])->where('id', (int) _post('item_id'))->find_one();
        if (!$item) ib_die('Arrecadação não encontrada.');
        $transacao = ORM::for_table('sys_transactions')->find_one($item['transaction_id']);
        if (!$transacao) ib_die('Transação da arrecadação não encontrada.');
        $recebedor = apuracao_recebedor_conta($transacao['account']);
        $usuario_logado_apuracao = apuracao_identidade_usuario($user);
        if ($usuario_logado_apuracao === '' || $usuario_logado_apuracao !== $recebedor) {
            r2(U . 'apuracao/fechamento/' . $a['id'] . '/', 'e', 'Esta arrecadação só pode ser aprovada por ' . $recebedor . '.');
        }
        $item->approved = 1;
        $item->approved_by = $user['id'];
        $item->approved_at = date('Y-m-d H:i:s');
        $item->save();
        r2(U . 'apuracao/fechamento/' . $a['id'] . '/');
        break;

    case 'reprocessar':
        apuracao_check_csrf();
        $a = apuracao_carregar(_post('apuracao_id'));
        if ($a['status'] === 'Fechado') ib_die('Uma apuração fechada não pode ser reprocessada.');

        ORM::for_table('sys_apuracao_receitas')->where('apuracao_id', $a['id'])->delete_many();
        ORM::for_table('sys_apuracao_despesas')->where('apuracao_id', $a['id'])->delete_many();
        ORM::for_table('sys_apuracao_repasses')->where('apuracao_id', $a['id'])->delete_many();
        apuracao_sincronizar($a);

        _log('Apuração reprocessada: ' . date('m/Y', strtotime($a['competencia'])) . ' [ID: ' . $a['id'] . ']', 'Admin', $user['id']);
        r2(U . 'apuracao/fechamento/' . $a['id'] . '/', 's', 'Receitas e despesas importadas novamente. As aprovações anteriores foram removidas.');
        break;

    case 'despesas':
        $a = apuracao_carregar(route(2));
        if ($a['status'] === 'Fechado') r2(U . 'apuracao/relatorio/' . $a['id'] . '/');
        if (count(apuracao_faturas_pendentes($a)) > 0) {
            r2(U . 'apuracao/fechamento/' . $a['id'] . '/', 'e', 'A apuração só pode continuar quando todas as faturas da competência estiverem pagas.');
        }
        $pendentes = ORM::for_table('sys_apuracao_receitas')->where('apuracao_id', $a['id'])->where('approved', 0)->count();
        if ($pendentes > 0) r2(U . 'apuracao/fechamento/' . $a['id'] . '/', 'e', 'Aprove todas as arrecadações antes de continuar.');
        $usuarios = apuracao_usuarios();
        $ui->assign('a', $a);
        $ui->assign('despesas', apuracao_despesas($a['id']));
        $ui->assign('usuarios_apuracao', $usuarios);
        $ui->display('apuracao-despesas.tpl');
        break;

    case 'salvar-despesas':
        apuracao_check_csrf();
        $a = apuracao_carregar(_post('apuracao_id'));
        if ($a['status'] === 'Fechado') ib_die('Esta apuração já foi fechada.');
        if (count(apuracao_faturas_pendentes($a)) > 0) {
            r2(U . 'apuracao/fechamento/' . $a['id'] . '/', 'e', 'A apuração foi bloqueada porque existem faturas em aberto.');
        }
        $usuarios = apuracao_usuarios();
        $validos = array(0);
        foreach ($usuarios as $u) $validos[] = (int) $u['id'];
        $despesas = apuracao_despesas($a['id']);
        foreach ($despesas as $d) {
            $campo = 'payer_' . $d['id'];
            if (!isset($_POST[$campo]) || !in_array((int) $_POST[$campo], $validos)) {
                r2(U . 'apuracao/despesas/' . $a['id'] . '/', 'e', 'Informe quem pagou cada gasto.');
            }
            $item = ORM::for_table('sys_apuracao_despesas')->find_one($d['id']);
            $item->payer_user_id = (int) $_POST[$campo];
            $item->save();
        }
        r2(U . 'apuracao/relatorio/' . $a['id'] . '/');
        break;

    case 'relatorio':
        $a = apuracao_carregar(route(2));
        if ($a['status'] !== 'Fechado' && count(apuracao_faturas_pendentes($a)) > 0) {
            r2(U . 'apuracao/fechamento/' . $a['id'] . '/', 'e', 'Quite todas as faturas da competência antes de gerar o relatório.');
        }
        $usuarios = apuracao_usuarios();
        if ($a['status'] === 'Fechado') {
            $repasses = ORM::for_table('sys_apuracao_repasses')->where('apuracao_id', $a['id'])->find_array();
            $calculo = array('lucro_liquido' => $a['lucro_liquido'], 'repasses' => $repasses, 'despesas' => apuracao_despesas($a['id']));
        } else {
            $calculo = apuracao_calculo($a, $usuarios);
        }
        $ui->assign('a', $a);
        $ui->assign('calculo', $calculo);
        $ui->assign('usuarios_apuracao', $usuarios);
        $ui->display('apuracao-relatorio.tpl');
        break;

    case 'finalizar':
        apuracao_check_csrf();
        $a = apuracao_carregar(_post('apuracao_id'));
        if ($a['status'] === 'Fechado') r2(U . 'apuracao/relatorio/' . $a['id'] . '/');
        if (count(apuracao_faturas_pendentes($a)) > 0) ib_die('Existem faturas em aberto nesta competência.');
        $pendentes = ORM::for_table('sys_apuracao_receitas')->where('apuracao_id', $a['id'])->where('approved', 0)->count();
        if ($pendentes > 0) ib_die('Existem arrecadações pendentes de aprovação.');
        $usuarios = apuracao_usuarios();
        if (count($usuarios) !== 2) ib_die('Marcos e Vinícius precisam estar cadastrados e ativos para finalizar.');
        $calculo = apuracao_calculo($a, $usuarios);
        foreach ($calculo['repasses'] as $repasse) {
            $r = ORM::for_table('sys_apuracao_repasses')->create();
            $r->apuracao_id = $a['id'];
            $r->user_id = $repasse['id'];
            $r->user_name = $repasse['fullname'];
            $r->receitas_recebidas = $repasse['receitas_recebidas'];
            $r->despesas_pagas = $repasse['despesas_pagas'];
            $r->lucro = $repasse['lucro'];
            $r->valor_repasse = $repasse['valor_repasse'];
            $r->save();
        }
        $a->status = 'Fechado';
        $a->lucro_liquido = $calculo['lucro_liquido'];
        $a->finalized_by = $user['id'];
        $a->finalized_at = date('Y-m-d H:i:s');
        $a->save();

        $competencia = date('m/Y', strtotime($a['competencia']));
        $mensagem = "Fechamento de {$competencia}\n\nReceita: R$ " . number_format($a['total_receita'], 2, ',', '.') . "\nDespesas: R$ " . number_format($a['total_despesa'], 2, ',', '.') . "\nLucro líquido: R$ " . number_format($a['lucro_liquido'], 2, ',', '.');
        foreach ($calculo['repasses'] as $repasse) {
            $situacao = $repasse['valor_repasse'] >= 0 ? 'deve receber' : 'deve repassar';
            $mensagem .= "\n\n" . $repasse['fullname'] . "\nJá recebeu: R$ " . number_format($repasse['receitas_recebidas'], 2, ',', '.') . "\n{$situacao}: R$ " . number_format(abs($repasse['valor_repasse']), 2, ',', '.');
        }
        $whats = new WhatsApp();
        foreach ($calculo['repasses'] as $repasse) {
            if (!empty($repasse['phonenumber'])) {
                try {
                    $numero = preg_replace('/\D+/', '', $repasse['phonenumber']);
                    if (substr($numero, 0, 2) !== '55') $numero = '55' . $numero;
                    $whats->Send($mensagem, $numero, "Apuração finalizada\n\n");
                } catch (Exception $e) {
                    _log('Falha ao notificar apuração ' . $a['id'] . ': ' . $e->getMessage(), 'Admin', $user['id']);
                }
            }
        }
        _log('Apuração finalizada: ' . $competencia . ' [ID: ' . $a['id'] . ']', 'Admin', $user['id']);
        r2(U . 'apuracao/relatorio/' . $a['id'] . '/', 's', 'Apuração finalizada com sucesso.');
        break;

    default:
        r2(U . 'apuracao/index/');
}
