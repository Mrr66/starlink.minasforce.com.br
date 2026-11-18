<?php
require_once("application/plugins/whatsapp/whatsApp.php");

if(!isset($myCtrl)){
    $myCtrl = 'contacts';
}
_auth();
$ui->assign('_application_menu', 'Devices');
$ui->assign('_title', $_L['Devices'].' - '. $config['CompanyName']);
$ui->assign('_st', $_L['Devices']);

$action = $routes['1'];
$user = User::_info();
$ui->assign('user', $user);

$ui->assign('jsvar', '
_L[\'Working\'] = \''.$_L['Working'].'\';
_L[\'Submit\'] = \''.$_L['Submit'].'\';
 ');
if($action == '')
    $action = 'devices';

switch ($action) {
    case 'add-device':
        Event::trigger('devices/add-device/');
        $ip =  _post('ip');
        $id =  _post('id');
        
        $device = ORM::for_table('sys_dispositivos')->where("ip", $ip)->find_one();
        if( $device && $device->id != $id) 
            echo json_encode(["status" => "ERROR", "msg" => "O IP já existe!"]);
        else{
            if($id > 0)
                $device = ORM::for_table('sys_dispositivos')->where("id", $id)->find_one();
            else
                $device = ORM::for_table('sys_dispositivos')->create();
            $device->nome = _post('nome');
            $device->ip = $ip;
            $device->situacao = _post('situacao');
            $device->ssid = _post('ssid');
            $device->senha = _post('senha');
            $device->dispositivo_senha = _post('dispositivo_senha');
            $device->dispositivo_usuario = _post('dispositivo_usuario');
            $device->tunel = _post('tunel');
            $device->local_dispositivo = _post('local_dispositivo');
            $device->longitude = _post('longitude');
            $device->latitude = _post('latitude');
            $device->notas = _post('notas');
            $device->save();
            echo json_encode(["status" => "SUCESSO", "msg" => "Cadastrado com sucesso!"]);
        }
    break;

    case 'remove':
        $id = _post('id');
        $disp = ORM::for_table('sys_dispositivos')->find_one($id);
        $dataNow = date('Y/m/d H-i-s');
        $log = ORM::for_table('sys_logs_devices')->create();
        $log->date = $dataNow;
        $log->description = "Dispositivo: $disp->ip, Foi excluido Por: $user->fullname, data: $dataNow.";
        $log->type = "admin";
        $log->userId = $user->id;
        $log->ip = "0.0.0.0";
        $log->save();

        ORM::for_table('sys_dispositivos')->where('id',$id)->delete_many();
    break;

    case 'modal-tv':
            $id = $routes[2];
           
            $device = ORM::for_table('sys_dispositivos')->where("id", $id)->find_one();
            if($device)
                $ui->assign('device', $device);
            else{

                $device = new stdclass;
                $device->nome = "";
                $device->endereco = "";
                $device->situacao = "ATIVO";
                $device->ip = "";
                $device->online = 0;
                $device->tunel = "";
                $device->id = 0;
                $device->ssid = "";
                $device->senha = "";
                $device->dispositivo_senha = "";
                $device->dispositivo_usuario = "";
                $device->notas = "";
                $ui->assign('device', $device);
            }
            
        $css_arr = array('s2/css/select2.min','modal','dp/dist/datepicker.min','redactor/redactor');
        $js_arr = array('redactor/redactor.min','s2/js/select2.min','s2/js/i18n/'.lan(),'dp/dist/datepicker.min','dp/i18n/'.$config['language'],'numeric','modal');
        $ui->assign('xheader', Asset::css($css_arr));
        $ui->assign('xfooter', Asset::js($js_arr));
        $timelines = [];
        
        $ui->assign('timelines', $timelines);
        $ui->display('dispositivos.tpl');
    break;

    case 'minasforcedog':
         $ui->assign('jsvar', '_L[\'are_you_sure\'] = \''.$_L['are_you_sure'].'\';');
        $ui->assign('_st', $_L['Minas Force Dog']);

        $devices = Model::factory('sys_dispositivos')->where("watchdog", 1)->find_array();

        $ui->assign('xheader',Asset::css(array('modal')));
        $ui->assign('xfooter',Asset::js(array('modal','minasforcedog/ap_cpe')));

        $ui->assign('devices',$devices);


        $ui->display('minasforcedog.tpl');
    break;

    case 'maps':
         $ui->assign('jsvar', '_L[\'are_you_sure\'] = \''.$_L['are_you_sure'].'\';');
        $ui->assign('_st', $_L['Maps Network']);

        //$devices = Model::factory('sys_dispositivos')->where("watchdog", 1)->find_array();

        $ui->assign('xheader',Asset::css(array('modal')));
        $ui->assign('xfooter',Asset::js(array('modal','minasforcedog/map_network')));

        //$ui->assign('devices',$devices);
        $ui->display('maps_network.tpl');
    break;

    case 'logs':
         $ui->assign('jsvar', '_L[\'are_you_sure\'] = \''.$_L['are_you_sure'].'\';');
        $ui->assign('_st', $_L['Logs']);

        $logs = Model::factory('sys_logs_devices')->order_by_desc("id")->find_array();

        $ui->assign('xheader',Asset::css(array('modal')));
        $ui->assign('xfooter',Asset::js(array('modal','minasforcedog/ap_cpe')));

        $ui->assign('logs',$logs);
        $ui->display('list-logs-ap-cpe.tpl');
    break;

    case 'modal_add_station':
        $f_type = 'add';
        Event::trigger('devices/modal_add_station/');
        $cs = ORM::for_table('sys_dispositivos')->where("situacao", "ATIVO")->where("watchdog", 0)->find_array();
        $ui->assign('c', $cs); // may add this $config['country_code']
        $ui->assign('f_type',$f_type);
        $ui->display('modal_add_station_cpe.tpl');
    break;

    case 'add_watchdog_post':
        $data = ib_posted_data();
        $d = ORM::for_table('sys_dispositivos')->find_one($data['cid']);
        $d->watchdog = 1;
        $d->save();
        echo $d->id();
    break;

    case 'edit':

        Event::trigger('contacts/edit/');

        $cid = _post('cid');
        $d = ORM::for_table('crm_accounts')->find_one($cid);
        if($d){
            $fs = ORM::for_table('crm_customfields')->where('ctype','crm')->order_by_asc('id')->find_many();
            $ui->assign('fs',$fs);
            $ui->assign('countries',Countries::all($d['country']));
            $ui->assign('d',$d);
            $tags = Tags::get_all('Contacts');
            $ui->assign('tags',$tags);
            $dtags = explode(',',$d['tags']);
            $ui->assign('dtags',$dtags);

            // find all groups

            $gs = ORM::for_table('crm_groups')->order_by_asc('sorder')->find_array();

            $ui->assign('gs',$gs);

            $g_selected_id = route(4);

            if($g_selected_id){
                $ui->assign('g_selected_id',$g_selected_id);
            }
            else{
                $ui->assign('g_selected_id','');
            }

            $currencies = Model::factory('Models_Currency')->find_array();

            $ui->assign('currencies',$currencies);

            $ui->display('ajax.contact-edit.tpl');
        }
        else{

        }


    break;
    
    case 'devices':
        $xheader = Asset::css(array('dashboard/dashboard','modal','css/progressbarW3'));
        $xfooter = Asset::js(array('dashboard/graph','chart/echarts.min','numeric','dashboard/dashboard', 'modal','sn/summernote.min', 'jslib/tv.js?='.rand(0, 9999999)));

        $ui->assign('xheader', $xheader);
        $ui->assign('xfooter', $xfooter);
        $ui->display('list-devices.tpl');

    break;
    case 'get-devices':
        $search = $routes[2];
        #$typeList = $routes[3];
        if($search == null)
            $result = ORM::for_table('sys_dispositivos')->order_by_asc('nome')->find_array();
        else{
            if($search == 'online'){
                $result = ORM::for_table('sys_dispositivos')->where("online", 1)->order_by_asc('nome')->find_array();
            }
            else if($search == 'offline'){
                $result = ORM::for_table('sys_dispositivos')->where("online", 0)->order_by_asc('nome')->find_array();
            }
            else{
                $result = ORM::for_table('sys_dispositivos')->where("local_dispositivo",  $search)->order_by_asc('nome')->find_array();
            }
        }
        echo "".json_encode($result)."";
    break;

    case 'get-devices-watchdog':
        $result = ORM::for_table('sys_dispositivos')->where('watchdog', 1)->order_by_asc('nome')->find_array();
        echo "".json_encode($result)."";
    break;
    case 'delete':

        Event::trigger('contacts/delete/');


        $id = $routes['2'];
        if($_app_stage == 'Demo'){
            r2(U.$myCtrl.'/list/', 'e', 'Sorry! Deleting Account is disabled in the demo mode.');
        }
        $d = ORM::for_table('crm_accounts')->find_one($id);
        if($d){
            $d->delete();
            r2(U.$myCtrl.'/list/', 's', $_L['account_delete_successful']);
        }

        break;


    case 'more':

        Event::trigger('contacts/more/');

        $cid = _post('cid');
        $d = ORM::for_table('crm_accounts')->find_one($cid);
        if($d){
            $ui->assign('countries',Countries::all($d['country']));
            $ui->assign('d',$d);
            $ui->display('ajax.contact-more.tpl');
        }
        else{

        }


        break;

    case 'edit-more':

        Event::trigger('contacts/edit-more/');

        $id = _post('cid');
        $d = ORM::for_table('crm_accounts')->find_one($id);
        if($d){
            $img = _post('picture');
            $facebook = _post('facebook');
            $google = _post('google');
            $linkedin = _post('linkedin');

            $msg = '';



            //check email already exist





            if($msg == ''){


                $d = ORM::for_table('crm_accounts')->find_one($id);

                $d->img = $img;
                $d->facebook = $facebook;
                $d->google = $google;
                $d->linkedin = $linkedin;
                $d->save();
                echo $d->id();
            }
            else{
                echo $msg;
            }

        }
        else{
            r2(U.$myCtrl.'/list/', 'e', $_L['Account_Not_Found']);
        }


        break;


    case 'edit-notes':

        Event::trigger('contacts/edit-notes/');

        $id = _post('cid');
        $d = ORM::for_table('crm_accounts')->find_one($id);
        if($d){

            $notes = _post('notes');

            $msg = '';



            //check email already exist





            if($msg == ''){


                $d = ORM::for_table('crm_accounts')->find_one($id);


                $d->notes = $notes;
                $d->save();
                echo $d->id();
            }
            else{
                echo $msg;
            }

        }
        else{
            r2(U.$myCtrl.'/list/', 'e', $_L['Account_Not_Found']);
        }


        break;

    case 'render-address':

        Event::trigger('contacts/render-address/');

        $cid = _post('cid');
        $d = ORM::for_table('crm_accounts')->find_one($cid);
        $address = $d['address'];
        $city = $d['city'];
        $state = $d['state'];
        $zip = $d['zip'];
        $country = $d['country'];
        echo "$address
$city
$state $zip
$country
";
        break;


    case 'send_email':

        Event::trigger('contacts/send_email/');

        $msg = '';
        $cid = _post('cid');
        $d = ORM::for_table('crm_accounts')->find_one($cid);
        $email = $d['email'];
        $toname = $d['account'];
$subject = _post('subject');
        if($subject == ''){
            $msg .= $_L['Subject is Empty'].' <br>';
        }
        $message = $_POST['message'];
if($message == ''){
    $msg .= $_L['Message is Empty'].' <br>';
}
        if($msg == ''){
            //send email
            Notify_Email::_send($toname,$email,$subject,$message,$cid);
            echo $cid;

        }
        else{
            echo $msg;
        }
        break;


    case 'modal_add':

        Event::trigger('contacts/modal_add/');

        $ui->assign('countries',Countries::all($config['country'])); // may add this $config['country_code']
        $ui->display('modal_add_contact.tpl');


        break;


    case 'set_view_mode':

        Event::trigger('contacts/set_view_mode/');

//        if(isset($routes['2']) AND ($routes['2'] != 'tbl')){
//            $mode = 'card';
//        }
//        else{
//            $mode = 'tbl';
//        }

        if(isset($routes[2]) AND ($routes[2] != '')){
            $mode = $routes['2'];
        }

        else{
            $mode = 'tbl';
        }

        $available_mode = array("tbl", "card", "search");
        if (in_array($mode, $available_mode)) {

            update_option('contact_set_view_mode',$mode);

        }

        r2(U.'contacts/list/');

        break;



    case 'export_csv':


        $fileName = 'contacts_'.time().'.csv';

        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Description: File Transfer');
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename={$fileName}");
        header("Expires: 0");
        header("Pragma: public");

        $fh = @fopen( 'php://output', 'w' );

        $headerDisplayed = false;

       // $results = ORM::for_table('crm_Accounts')->find_array();
        $results = db_find_array('crm_accounts',array('id','account','company','phone','email','address','city','state','zip','country','balance','tags'));

        foreach ( $results as $data ) {
            // Add a header row if it hasn't been added yet
            if ( !$headerDisplayed ) {
                // Use the keys from $data as the titles
                fputcsv($fh, array_keys($data));
                $headerDisplayed = true;
            }

            // Put the data into the stream
            fputcsv($fh, $data);
        }
// Close the file
        fclose($fh);



        break;



    case 'dev_demo_data':


        // this only work with dev mode
        is_dev();





        break;

    case 'import_csv':


        $ui->assign('xheader', Asset::css(array('dropzone/dropzone')));


        $ui->assign('xfooter', Asset::js(array('dropzone/dropzone','contacts/import')));



        $ui->display('contacts_import.tpl');



        break;

    case 'csv_upload':

        $uploader   =   new Uploader();
        $uploader->setDir('application/storage/temp/');
       // $uploader->sameName(true);
        $uploader->setExtensions(array('csv'));  //allowed extensions list//
        if($uploader->uploadFile('file')){   //txtFile is the filebrowse element name //
            $uploaded  =   $uploader->getUploadName(); //get uploaded file name, renames on upload//

            $_SESSION['uploaded'] = $uploaded;

        }else{//upload failed
            _msglog('e',$uploader->getMessage()); //get upload error message
        }


        break;

    case 'csv_uploaded':


        if(isset($_SESSION['uploaded'])){

            $uploaded = $_SESSION['uploaded'];

          // _msglog('s',$uploaded);

//            $csvData = file_get_contents('application/storage/temp/'.$uploaded);
//            $lines = explode(PHP_EOL, $csvData);
//            $contacts = array();
//            foreach ($lines as $line) {
//                $contacts[] = str_getcsv($line);
//            }




            $csv = new parseCSV();
            $csv->auto('application/storage/temp/'.$uploaded);

            $contacts = $csv->data;



            $cn = 0;

            foreach($contacts as $contact){

                $data = array();
                $data['account'] = $contact['Full Name'];
                $data['email'] = $contact['Email'];
                $data['phone'] = $contact['Phone'];
                $data['address'] = $contact['Address'];
                $data['city'] = $contact['City'];
                $data['zip'] = $contact['Zip'];
                $data['state'] = $contact['State'];
                $data['country'] = $contact['Country'];
                $data['company'] = $contact['Company'];



                $save = Contacts::add($data);

                if(is_numeric($save)){

                    $cn++;

                }


            }


            _msglog('s',$cn.' Contacts Imported');

//            ob_start();
//            var_dump($contacts);
//            $result = ob_get_clean();
//
//            _msglog('s',$result);



        }
        else{

            _msglog('e','An Error Occurred while uploading the files');

        }


        break;


    case 'groups':

        // find all groups

        $gs = ORM::for_table('crm_groups')->order_by_asc('sorder')->find_array();

        $ui->assign('gs',$gs);

        $ui->assign('xfooter',Asset::js(array('contacts/groups')));

        $ui->assign('jsvar', '
_L[\'are_you_sure\'] = \''.$_L['are_you_sure'].'\';
 ');

        $ui->display('crm_groups.tpl');



        break;


    case 'add_group':

        $group_name = _post('group_name');

        if($group_name != ''){

            //check same group already exist

            $c = ORM::for_table('crm_groups')->where('gname',$group_name)->find_one();

            if($c){

                ib_die('A Group with same name already exist');

            }

            $d = ORM::for_table('crm_groups')->create();
            $d->gname = $group_name;
            $d->color = '';
            $d->discount = '';
            $d->parent = '';
            $d->pid = 0;
            $d->exempt = '';
            $d->description = '';
            $d->separateinvoices = '';
            $d->sorder = 0;
            $d->c1 = '';
            $d->c2 = '';
            $d->c3 = '';
            $d->c4 = '';
            $d->c5 = '';
           $d->save();

            echo $d->id();



        }
        else{

            echo 'Group Name is required';

        }



        break;


    case 'find_by_group':

        $gid = route(2);

        if($gid){

            $g = ORM::for_table('crm_groups')->find_one($gid);

            if($g){

                $d = ORM::for_table('crm_accounts')->where('gid',$gid)->order_by_desc('id')->find_array();

                $ui->assign('d',$d);
                $ui->assign('gid',$gid);

                $ui->assign('xjq',' $(".cdelete").click(function (e) {
        e.preventDefault();
        var id = this.id;
        bootbox.confirm("'.$_L['are_you_sure'].'", function(result) {
           if(result){
               var _url = $("#_url").val();
               window.location.href = _url + "delete/crm-user/" + id + "/'.$gid.'/";
           }
        });
    });
');

                $ui->display('contacts_find_by_group.tpl');


            }

        }




        break;

    case 'group_edit':


        $id = _post('id');
        $id = str_replace('e','',$id);
        $gname = _post('gname');

        $d = ORM::for_table('crm_groups')->find_one($id);

        if($d){

            // update all gname in contacts

            $o_gname = $d->gname;

            ORM::execute("update crm_accounts set gname='$gname' where gname='$o_gname'");

            $d->gname = $gname;

            $d->save();

            echo $d->id;



        }





        break;

    case 'group_email':

        $gid = route(2);

        if($gid){

            // find group


            $ds = ORM::for_table('crm_accounts')->where('gid',$gid)->where_not_equal('email','')->select('account')->select('email')->order_by_desc('id')->find_array();

            $ui->assign('ds',$ds);

            $ui->assign('xheader', Asset::css(array('s2/css/select2.min','sn/summernote','sn/summernote-bs3','sn/summernote-application')));




            $ui->assign('xfooter', Asset::js(array('s2/js/select2.min','s2/js/i18n/'.lan(),'sn/summernote.min','contacts/group_email')));
            $ui->display('contacts_group_email.tpl');

        }


        break;


    case 'group_email_post':


//        $recipients = array(
//            'person1@domain.com' => 'Person One',
//            'person2@domain.com' => 'Person Two',
//            // ..
//        );
//        foreach($recipients as $email => $name)
//        {
//            $mail->AddAddress($email, $name);
//        }



        $emails = $_POST['emails'];
        $subject = $_POST['subject'];
        $msg = $_POST['msg'];


        Ib_Email::bulk_email($emails,$subject,$msg,$user->username);

        echo 'Mail Sent!';


//       if(Ib_Email::bulk_email($emails,$subject,$msg,$user->username)){
//
//           echo 'Mail Sent!';
//
//       }
//
//        else{
//
//            echo 'An Error Occurred while sending email.';
//
//        }

        break;

        case 'environment':

            $ui->assign('jsvar', '_L[\'are_you_sure\'] = \''.$_L['are_you_sure'].'\';');
    
            $ui->assign('_application_menu', 'environment');
            $ui->assign('_st', $_L['Companies']);
    
    // find all companies
    
            $companies = Model::factory('Models_Company')->find_array();
    
            $ui->assign('xheader',Asset::css(array('modal')));
            $ui->assign('xfooter',Asset::js(array('modal','contacts/companies')));
    
            $ui->assign('environment',$companies);
    
    
            $ui->display('environment.tpl');
    
    
            break;


    case 'companies':

        $ui->assign('jsvar', '_L[\'are_you_sure\'] = \''.$_L['are_you_sure'].'\';');

        $ui->assign('_application_menu', 'companies');
        $ui->assign('_st', $_L['Companies']);

// find all companies

        $companies = Model::factory('Models_Company')->find_array();

        $ui->assign('xheader',Asset::css(array('modal')));
        $ui->assign('xfooter',Asset::js(array('modal','contacts/companies')));

        $ui->assign('companies',$companies);


        $ui->display('companies.tpl');


        break;

    case 'modal_add_company':

        $id = route(2);


        $company = false;

        if($id != ''){

            $id = str_replace('ae','',$id);
            $id = str_replace('be','',$id);

            $company = Model::factory('Models_Company')->find_one($id);

        }

        $val = array();

        if($company){
            $f_type = 'edit';
            $val['company_name'] = $company->company_name;
            $val['url'] = $company->url;
            $val['email'] = $company->email;
            $val['phone'] = $company->phone;
            $val['logo_url'] = $company->logo_url;
            $val['cid'] = $id;

//            $val[''] = $company->;
        }
        else{
            $f_type = 'create';
            $val['company_name'] = '';
            $val['url'] = 'http://';
            $val['email'] = '';
            $val['phone'] = '';
            $val['logo_url'] = '';
//            $val[''] = '';
        }

        $ui->assign('f_type',$f_type);
        $ui->assign('val',$val);


        $ui->display('modal_add_company.tpl');

        break;





    default:
        echo 'action not defined';
}

use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

function getData($headers, $rota){
    
    $handler = HandlerStack::Create();
    $client = new Client(["handler"=> $handler]);
    $url_base = "https://api.notifique-me.com";
    $usuario = getenv('NOTIFIQUE_ME_USUARIO');
    $senha = getenv('NOTIFIQUE_ME_SENHA');
    $auth = new Authorization($usuario, $senha, $url_base);

    $headers = $auth->getHeaders();
    $request = $client->get("$url_base$rota", ["headers" => $headers]);
    $result = json_decode($request->getBody());
    return $result;
}

function atualizarInvoiceConexaoSuper($fatura, $token){
    $url_base = "https://api.notifique-me.com";
    $jsonInvoice = json_encode($fatura);

    $url       = "$url_base/api/Invoice/v1";
    $cabecalho = array("Authorization: Bearer $token", 'Content-Type: application/json', 'Accept: application/json');
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,            $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER,     $cabecalho);
    curl_setopt($ch, CURLOPT_POSTFIELDS,     $jsonInvoice);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST,           true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,  'PUT');
    $resposta = curl_exec($ch);
    curl_close($ch);
    return $resposta;
}
