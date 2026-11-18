<?php

$ui->assign('_title', 'PagSeguro'.' - '. $config['CompanyName']);
$ui->assign('_st', 'Pagseguro');
// Definição do Ambiente
$action = $routes[2];

$processor  = ORM::for_table('sys_pg')->where('processor','pagseguro')->find_one();
switch ($action) {
    case 'make':
        $js     = 'application/plugins/pagseguro/views/js/';
        $ui->assign('_include','make');
        $ui->assign('xfooter','
            <script type="text/javascript" src="'.$js.'mercadopago.js?'. rand(0, 99999999999999) .'"></script>
        ');
        $ui->display('application/plugins/pagseguro/views/make.tpl');
    break;
    case 'pix':
        $id         = $routes[3];
        $invoice    = ORM::for_table('sys_invoices')->find_one($id);
        $user       = ORM::for_table('crm_accounts')->find_one($invoice['userid']);
        $timestamp = strtotime($invoice->date_of_expiration);
        $dateOld = date('Y-m-d H:i', $timestamp);
        $dataAnterior = new DateTime($dateOld);
        $agora = new DateTime(date('Y-m-d H:i'));
        if(!is_null($invoice->date_of_expiration) && $dataAnterior > $agora){
            //Retorna os dados do pagamento salvo no banco de dados
           
            $json = get_payload_mercadopago('/v1/payments/'.$invoice->payment_id, $processor);
            $d = json_decode($json);
        }
        else{
            //die("Vai gerar novamente");
            //Gerar um novo pagamento
            $invoiceItem    = ORM::for_table('sys_invoiceitems')->where("invoiceid", $id)->find_one();
            $dataPayment = array(
                "transaction_amount" => floatval($invoice->total),
                "date_of_expiration" => date('Y-m-d\TH:i:s.vP', strtotime(date('Y-m-d H:i') . ' +2 hours')), //O Usuario tem uma hora para pagar o PIX.
                "payment_method_id" => "pix",
                "external_reference" => $invoice->id,
                "notification_url" => $processor->c4,
                "metadata" => array(
                    "order_number" => "order_".$invoice->id
                ),
                "description" => $invoiceItem->description,
                "payer" => array(
                    "first_name" => $user->fname,
                    "last_name" => $user->lname,
                    "email" => $user->email,
                    "identification" => array(
                        "type" => "CPF",
                        "number" => $user->document
                    ),
                    "address" => array(
                        "zip_code" => $user->zip,
                        "street_name" => $user->address,
                        "street_number" => 0,
                        "neighborhood" => "Centro",
                        "city" => $user->city,
                        "federal_unit" => $user->state
                    )
                ),
                "additional_info" => array(
                    "items" => array(array(
                        "id"  => $invoice->id,
                        "title" => $invoiceItem->description,
                        "description" => $invoiceItem->description,
                        "picture_url" => null,
                        "category_id" => "Pagamento-super",
                        "quantity" => $invoiceItem->qty,
                        "unit_price" => floatval($invoice->total),
                        "event_date" =>  date('Y-m-d\TH:i:s.vP', strtotime(date('Y-m-d H:i')))
                    ))
                )
            );
            $json = post_payload_mercadopago('/v1/payments', $dataPayment, $processor);
            $d = json_decode($json);
            $invoice->date_of_expiration = $d->date_of_expiration;
            $invoice->payment_id = $d->id;
            $invoice->save();
        }
        
        //var_dump($d->id);
        $js     = 'application/plugins/pagseguro/views/js/';
        $ui->assign('_include','pix');
        $ui->assign('d', $d);
        $ui->assign('xfooter','
            <script type="text/javascript" src="'.$js.'pix.js?2'. rand(0, 99999999999999) .'"></script>
        ');
        $ui->display('application/plugins/pagseguro/views/pix.tpl');
    break;
    case 'boleto':

        if($processor->mode == 'Sandbox'){
            $pagseguro_token = $processor->c1;
            $url = 'https://ws.sandbox.pagseguro.uol.com.br/v2/checkout';
            $token = $processor->c1;
        } elseif($processor->mode == 'Live'){
            $pagseguro_token = $processor->value;
            $url = 'https://ws.pagseguro.uol.com.br/v2/checkout';
            $token = $processor->value;
        }

        $user       = ORM::for_table('crm_accounts')->find_one($invoice['userid']);

        if($user->email !=  $_POST['sender']['senderEmail']){
            $user   = ORM::for_table('crm_accounts')->create();
        }

        $user->account  = $_POST['sender']['senderName'];
        $user->phone    = $_POST['sender']['senderAreaCode'].$_POST['sender']['senderPhone'];
        $user->email    = $_POST['sender']['senderEmail'];
        $user->address  = $_POST['sender']['shippingAddressStreet'];
        $user->city     = $_POST['sender']['shippingAddressCity'];
        $user->state    = $_POST['sender']['shippingAddressState'];
        $user->zip      = $_POST['sender']['shippingAddressPostalCode'];
        $user->save();

        $email = $processor->c2;

        $POSTFIELDS = http_build_query($_POST['sender']);
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $url."?email=".$email."&token=".$token."&".$POSTFIELDS,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => '',
        CURLOPT_HTTPHEADER => array(
            "Accept: */*",
            "Accept-Encoding: gzip, deflate",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "Content-Type: application/x-www-form-urlencoded; charset=ISO-8859-1",
            "cache-control: no-cache"
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $xml = simplexml_load_string($response);

        curl_close($curl);

        // Atualizando dados do pagador.



        if ($err) {
            echo 'erro: '. $err;
        } else {
            echo json_encode($xml);
        }
    break;
    case 'payment_return':
        $id         = $routes[3];
        $transCode  = $routes[4];
        $invoice    = ORM::for_table('sys_invoices')->find_one($id);
        $user       = ORM::for_table('crm_accounts')->find_one($invoice['userid']);

        if(!$invoice){
            exit('Invoice Not Found');
        }

        $token      = $routes[4];

        if(!$processor){
            echo 'Desculpe, o Pagseguro não está configurado corretamente';
            exit;
        }

        $transactions       = ORM::for_table('sys_transactions')->create();
        $account            = ORM::for_table('sys_accounts')->where('account', $processor->c3 )->find_one();


        if($processor->mode == 'Sandbox'){
            $pagseguro_token = $processor->c1;
            $url = 'https://ws.sandbox.pagseguro.uol.com.br/v3/transactions/';
            $token = $processor->c1;
        } elseif($processor->mode == 'Live'){
            $pagseguro_token = $processor->value;
            $url = 'https://ws.pagseguro.uol.com.br/v3/transactions/';
            $token = $processor->value;
        }

        $email = $processor->c2;

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $url.$transCode."?email=".$email."&token=".$token,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Accept: */*",
            "Accept-Encoding: gzip, deflate",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "Cookie: TRANSACTION_COOKIE=fb772f03481430cc292be8dac99621de",
            "Postman-Token: 22e39902-66db-4c01-86a1-be28ec991591,47ae9b86-0cb0-4ba5-8718-827eee76f1e5",
            "User-Agent: PostmanRuntime/7.17.1",
            "cache-control: no-cache"
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $xml = simplexml_load_string($response);

        $transactionCode = $xml->status;

        curl_close($curl);

        /*

        STATUS DO RETORNO DO PAGAMENTO

        - 4 Disponível: A transação foi paga e chegou ao final de seu prazo de liberação sem ter sido retornada e sem que haja nenhuma disputa aberta.
        - 5 Em disputa: O comprador, dentro do prazo de liberação da transação, abriu uma disputa.
        - 6 Devolvida: O valor da transação foi devolvido para o comprador.
        - 7 Cancelada: A transação foi cancelada sem ter sido finalizada.

        */

        switch($transactionCode){
            case '1': // Aguardando pagamento: O comprador iniciou a transação, mas até o momento o PagSeguro não recebeu nenhuma informação sobre o pagamento.

                $invoice->status            = 'Aguardando';
                $invoice->paymentmethod     = $processor->processor;
                $invoice->datepaid          = date('Y-m-d H:i:s');
                $invoice->save();

                r2(U.'client/iview/'. $invoice->id .'/token_'. $invoice->vtoken .'/','w','Aguardando Pagamento ...');

            break;

            case '3': // Paga: A transação foi paga pelo comprador e o PagSeguro já recebeu uma confirmação da instituição financeira responsável pelo processamento.

            $transactions->account      = $processor->c3;
            $transactions->type         = "Income";
            $transactions->category     = "Uncategorized";
            $transactions->amount       = $invoice->subtotal;
            $transactions->payerid      = $user->id;
            $transactions->method       = $processor->processor;
            $transactions->status       = "Cleared";
            $transactions->description  = "Pagamento para a Fatura: " . $invoice->id . ". Código de Transação no PagSeguro: ". $transCode;
            $transactions->date         = date('Y-m-d H:i:s');
            $transactions->dr           = "0.00";
            $transactions->cr           = $invoice->subtotal;
            $transactions->bal          = $invoice->subtotal;
            $transactions->iid          = $invoice->id;
            $transactions->updated_at   = date('Y-m-d H:i:s');
            $transactions->updated_by   = "0";
            $transactions->attachments  = NULL;
            $transactions->source       = NULL;
            $transactions->rid          = "0";
            $transactions->pid          = "0";
            $transactions->archived     = "0";
            $transactions->trash        = "0";
            $transactions->flag         = "0";

            $transactions->save();

            $account->balance           = $account->balance + $invoice->subtotal;

            $account->save();

            $invoice->status            = 'Paid';
            $invoice->paymentmethod     = $processor->processor;
            $invoice->datepaid          = date('Y-m-d H:i:s');

            $invoice->save();
            r2(U.'client/iview/'. $invoice->id .'/token_'. $invoice->vtoken .'/','s','Invoice Payment Successful');

            break;
        }
    break;
}

function post_payload_mercadopago($endpoint, $data, $processor){
    if($processor->mode == 'Sandbox'){
        $access_token = $processor->c1;
        $url = 'https://api.mercadopago.com'.$endpoint;
    } elseif($processor->mode == 'Live'){
        $access_token = $processor->value;
        $url = 'https://api.mercadopago.com'.$endpoint;
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => json_encode($data),
      CURLOPT_HTTPHEADER => array(
        'X-Idempotency-Key: '.GUID(),
        "Authorization: Bearer ".$processor->value,
        "Content-Type: application/json",
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
      return "cURL Error #:" . $err;
    } else {
      return $response;
    }
}

function get_payload_mercadopago($endpoint, $processor){
    if($processor->mode == 'Sandbox'){
        $access_token = $processor->c1;
        $url = 'https://api.mercadopago.com'.$endpoint;
    } elseif($processor->mode == 'Live'){
        $access_token = $processor->value;
        $url = 'https://api.mercadopago.com'.$endpoint;
    }
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer ".$processor->value
      ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
      return "cURL Error #:" . $err;
    } else {
      return $response;
    }
}

function GUID()
{
    if (function_exists('com_create_guid') === true)
    {
        return trim(com_create_guid(), '{}');
    }

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}