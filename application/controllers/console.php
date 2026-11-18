<?php
require_once("application/plugins/whatsapp/whatsApp.php");
$c_ckey = $config['ckey'];
$r_ckey = $routes['1'];
if($c_ckey != $r_ckey){
    exit('Access Denied.');
}

@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', 0);
@set_time_limit(0);

define('INVOICE_LATE_FEE_ITEM', 'MULTA_ATRASO');
define('INVOICE_LATE_FEE_PERCENT', 0.02);
define('INVOICE_LATE_FEE_DAILY', 0.25);

/**
 * Retorna o item de multa já existente na fatura, quando houver.
 */
function console_late_fee_item($invoice_id)
{
    return ORM::for_table('sys_invoiceitems')
        ->where('invoiceid', $invoice_id)
        ->where('itemcode', INVOICE_LATE_FEE_ITEM)
        ->find_one();
}

/**
 * Aplica ou atualiza a multa sem acumular valores em duplicidade.
 * Fórmula: 2% sobre o valor original + R$ 0,25 por dia vencido.
 */
function console_apply_late_fee($invoice, $today)
{
    if ($invoice['status'] !== 'Unpaid' && $invoice['status'] !== 'Partially Paid') return false;
    if (empty($invoice['duedate']) || $invoice['duedate'] === '0000-00-00' || $invoice['duedate'] >= $today) return false;

    $due = new DateTime($invoice['duedate']);
    $now = new DateTime($today);
    $days_overdue = (int) $due->diff($now)->days;
    if ($days_overdue < 1) return false;

    $fee_item = console_late_fee_item($invoice['id']);
    $previous_fee = $fee_item ? (float) $fee_item['total'] : 0.0;
    $base_total = max(0, (float) $invoice['total'] - $previous_fee);
    $base_subtotal = max(0, (float) $invoice['subtotal'] - $previous_fee);
    $percentage_fee = round($base_total * INVOICE_LATE_FEE_PERCENT, 2);
    $daily_fee = round($days_overdue * INVOICE_LATE_FEE_DAILY, 2);
    $new_fee = round($percentage_fee + $daily_fee, 2);

    if (!$fee_item) {
        $fee_item = ORM::for_table('sys_invoiceitems')->create();
        $fee_item->invoiceid = $invoice['id'];
        $fee_item->userid = $invoice['userid'];
        $fee_item->type = 'Late Fee';
        $fee_item->relid = 0;
        $fee_item->itemcode = INVOICE_LATE_FEE_ITEM;
        $fee_item->qty = '1';
        $fee_item->taxed = 0;
        $fee_item->taxamount = '0.00';
        $fee_item->duedate = $invoice['duedate'];
        $fee_item->paymentmethod = '';
        $fee_item->notes = 'Multa calculada automaticamente pelo cron';
    }

    $fee_item->description = 'Multa por atraso: 2% + R$ 0,25 x ' . $days_overdue . ' dia(s)';
    $fee_item->amount = $new_fee;
    $fee_item->total = $new_fee;
    $fee_item->save();

    $invoice->subtotal = round($base_subtotal + $new_fee, 2);
    $invoice->total = round($base_total + $new_fee, 2);
    $invoice->save();

    return array(
        'days' => $days_overdue,
        'percentage' => $percentage_fee,
        'daily' => $daily_fee,
        'fee' => $new_fee,
        'total' => (float) $invoice->total
    );
}

//Pegando a data de hoje
$today = date('Y-m-d');

$d = ORM::for_table('sys_schedulelogs')->where('date',$today)->find_one();

if($d){
    echo 'application Schedule Jobs already run for today: '.$today;
}
else{
    $syslogs = '';
    $syslogs .= '================================================== <br>';
    $syslogs .= date('Y-m-d H:i:s').' : Schedule Jobs Started....... <br>';

    $msg = '';
    $arcs = array();
    $cs = ORM::for_table('sys_schedule')->find_many();
    foreach($cs as $rcs)
    {
        $arcs[$rcs['cname']]=$rcs['val'];
    }

    if(($arcs['accounting_snapshot']) == 'Active'){
        $syslogs .= date('Y-m-d H:i:s').' : Creating Accounting Snapshot <br>';
        $last_day = date('Y-m-d', strtotime('Yesterday'));
        $msg .= 'Accounting Snaphsot - Date: '.$last_day.'<br>';
        $last_day_income = ORM::for_table('sys_transactions')->where('date',$last_day)->sum('cr');
        if($last_day_income == ''){
            $last_day_income = '0.00';
        }

        $msg .= 'Total Income: '.ib_money_format($last_day_income,$config).'<br>';

        $last_day_expense = ORM::for_table('sys_transactions')->where('date',$last_day)->sum('dr');
        if($last_day_expense == ''){
            $last_day_expense = '0.00';
        }

        $msg .= 'Total Expense: '.ib_money_format($last_day_expense,$config).'<br>';

        // echo $msg;
        $syslogs .= date('Y-m-d H:i:s').' : Accounting Snapshot created! <br>';
        $syslogs .= '=============== Accounting Snaphsot ==================== <br>';
        $syslogs .= $msg;
        $syslogs .= '================================================== <br>';



    }

//  ============================ For Invoice =======================================================================
    if(($arcs['recurring_invoice']) == 'Active'){
        
        $syslogs .= date('Y-m-d H:i:s').' : Criação de fatura recorrente <br>';

        //find all invoice that nd = today
        $its = strtotime($today);

        $i = ORM::for_table('sys_invoices')->where_not_equal('r','0')->where('nd',$today)->find_many();
        $c = 0;
        foreach($i as $is){
            $user = ORM::for_table('crm_accounts')->find_one($is['userid']);
            $nome = explode(" ", $is["account"])[0];
            $iid = $is['id'];
            $r = $is['r'];
            $pdd = strtotime($is['duedate']);
            $dd = date('Y-m-d',strtotime($r,$pdd));
            $nd = date('Y-m-d',strtotime($r,$its));
            $vtoken = _raid(10);
            $ptoken = _raid(10);
            $old_late_fee_item = console_late_fee_item($is['id']);
            $old_late_fee = $old_late_fee_item ? (float) $old_late_fee_item['total'] : 0.0;
            $d = ORM::for_table('sys_invoices')->create();
            $d->userid = $is['userid'];
            $d->account = $is['account'];
            $d->date = $today;
            $d->duedate = $dd;
            $d->subtotal = max(0, (float) $is['subtotal'] - $old_late_fee);
            $d->total = max(0, (float) $is['total'] - $old_late_fee);
            $d->tax = $is['tax'];
            $d->taxname = $is['taxname'];
            $d->taxrate = $is['taxrate'];
            $d->vtoken = $vtoken;
            $d->ptoken = $ptoken;
            $d->status = 'Unpaid';
            $d->notes = $is['notes'];
            $d->r = $r;
            $d->nd = $nd;
            //others
            $d->invoicenum = $is['invoicenum'];
            $d->cn = $is['cn'];
            $d->tax2 = '0.00';
            $d->taxrate2 = '0.00';
            $d->paymentmethod = '';
            $d->save();
            $invoiceid = $d->id();
            //set previous invoice r 0
            $s = ORM::for_table('sys_invoices')->find_one($iid);
            $s->r = '0';
            $s->save();
            $whats = new WhatsApp();
            $link_pagamento = APP_URL."?ng=client/iview/$invoiceid/token_$d->vtoken";
            $msg = "Olá, *$nome*!\n\nSua fatura foi criada com vencimento em *$dd*. Use o link abaixo para visualizá-la:\n$link_pagamento\n\n*Obs:* Esta mensagem é automática. Se já pagou, favor desconsiderar.";
            $numero = "55".$user["phone"];
            $whats->send($msg, $numero,"📄 ");
            $c++;
            //create items from old data

            $items = ORM::for_table('sys_invoiceitems')->where('invoiceid',$iid)->order_by_asc('id')->find_many();
            foreach($items as $item){
                if ($item['itemcode'] === INVOICE_LATE_FEE_ITEM) continue;
                $t = ORM::for_table('sys_invoiceitems')->create();
                $t->invoiceid = $invoiceid;
                $t->userid = $item['userid'];
                $t->description = $item['description'];
                $t->qty = $item['qty'];
                $t->amount = $item['amount'];
                $t->total = $item['total'];
                $t->taxed = $item['taxed'];
                $t->relid = $item['relid'];
                $t->type = '';
                $t->itemcode = '';
                $t->paymentmethod = '';
                $t->notes = 'Combrança recorrente automática';
                $t->save();
            }

            if($config['console_notify_invoice_created'] == '1'){
                $msg = Invoice::gen_email($invoiceid,'created');
                $subject = $msg['subject'];
                $message = $msg['body'];
                $email = $msg['email'];
                $name = $msg['name'];
                $cid = $msg['cid'];

                Notify_Email::_send($name, $email, $subject, $message, $cid, $invoiceid);

            }

        }
        // echo $msg;
        $syslogs .= date('Y-m-d H:i:s').' : '.$c.' Invoice created! <br>';
        //Send Email Invoice Created
        $syslogs .= '================================================== <br>';
    }

    $l = ORM::for_table('sys_schedulelogs')->create();
    $l->date = $today;
    $l->logs = $syslogs;
    $l->save();

    if(($arcs['notify']) == 'Active') {
        Notify_Email::_send($config['CompanyName'],$arcs['notifyemail'],$config['CompanyName'].' Automation Activity',$syslogs);
    }

    echo $syslogs;
}

// Atualiza as multas de todas as faturas vencidas antes de enviar as cobranças.
$late_fee_invoices = ORM::for_table('sys_invoices')
    ->where_raw("(`status` = 'Unpaid' OR `status` = 'Partially Paid')")
    ->where_lt('duedate', $today)
    ->find_many();
$late_fee_updated = 0;
foreach ($late_fee_invoices as $late_fee_invoice) {
    $late_fee_result = console_apply_late_fee($late_fee_invoice, $today);
    if ($late_fee_result !== false) $late_fee_updated++;
}
echo '<br>Multas por atraso atualizadas: ' . $late_fee_updated . '<br>';

// Cobrança recorrente
$faturas = ORM::for_table('sys_invoices')->where("status", "Unpaid")->find_many();
echo "<br>";
$data_inicio = new DateTime($today);
$syslogs = '<h2 style="color: #fff; background: #222; width:100%; text-align:center"> Serviço de Cobrança </h2>';
$syslogs .= '<table border="1">';
$syslogs .= '<th>Fatura</th><th>Cliente</th><th>Notificação</th><th>Aberta Pag.. </th><th>Dias Vencidos</th><th>Vencimento</th>';

foreach($faturas as $fatura){
    $data_fim = new DateTime($fatura["duedate"]);
    // Resgata diferença entre as datas
    $dateInterval = $data_inicio->diff($data_fim);
    $dias_vencido =  $dateInterval->days;
    $whats = new WhatsApp();
    $user = ORM::for_table('crm_accounts')->find_one($fatura["userid"]);
    $nome = $user["account"];
    $token = $fatura["vtoken"];
    $invoiceid = $fatura["id"];
    $invoiceName = "Internet banda larga";
    $link_pagamento = APP_URL."?ng=client/iview/$invoiceid/token_$token";
    $time_atual = strtotime($today);
    $time_expira = strtotime($fatura["duedate"]);
    $dif_tempo = $time_expira - $time_atual;
    $dias = (int)floor( $dif_tempo / (60 * 60 * 24));
    $syslogs .= "<tr>";
    $date = new DateTime($fatura["duedate"]);
    $date_paid = new DateTime($fatura["datepaid"]);
    $vencido =  $date->format( 'd-m-Y' );
    $aberto =  $date_paid->format( 'd-m-Y' );
    switch ($dias){
        case 0:
            $msg = "*Internet Minas Sat*\n\nOlá, *$nome*!\n\nSua fatura *Nº $invoiceName* vence *hoje*. Não deixe para a última hora!\n\nUse o link abaixo para visualizar e pagar (PIX ou Boleto):\n$link_pagamento\n\n---\nMelhoramos nosso sistema para facilitar seu pagamento! \n\n*Obs:* Esta mensagem é automática, favor desconsiderar se já efetuou o pagamento.";
            $numero = "55".$user["phone"];
            $whats->send($msg, $numero, "🟡 ");
            $syslogs .= "<td>$invoiceid</td><td>$nome</td><td>$msg</td><td>$aberto</td><td style='color: green; text-align:center'>$dias</td><td>$vencido</td>";
            break;
        case 1:
            $msg = "*Internet Minas Sat*\n\nFatura *Nº $invoiceid* vence amanhã não deixe para última hora, use o link de pagamento para visualizar e pagar. \n $link_pagamento\n\n*Obs:* Esta mensagem é automática, favor desconsiderar se já efetuou o pagamento.";
            $numero = "55".$user["phone"];
            $whats->send($msg, $numero,"🚨📄 ");
            $syslogs .= "<td>$invoiceid</td><td>$nome</td><td>$msg</td><td>$aberto</td><td style='color: green; text-align:center'>$dias</td><td>$data_fvencidoim</td>";
            break;
        case 2:
            $msg = "*Internet Minas Sat*\n\nSua fatura *Nº $invoiceid* está proxímo do vencimento, use o link de pagamento para visualizar e pagar. \n $link_pagamento\n\n*Obs:* Esta mensagem é automática, favor desconsiderar se já efetuou o pagamento.";
            $numero = "55".$user["phone"];
            $whats->send($msg, $numero,"🚨📄 ");
            $syslogs .= "<td>$invoiceid</td><td>$nome</td><td>$msg</td><td>$aberto</td><td style='color: green; text-align:center'>$dias</td><td>$vencido</td>";
            break;
        case 3:
            $msg = "*Internet Minas Sat*\n\nSua fatura *Nº $invoiceid* está proxímo do vencimento, use o link de pagamento para visualizar e pagar. \n $link_pagamento\n\n*Obs:* Esta mensagem é automática, favor desconsiderar se já efetuou o pagamento.";
            $numero = "55".$user["phone"];
            $whats->send($msg, $numero,"🚨📄 ");
            $syslogs .= "<td>$invoiceid</td><td>$nome</td><td>$msg</td><td>$aberto</td><td style='color: green; text-align:center'>$dias</td><td>$vencido</td>";
            break;
        default:
            //continue;
            if($dias < 0){
                $msg = "Fatura vencida há $dias_vencido dias, juros poderá ser aplicado, use o link de pagamento para visualizar e efetuar o pagamento da fatura, disponivél no link abaixo. \n $link_pagamento\n\n*Obs:* Esta mensagem é automática, favor desconsiderar se já efetuou o pagamento.";
                $numero = "55".$user["phone"];
                $whats->send($msg, $numero,"🔴📄 ");
                $syslogs .= "<td>$invoiceid</td><td>$nome</td><td>$msg</td><td>$aberto</td><td style='color: red; text-align:center'>$dias</td><td>$vencido</td>";
            }
            break;
    }
    $syslogs .= "</td>";
}
$syslogs .= "</table>";
$l = ORM::for_table('sys_schedulelogs')->create();
$l->date = $today;
$l->logs = $syslogs;
$l->save();
$arcs['notifyemail'] = "contato@minasforce.com.br";
Notify_Email::_send($config['CompanyName'],$arcs['notifyemail'],$config['CompanyName'].' Cobrança Automatizada',$syslogs);
echo $syslogs;
