<?php
//require __DIR__ . '../helpers/ibilling_response.php'; //DEV
require '/usr/share/nginx/pagamentosuper.minasforce.com.br/application/helpers/ibilling_response.php';
require_once("application/plugins/whatsapp/whatsApp.php");

$data = array();
$data['success'] = false;
$data['msg'] = '';
$ip = '';
$ref = '';
$agent = '';
if(isset($_SERVER['REMOTE_ADDR'])){
$ip = $_SERVER['REMOTE_ADDR'];
}
if(isset($_SERVER['HTTP_REFERER'])){
    $ref = $_SERVER['HTTP_REFERER'];
}
if(isset($_SERVER['HTTP_USER_AGENT'])){
    $agent = $_SERVER['HTTP_USER_AGENT'];
}
$data['ip'] = $ip;
$data['ref'] = $ref;
$data['agent'] = $agent;
$data['result'] = false;




function api_response($data=array()){

    header('Content-type: application/json');
    echo json_encode( $data );
exit;
}

function api_product_data($item)
{
    $image_url = null;
    if (!empty($item['image']) && basename($item['image']) === $item['image']) {
        $image_url = APP_URL . '/application/storage/products/' . rawurlencode($item['image']);
    }
    return array(
        'id' => (int) $item['id'],
        'name' => $item['name'],
        'item_number' => $item['item_number'],
        'sales_price' => (float) $item['sales_price'],
        'description' => $item['description'],
        'location' => $item['location'],
        'image' => $item['image'],
        'image_url' => $image_url,
        'stock' => (int) $item['item_number'],
        'status' => $item['status']
    );
}

function api_product_upload_image(&$error)
{
    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) return '';
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Falha ao enviar a imagem. Código: ' . (int) $_FILES['image']['error'];
        return false;
    }
    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        $error = 'A imagem deve ter no máximo 5 MB.';
        return false;
    }
    $info = @getimagesize($_FILES['image']['tmp_name']);
    $types = array(IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif');
    if (defined('IMAGETYPE_WEBP')) $types[IMAGETYPE_WEBP] = 'webp';
    if (!$info || !isset($types[$info[2]])) {
        $error = 'Formato inválido. Envie JPG, PNG, GIF ou WebP.';
        return false;
    }
    $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        $error = 'Não foi possível criar a pasta de imagens.';
        return false;
    }
    if (!is_writable($directory)) {
        $error = 'A pasta de imagens não possui permissão de escrita.';
        return false;
    }
    $filename = 'product_' . date('YmdHis') . '_' . substr(sha1(uniqid(mt_rand(), true)), 0, 12) . '.' . $types[$info[2]];
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $directory . $filename)) {
        $error = 'Não foi possível salvar a imagem.';
        return false;
    }
    return $filename;
}

if(isset($_POST['key']) AND ($_POST['key'] != '')){
    
    $key = $_POST['key'];

    $a = ORM::for_table('sys_api')->where('apikey',$key)->find_one();

    if($a){
        $data['success'] = true;

        $method = _post('method');

        switch ($method) {
            case 'consumo_internet':
                $download = _post('download');
                $upload = _post('upload');
                $time = date('H:i:s');
                $data_hoje = date('Y-m-d');
                $ci = ORM::for_table('sys_consumo_internet')->create();
                $ci->time = $time;
                $ci->download = $download;
                $ci->upload = $upload;
                $ci->data = $data_hoje;
                $ci->save();
                $data['result'] = true;
                $data['notificacoes_enviadas'] = 0;
                $data['notificacoes_sem_telefone'] = 0;
                $users = ORM::for_table('sys_users')->find_many();
                foreach($users as $user){
                    $userName = trim($user["fullname"]);
                    $userLogin = strtolower(trim($user["username"]));
                    $isVinicius = $userLogin == "viiniciusgomes32@gmail.com" || strpos($userName, "Gomes Almeida") !== false;
                    $isMarcos = $userLogin == "rafaeljacuri@gmail.com" || $userName == "Marcos Rafael Rodrigues";

                    if ($isVinicius || $isMarcos) {
                        if (empty($user["phonenumber"])) {
                            $data['notificacoes_sem_telefone']++;
                            continue;
                        }

                        if($isVinicius){
                            $msg = "📶 *Monitor de tráfego:* \\n\\nOlá $userName,  Um novo limite foi atingido de *$download* MB de download as $time, no decorrer do dia um novo consumo máximo poderá ser alcançado. \\n\\nhttps://starlink.minasforce.com.br/?ng=dashboard/";
                            sendMessage(normalizarNumeroWhatsApp($user["phonenumber"]), $msg);
                            $data['notificacoes_enviadas']++;
                        }else if($isMarcos){
                            $msg = "📶 *Monitor de tráfego:* \\n\\nOlá $userName,  Um novo limite foi atingido de *$download* MB de download as $time, no decorrer do dia um novo consumo máximo poderá ser alcançado. \\n\\nhttps://starlink.minasforce.com.br/?ng=dashboard/";
                            sendMessage(normalizarNumeroWhatsApp($user["phonenumber"]), $msg);
                            $data['notificacoes_enviadas']++;
                        }
                    }
                }
                if ($data['notificacoes_enviadas'] == 0) {
                    $data['msg'] = 'Consumo registrado, mas nenhuma notificacao foi enviada. Verifique os telefones dos administradores.';
                }
                api_response($data);
            break;

            case 'get_consumo_internet':
                $data_hoje = date('Y-m-d');
                $d = ORM::for_table('sys_consumo_internet')->where('data',$data_hoje)->order_by_desc('id')->find_array();
                if($d)
                    api_response($d);
                else{
                    $data['result'] = false;
                    $data['download'] = 0;
                    $data['time'] = 0;
                    $data['msg'] = 'Nenhum consumo encontrado para hoje';
                    api_response($data);
                }
            break;

            case 'get_devices':
                $d = ORM::for_table('sys_dispositivos')->where('situacao', 'ATIVO')->find_array();
                if($d)
                    api_response($d);
                else{
                    $data['result'] = false;
                    $data['msg'] = 'Nenhum dispositivo encontrado';
                    api_response($data);
                }
            break;

            case 'get_product':
            case 'get_products':
                $id = (int) _post('id', 0);
                $item_number = trim(_post('item_number'));
                $search = trim(_post('search'));
                $include_inactive = _post('include_inactive', '0') === '1';

                $query = ORM::for_table('sys_items')->where('type', 'Product');
                if (!$include_inactive) {
                    $query->where('status', 'Active');
                }
                if ($id > 0) {
                    $query->where('id', $id);
                } elseif ($item_number !== '') {
                    $query->where('item_number', $item_number);
                } elseif ($search !== '') {
                    $query->where_raw(
                        '(`name` LIKE ? OR `item_number` LIKE ? OR `description` LIKE ? OR `location` LIKE ?)',
                        array('%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%')
                    );
                }

                $items = $query->order_by_asc('name')->find_array();
                $products = array();
                foreach ($items as $item) {
                    $products[] = api_product_data($item);
                }

                $data['result'] = $products;
                $data['count'] = count($products);
                $data['msg'] = count($products) > 0 ? 'Produtos encontrados' : 'Nenhum produto encontrado';
                api_response($data);
            break;

            case 'update_product':
                $id = (int) _post('id', 0);
                if ($id < 1) {
                    $data['success'] = false;
                    $data['msg'] = 'O campo id é obrigatório.';
                    api_response($data);
                }

                $product = ORM::for_table('sys_items')->where('type', 'Product')->find_one($id);
                if (!$product) {
                    $data['success'] = false;
                    $data['msg'] = 'Produto não encontrado.';
                    api_response($data);
                }

                if (!isset($_POST['sales_price']) && !isset($_POST['item_number'])) {
                    $data['success'] = false;
                    $data['msg'] = 'Informe sales_price e/ou item_number para atualizar o produto.';
                    api_response($data);
                }

                if (isset($_POST['sales_price'])) {
                    $price = Finance::amount_fix($_POST['sales_price']);
                    if (!is_numeric($price) || $price < 0) {
                        $data['success'] = false;
                        $data['msg'] = 'Preço de venda inválido.';
                        api_response($data);
                    }
                    $product->sales_price = $price;
                }
                if (isset($_POST['item_number'])) {
                    if (filter_var($_POST['item_number'], FILTER_VALIDATE_INT) === false || (int) $_POST['item_number'] < 0) {
                        $data['success'] = false;
                        $data['msg'] = 'Estoque inválido. item_number deve ser um número inteiro igual ou maior que zero.';
                        api_response($data);
                    }
                    $product->item_number = (string) ((int) $_POST['item_number']);
                }

                $product->save();

                $updated_product = ORM::for_table('sys_items')->find_one($id);
                $data['result'] = api_product_data($updated_product);
                $data['msg'] = 'Produto atualizado com sucesso.';
                api_response($data);
            break;

            case 'device_online_offline':
                $online = _post('online');
                $ip = _post('ip');
                $device = ORM::for_table('sys_dispositivos')->where("ip", $ip)->find_one();
                $dispositivo_usuario = $device->nome;
                if($device->online == "1" && $online == "0"){
                    notificar_admin("🚨 Alerta:\\nFalha ao acessar o radio 5G\\nLocal: *$dispositivo_usuario - $ip*, foram feitas 10 tentativas nenhuma com resposta.");
                    $device->online = $online;
                    $device->save();
                }else if($device->online == "0" && $online == "1"){
                    notificar_admin("🟢 Alerta:\\nO radio 5G voltou a funcionar\\nLocal: *$dispositivo_usuario - $ip*, nada precisará ser feito.");
                    $device->online = $online;
                    $device->save();
                }
                
                $data['result'] = true;
                $data['msg'] = 'OK';
                api_response($data);
                
            break;

            case 'send_whatsapp_message':
                $numero = _post('numero');
                $mensagem = _post('mensagem');
                $response = sendMessage($numero, $mensagem);
                $data['result'] = true;
                $data['success'] = true;
                api_response($data);
            break;

            case 'logs':
                $date = _post('date');
                $type = _post('type');
                $description = _post('description');
                $ip = _post('ip');
                $ci = ORM::for_table('sys_logs_devices')->create();
                $ci->date = $date;
                $ci->type = $type;
                $ci->ip = $ip;
                $description = str_replace("ping replies in a row", "Perdeu uma resposta de ping seguida, se continuar o radio será reiniciado", $description);
                $description = str_replace("Watchdog is checking", "MinasForceDog está verificando", $description);
                $description = str_replace("<30>", "Comando executado: ", $description);
                $description = str_replace("<29>", "Radio de origem: $ip", $description);
                $description = str_replace("exited. Scheduling for restart.", "foi encerrado. Agendando reinicialização. Se existir erro em CCQ ou sinal ou travamento, será reiniciado automaticamente.", $description);
                $ci->description = $description;
                $device = ORM::for_table('sys_dispositivos')->where("ip", $ip)->find_one();
                $ci->device = $device->ip;
                $ci->userid = 1;
                $ci->save();

                $data['result'] = true;
                $pos = strpos($description, "Scheduling for restart.");
                
                $users = ORM::for_table('sys_users')->find_many();
                foreach($users as $user){
                    $userName = $user["fullname"];
                    if($pos !== false){
                        $data['msg'] = "🐕 *MF DOG:* \\n\\nOlá $userName, o cão de guarda agendou a reinicialização do dispositivo *$ip* se houver perda de sinal ou se taxa de transferência abaixo de 49% ou travamento do dispositivo. \\n\\nNada precisa ser feito. Para ver o logs clique no link abaixo:\\n\\nhttps://starlink.minasforce.com.br/?ng=devices/logs/";
                    }else{
                        $data['msg'] = "🐕 *MF DOG:* \\n\\nOlá $userName, um novo log foi registrado para o dispositivo *$ip* com a seguinte descrição: *$description* \\n\\nPara ver os logs clique no link abaixo:\\n\\nhttps://starlink.minasforce.com.br/?ng=devices/logs/";
                    }
                    if(!empty($user["phonenumber"])){
                        if($userName == "Vinicius Gomes"){
                            $msg = $data['msg'];
                            sendMessage("55".$user["phonenumber"], $msg);
                        }else if($userName == "Marcos Rafael Rodrigues"){
                            sendMessage("55".$user["phonenumber"], $data['msg']);
                        }
                    }
                }
                api_response($data);
            break;

            case 'get_contact_details_by_id':

               $id = _post('id');

                $d = ORM::for_table('crm_accounts')->where('id',$id)->limit(1)->find_array();
               if($d){
                   $data['result'] = $d;
                   api_response($data);
               }
               else{
                   $data['result'] = false;
                   $data['msg'] = 'Contact Not Found';
                   api_response($data);
               }

            break;


            case 'get_contact_details_by_email':
                echo "Entrou ";
                $email = _post('email');
                $d = ORM::for_table('crm_accounts')->where('email',$email)->limit(1)->find_array();
                if($d){
                    $data['result'] = $d;
                    api_response($data);
                }
                else{
                    $data['result'] = false;
                    $data['msg'] = 'Contact Not Found';
                    api_response($data);
                }

                break;


            case 'get_contact_details_by_token':

                $token = _post('token');

                $d = ORM::for_table('crm_accounts')->where('token',$token)->limit(1)->find_array();
                if($d){
                    $data['result'] = $d;
                    api_response($data);
                }
                else{
                    $data['result'] = false;
                    $data['msg'] = 'Contact Not Found';
                    api_response($data);
                }

                break;


                case 'get_invoice_details_by_id':

                $id = _post('id');

                $d = ORM::for_table('sys_invoices')->where('id',$id)->limit(1)->find_array();
                if($d){
                    $data['result'] = $d;
                    api_response($data);
                }
                else{
                    $data['result'] = false;
                    $data['msg'] = 'Invoice Not Found';
                    api_response($data);
                }

                break;

            case 'get_contacts':
                $d = ORM::for_table('crm_accounts')->find_array();
                $data['result'] = $d;
                api_response($data);
                break;
            case 'get_invoices_by_contact_email':

                $email = _post('email');
                $cid = '';
                $i = false;

                $d = ORM::for_table('crm_accounts')->where('email',$email)->find_one();

                if($d){
                    $cid = $d['id'];
                    $i = ORM::for_table('sys_invoices')->where('userid',$d['id'])->find_array();
                }

                $data['result'] = $i;
                $data['email'] = $email;
                $data['cid'] = $cid;

                api_response($data);

                break;
            case 'get_invoices_by_celular':

                $celular = _post('celular');
                $cid = '';
                $i = false;

                $d = ORM::for_table('crm_accounts')->where('phone',$celular)->find_one();

                if($d){
                    $cid = $d['id'];
                    $i = ORM::for_table('sys_invoices')->where('userid',$d['id'])->where('status', 'Unpaid')->find_array();
                }

                $data['result'] = $i;
                $data['celular'] = $celular;
                $data['cid'] = $cid;

                api_response($data);

                break;

            case 'get_invoices_by_contact_id':

                $id = _post('id');
                $cid = '';
                $i = false;

                $d = ORM::for_table('crm_accounts')->find_one($id);

                if($d){
                    $cid = $d['id'];
                    $i = ORM::for_table('sys_invoices')->where('userid',$d['id'])->find_array();
                }

                $data['result'] = $i;
                $data['id'] = $id;
                $data['email'] = $d['email'];
                $data['cid'] = $cid;

                api_response($data);

                break;


           case 'get_recent_invoices_by_contact_id':

                $id = _post('id');
                $cid = '';
                $i = false;

                $d = ORM::for_table('crm_accounts')->find_one($id);

                if($d){
                    $cid = $d['id'];
                    $i = ORM::for_table('sys_invoices')->where('userid',$d['id'])->limit(5)->find_array();
                }

                $data['result'] = $i;
                $data['id'] = $id;
                $data['email'] = $d['email'];
                $data['cid'] = $cid;

                api_response($data);

                break;


            case 'login':

                $email = _post('email');
                $password = _post('password');

                $l = Contacts::login($email,$password);

                if($l){
                    $data['token'] = $l;
                }
                else{

                    $data['token'] = false;

                }


                api_response($data);




                break;

            case 'logout':

                $token = _post('token');

                $l = Contacts::logout_using_token($token);

                if($l){
                    $data['logout'] = true;
                }
                else{
                    $data['logout'] = false;
                }

                api_response($data);
                break;

            case 'ib_info':

                foreach ($config as $key => $value){



                    $data[$key] = $value;
                }

                api_response($data);

                break;
            case 'save_speead':
                $spead = ORM::for_table("sys_appconfig")->where('setting', 'max_spead')->find_one();    
                $spead->value = _post("download");
                $spead->value = _post("upload");
                $res = new Response("Erro", 0, "clienteId não pode ser nulo");
                api_response($res->get());
            break;    
            case 'plugin':


                $plugin = _post('plugin');

                $api_path = 'application/plugins/'.$plugin.'/api.php';

                if(file_exists($api_path)){

                    $data['api_found'] = 'Yes';

                    include $api_path;

                }

                else{
                    $data['api_found'] = 'No';
                }
                api_response($data);
                break;
                
            case 'saveInvoice':
                $email = _post('email');
                $item = ORM::for_table('crm_accounts')->where('email',$email)->find_one();             
                if(empty($item) or $item == null){
                    $res = new Response("Erro", 0, "Este e-mail não existe no banco de dados");
                    api_response($res->get());
                    exit;
                }
                $item['userid'] = $item['id'];
                $item['description'] = _post('description');
                $item['qty'] = _post('qty');
                $item['amount'] = _post('amount');
                $item['subtotal'] = $item['amount'];
                
                $item['taxed'] = 0; // Pegar taxa no sistema
                $item['tax'] = 0; // Pegar taxa no sistema
                // var_dump($item); exit;
                $item['taxname'] = 0;
                $item['taxrate'] = 0;
                $item['invoicenum'] = 0;
                $item['cn'] = 0;
                $item['duedate'] = _post('duedate');
                
                // Calcular valor total
                $totalValue = floatval($item['amount']) * intval($item['qty']);
                $item['total'] = $totalValue;
                // Validar email
                if(empty($item['userid'])) {
                    $res = new Response("Erro", 0, "clienteId não pode ser nulo");
                    api_response($res->get());
                    exit;
                }
            $invoiceid = InvoiceDao::saveInvoice($item);
            InvoiceDao::saveInvoiceItem($item, $invoiceid);
            $sys_invoice = ORM::for_table('sys_invoices')->find_one($invoiceid);
            $invoice = new stdClass;
            $invoice->vtoken = $sys_invoice->vtoken;
            $invoice->id = $invoiceid;
            $invoice->Status = "OK";
            api_response($invoice);
            break;
            
            case "statusBoletoUpaid":
                $statusPayment = ORM::for_table('sys_invoices')->where('status','Unpaid')->where_not_equal('invoicenum', '0')->where_not_equal('invoicenum', '')->find_array();
                api_response($statusPayment);
                break;
            

            case "updateBoletoToPaid":
                $id = _post('id');
                $invoice = ORM::for_table('sys_invoices')->find_one($id);
                $invoice->status = "Paid";
                $invoice->save();
                $res = new Response("OK", "200", "Updated");
                api_response($res->get());
                break;

            default:
                $data['msg'] = 'Method Not Found';
                api_response($data);
                exit;
            }
        }
        else{
            $data['msg'] = 'Invalid API Key';
            api_response($data);
        }
        
    }
    else
    { 
        $jsonData = file_get_contents('php://input');
        if(isset($jsonData)){
            $position = strpos($jsonData, "61 654 140"); //Identifica o inicio do CNPJ que vai no boleto
            $data = json_decode($jsonData);
            if($position){
                _log($data->summary,"Boleto", 1);
                $result = explode("MF-", $data->summary);
                $userId = explode(" ", $result[1])[0];
                //Buscar o cliente
                $i = ORM::for_table('sys_invoices')->where('userid',$userId)->where('status','Unpaid')->find_array();
                //Criar pegar a ultima fatura e mudar de pendente para pago.
                $invoice = ORM::for_table('sys_invoices')->find_one($i[0]['id']);
                $invoice->status = "Paid";
                $invoice->save();
                
                $transaction = ORM::for_table('sys_transactions')->create();
                $transaction->category = "Boleto";
                $transaction->method = "Boleto";
                $transaction->description = "Recebimento via Boleto: ".$invoice->account;
                create_transaction_credit($transaction, $invoice);
                notificar($transaction, $invoice);
                exit;
            }
            if($data->action == "payment.updated"){
                _log($jsonData,"PIX", 1);
                $payment = $data->data;
                $processor  = ORM::for_table('sys_pg')->where('processor','pagseguro')->find_one();
                $json = get_payload_mercadopago('/v1/payments/'.$payment->id, $processor);
                $d = json_decode($json);
                if($d->status == "approved"){
                    $invoice = ORM::for_table('sys_invoices')->find_one($d->external_reference);
                    $invoice->status = "Paid";
                    $invoice->save();
                    $transaction = ORM::for_table('sys_transactions')->create();
                    $transaction->category = "PIX";
                    $transaction->method = "Pix";             
                    $transaction->description = "Recebimento via PIX: ".$invoice->account;
                    create_transaction_credit($transaction, $invoice);
                    notificar($transaction, $invoice);
                    $res = new Response("OK", "200", "Updated");
                    api_response($res->get());
                }
            }
        }
        else{
            $data['msg'] = 'API Key is required';
            api_response($data);
        }
        
    }
    
    class UserDao {
        public static function registerUser($user) {
            $d = ORM::for_table('crm_accounts')->create();
            $d->account = $user['account'];
            $d->password = $user['npassword'];
            $d->fname = $user['fname'];
            $d->lname = $user['lname'];
            $d->phone = $user['phone'];
            $d->email = $user['email'];
            $d->address = $user['address'];
            $d->city = $user['city'];
            $d->state = $user['state'];
            $d->zip = $user['zip'];
            $d->country = $user['country'];
            $d->company = $user['company'];
            $d->jobtitle = null;
            $d->cid = 0;
            $d->balance = 0;
            $d->notes = 0;
            $d->token = 0;
            $d->ts = 0;
            $d->img = 0;
            $d->web = 0;
            $d->facebook = 0;
            $d->google = 0;
            $d->linkedin = 0;
            $d->jobtitle = 0;
            $d->tags = 0;
            $d->save();
        }
    }
    
    class InvoiceDao {
        public static function saveInvoice($is) {
            $iid = 0;
            $r = 0;
            $vencimento = date('Y-m-d', strtotime("+10 days"));
            $dd = null;
            $nd = date('Y-m-d');
            $vtoken = _raid(10);
            $ptoken = _raid(10);
            $d = ORM::for_table('sys_invoices')->create();
            $d->userid = $is['userid'];
            $d->account = $is['account'];
            $d->date = /*$today*/ date('Y-m-d');
            $d->duedate = $vencimento;
            $d->subtotal = $is['subtotal'];
            $d->total = $is['total'];
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
            $d->paymentmethod = '0';
            $d->save();
            $invoiceid = $d->id();
            // //set previous invoice r 0
            // $s = ORM::for_table('sys_invoices')->find_one($iid);
            // $s->r = '0';
            // $s->save();
            return $invoiceid;
        }
        
        public static function saveInvoiceItem($item, $invoiceid) {
            $t = ORM::for_table('sys_invoiceitems')->create();
            $t->invoiceid = $invoiceid;
            $t->userid = $item['userid'];
            $t->relid = 0;
            $t->type = '';
            $t->taxamount = 0;
            $t->duedate = $item["duedate"];
            $t->description = $item['description'];
            $t->qty = $item['qty'];
            $t->amount = $item['amount'];
            $t->total = $item['total'];
            $t->taxed = $item['taxed'];
            $t->itemcode = '';
            $t->paymentmethod = '';
            $t->notes = '';
            $t->save();
        }
    }

function get_payload_mercadopago($endpoint, $processor){
    $url = 'https://api.mercadopago.com'.$endpoint;
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

function sendMessage($numero, $msg){
    $whatsApp = new WhatsApp();
    return $whatsApp->Send($msg, $numero, '', 0, 0);
}

function normalizarNumeroWhatsApp($numero){
    $numero = preg_replace('/\D+/', '', (string) $numero);
    if (substr($numero, 0, 2) !== '55') {
        $numero = '55' . $numero;
    }
    return $numero;
}

function create_transaction_credit($transaction, $invoice){
    $transaction->amount = $invoice->total;
    $transaction->payer = "";
    $transaction->payee = "";
    $transaction->payeeid = 0;
    $transaction->payerid = 0;
    $transaction->method = "";
    $transaction->ref = "";
    $transaction->status = "Cleared";
    $transaction->date =  date('Y-m-d');
    $transaction->account = $invoice->account;
    $transaction->type = "Income";
    $transaction->save();
}

function notificar($transaction, $invoice){
    $vtoken = $invoice->vtoken;
    $invoiceId =  $invoice->id;
    $tid = $transaction->id();
    $client = ORM::for_table('crm_accounts')->where("id", $invoice->userid)->find_one();
    //Notificando o cliente
    sendMessage("55".$client->phone, "✅ *Pagamento Confirmado* \\n\\nOlá ".$client["fullname"].", Seu pagamento foi identificado pelo nosso sistema, desde já agradecemos por utilizar nossos serviços! para mais detalhes clique no link abaixo.\\n\\n".APP_URL."/?ng=client/iview/$invoiceId/token_$vtoken");
    //Notificar os administradores de criação de nova transacao, fazer um metodo para isso.
    $users = ORM::for_table('sys_users')->find_many();
    foreach($users as $user){
        if(!empty($user["phonenumber"]))
            sendMessage("55".$user["phonenumber"]. "", "✅ *Minas Force Pay* \\n\\nOlá ".$user["fullname"].", Foi lançado um pagamento de *$invoice->total* creditos aplicado por  *$invoice->account*, OBS: Se o split de pagamento estiver ativado você recebera valores em até uma hora. \\n\\n".APP_URL."/?ng=transactions/manage/$tid");
    }
}

function notificar_admin($msg){
    $users = ORM::for_table('sys_users')->find_many();
    foreach($users as $user){
        echo $userName = $user["fullname"];
        if(!empty($user["phonenumber"])){
            sendMessage("55".$user["phonenumber"], $msg);
        }
    }
}
