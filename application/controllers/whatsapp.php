<?php
_auth();
require_once("application/plugins/whatsapp/whatsApp.php");

$ui->assign('_application_menu', 'whatsapp');
$ui->assign('_title', $_L['WhatsApp Business'].'- '. $config['CompanyName']);
$ui->assign('_st', $_L['WhatsApp Business']);
$action = $routes['1'];
$user = User::_info();
$ui->assign('user', $user);

Event::trigger('whatsapp');


switch ($action) {
    case 'config':
        $result = getConfig();
        $ui->assign('xfooter', Asset::js(array('dp/i18n/'.$config['language'],'numeric','whatsapp')));

        if($result['clienteId']){ 
            
            $config = new stdClass;
            $config->clienteId = $result['clienteId']->value;
            $config->key = $result['key']->value;
            $config->group = $result['group']->value;

            $ui->assign("config", $config);
        } else {
            $config = new stdClass;
            $config->clienteId = "";
            $config->key = "";
            $config->group = "";

            $ui->assign("config", $config);
        }
        $ui->display('whatsapp-config.tpl');
        break;

    case 'config-post':
            $clientId = $_POST['clientId'];
            $secretId = $_POST['secretId'];
            $group = $_POST['group'];
    
            $d = ORM::for_table('sys_appconfig')->where('setting','notifiqueme_clienteId')->find_one();
            if(!$d)
                $d = ORM::for_table('sys_appconfig')->create();

            $d->setting = 'notifiqueme_clienteId';
            $d->value = $clientId;
            $d->save();
            
            $d = ORM::for_table('sys_appconfig')->where('setting','notifiqueme_key')->find_one();
            if(!$d)
                $d = ORM::for_table('sys_appconfig')->create();
           
            $d->setting = 'notifiqueme_key';
            $d->value = $secretId;
            $d->save();
    
            $d = ORM::for_table('sys_appconfig')->where('setting','notifiqueme_group')->find_one();
            if(!$d)
                $d = ORM::for_table('sys_appconfig')->create();
           
            $d->setting = 'notifiqueme_group';
            $d->value = $group;
            $d->save();

            $result = getConfig();
            if($result){
                $config = new stdClass;
                $config->clienteId = $result['clienteId']->value;
                $config->key = $result['key']->value;
                $config->group = $result['group']->value;
    
                $ui->assign("config", $config);
            }
            echo $_L['Whatsapp business config Successfully'];
            break;

    case "test":
        $ui->assign('xfooter', Asset::js(array('dp/i18n/'.$config['language'],'numeric','whatsapp')));
        $ui->display('whatsapp-test.tpl');
        break;
        
    case "test_send":
        // Dados telefone
        $codPais = $_POST["codPais"];
        $ddd = _post("ddd");
        $number = $_POST["number"];
        $celPhone = intval("{$codPais}{$ddd}{$number}");
        
        $type = intval($_POST["type"]);
        $message = $_POST["message"];
        $whats = new WhatsApp();
        $whats->Send($message, $celPhone, "👁️‍🗨️ Fatura: \n",  $type);
        echo $_L['Test performed successfully'];
        break;

    case "charges":
        $ui->assign('xfooter', Asset::js(array('dp/i18n/'.$config['language'],'numeric','whatsapp')));
        $ui->display('whatsapp-charges.tpl');
        break;
    default:
        echo 'action not defined';
}

function getConfig() {
    $result['clienteId'] = ORM::for_table('sys_appconfig')->where('setting','notifiqueme_clienteId')->find_one();
    $result['key'] = ORM::for_table('sys_appconfig')->where('setting','notifiqueme_key')->find_one();
    $result['group'] = ORM::for_table('sys_appconfig')->where('setting','notifiqueme_group')->find_one();
    return $result;
}
