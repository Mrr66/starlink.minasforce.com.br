<?php
// *************************************************************************
// *                                                                       *
// * iBilling -  Accounting, Billing Software                              *
// * Copyright (c) Sadia Sharmin. All Rights Reserved                      *
// *                                                                       *
// *************************************************************************
// *                                                                       *
// * Email: sadiasharmin3139@gmail.com                                                *
// * Website: http://www.sadiasharmin.com                                  *
// *                                                                       *
// *************************************************************************
// *                                                                       *
// * This software is furnished under a license and may be used and copied *
// * only  in  accordance  with  the  terms  of such  license and with the *
// * inclusion of the above copyright notice.                              *
// * If you Purchased from Codecanyon, Please read the full License from   *
// * here- http://codecanyon.net/licenses/standard                         *
// *                                                                       *
// *************************************************************************
_auth();
$ui->assign('_application_menu', 'ps');
$ui->assign('_title', $_L['Products n Services'].'- '. $config['CompanyName']);
$ui->assign('_st', $_L['Products n Services']);
$action = $routes['1'];
$user = User::_info();
$ui->assign('user', $user);

function ps_prepare_fields()
{
    $errors = array();
    try {
        ORM::execute("ALTER TABLE `sys_items` ADD `image` varchar(255) NOT NULL DEFAULT ''");
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') === false && strpos($e->getMessage(), '1060') === false) {
            $errors[] = $e->getMessage();
        }
    }
    try {
        ORM::execute("ALTER TABLE `sys_items` ADD `location` varchar(255) NOT NULL DEFAULT ''");
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') === false && strpos($e->getMessage(), '1060') === false) {
            $errors[] = $e->getMessage();
        }
    }
    if (count($errors) > 0) {
        _log('Falha ao preparar campos de produto: ' . implode(' | ', $errors), 'System', 0);
    }
}

function ps_upload_image(&$error)
{
    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) return '';
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $upload_errors = array(
            UPLOAD_ERR_INI_SIZE => 'A imagem excede o limite upload_max_filesize configurado no PHP.',
            UPLOAD_ERR_FORM_SIZE => 'A imagem excede o tamanho permitido pelo formulário.',
            UPLOAD_ERR_PARTIAL => 'A imagem foi enviada apenas parcialmente. Tente novamente.',
            UPLOAD_ERR_NO_TMP_DIR => 'A pasta temporária do servidor não está configurada.',
            UPLOAD_ERR_CANT_WRITE => 'O servidor não conseguiu gravar a imagem no disco.',
            UPLOAD_ERR_EXTENSION => 'Uma extensão do PHP interrompeu o upload da imagem.'
        );
        $error = isset($upload_errors[$_FILES['image']['error']]) ? $upload_errors[$_FILES['image']['error']] : 'Não foi possível enviar a imagem.';
        return false;
    }
    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        $error = 'A imagem deve ter no máximo 5 MB.';
        return false;
    }

    $info = @getimagesize($_FILES['image']['tmp_name']);
    $types = array(
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_GIF => 'gif'
    );
    if (defined('IMAGETYPE_WEBP')) $types[IMAGETYPE_WEBP] = 'webp';
    if (!$info || !isset($types[$info[2]])) {
        $error = 'Formato inválido. Envie uma imagem JPG, PNG, GIF ou WebP.';
        return false;
    }

    $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        $error = 'Não foi possível criar a pasta de imagens dos produtos.';
        return false;
    }
    if (!is_writable($directory)) {
        $error = 'A pasta application/storage/products não possui permissão de escrita para o PHP.';
        return false;
    }
    $filename = 'product_' . date('YmdHis') . '_' . substr(sha1(uniqid(mt_rand(), true)), 0, 12) . '.' . $types[$info[2]];
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $directory . $filename)) {
        $error = 'Não foi possível salvar a imagem no servidor.';
        return false;
    }
    return $filename;
}

ps_prepare_fields();
switch ($action) {

    case 'modal-list':

        $d = ORM::for_table('sys_items')->order_by_asc('name')->find_many();

        echo '
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h3>'.$_L['Products n Services'].'</h3>
</div>
<div class="modal-body">

<table class="table table-striped" id="items_table">
      <thead>
        <tr>
          <th width="10%">#</th>
          <th width="20%">'.$_L['Item Code'].'</th>
          <th width="55%">'.$_L['Item Name'].'</th>

          <th width="15%">'.$_L['Price'].'</th>
        </tr>
      </thead>
      <tbody>
       ';

        foreach($d as $ds){
           $price = number_format($ds['sales_price'],2,$config['dec_point'],$config['thousands_sep']);
            echo ' <tr>
          <td><input type="checkbox" class="si"></td>
          <td>'.$ds['item_number'].'</td>
          <td>'.$ds['name'].'</td>

          <td class="price">'.$price.'</td>
        </tr>';
        }

        echo '

      </tbody>
    </table>

</div>
<div class="modal-footer">

	<button type="button" data-dismiss="modal" class="btn">'.$_L['Close'].'</button>
	<button class="btn btn-primary update">'.$_L['Select'].'</button>
</div>';

        break;


    case 'p-new':



        $ui->assign('type','Product');
        $ui->assign('xfooter', Asset::js(array('numeric','jslib/add-ps')));
        $ui->assign('xjq', '
 $(\'.amount\').autoNumeric(\'init\');
 ');

        $max = ORM::for_table('sys_items')->max('id');
        $nxt = $max+1;
        $ui->assign('nxt',$nxt);
        $ui->display('add-ps.tpl');



        break;


    case 's-new':


        $ui->assign('type','Service');
        $ui->assign('xfooter', Asset::js(array('numeric','jslib/add-ps')));

        $ui->assign('xjq', '
 $(\'.amount\').autoNumeric(\'init\');
 ');

        $max = ORM::for_table('sys_items')->max('id');
        $nxt = $max+1;
        $ui->assign('nxt',$nxt);
        $ui->display('add-ps.tpl');



        break;


    case 'add-post':
        $name = _post('name');
        $sales_price = _post('sales_price');
        $sales_price = Finance::amount_fix($sales_price);
        $item_number = _post('item_number');
        $description = _post('description');
        $location = _post('location');
        $type = _post('type');

        $msg = '';

        if($name == ''){
            $msg .= 'Item Name is required <br>';
        }
       if(!is_numeric($sales_price)){
           $sales_price = '0.00';
       }


        $image_error = '';
        $image = ps_upload_image($image_error);
        if ($image === false) $msg .= $image_error . '<br>';

        if($msg == ''){


            $d = ORM::for_table('sys_items')->create();
            $d->name = $name;
            $d->sales_price = $sales_price;
            $d->item_number = $item_number;
            $d->description = $description;
            $d->location = $location;
            $d->image = $image;
            $d->type = $type;
//others
            $d->unit = '';
            $d->e = '';
            $d->save();

            _msglog('s',$_L['Item Added Successfully']);

            echo $d->id();
        }
        else{
            echo $msg;
        }
        break;


    case 'view':
//        $id  = $routes['2'];
//        $d = ORM::for_table('sys_items')->find_one($id);
//        if($d){
//
//            //find all activity for this user
//            $ac = ORM::for_table('sys_activity')->where('cid',$id)->limit(20)->order_by_desc('id')->find_many();
//            $ui->assign('ac',$ac);
//            $ui->assign('countries',Countries::all($d['country']));
//
//            $ui->assign('xheader', '
//<link rel="stylesheet" type="text/css" href="' . $_theme . '/lib/select2/select2.css"/>
//
//');
//            $ui->assign('xfooter', '
//<script type="text/javascript" src="' . $_theme . '/lib/select2/select2.min.js"></script>
//<script type="text/javascript" src="' . $_theme . '/lib/profile.js"></script>
//
//');
//
//            $ui->assign('xjq', '
// $("#country").select2();
//
// ');
//            $ui->assign('d',$d);
//            $ui->display('ps-view.tpl');
//
//        }
//        else{
//         //   r2(U . 'customers/list', 'e', $_L['Account_Not_Found']);
//
//        }

        break;




    case 'p-list':
        $paginator = Paginator::bootstrap('sys_items','type','Product');
        $d = ORM::for_table('sys_items')->where('type','Product')->offset($paginator['startpoint'])->limit($paginator['limit'])->order_by_desc('id')->find_many();
        $ui->assign('d',$d);
        $ui->assign('type','Product');
        $ui->assign('paginator',$paginator);
        $ui->assign('xheader', '
<link rel="stylesheet" type="text/css" href="' . $_theme . '/css/modal.css"/>

');
        $ui->assign('xfooter', '
        <script type="text/javascript" src="' . $_theme . '/lib/modal.js"></script>
<script type="text/javascript" src="' . $_theme . '/lib/ps-list.js"></script>

');
        $ui->display('ps-list.tpl');
        break;

    case 's-list':

        $paginator = Paginator::bootstrap('sys_items','type','Service');
        $d = ORM::for_table('sys_items')->where('type','Service')->offset($paginator['startpoint'])->limit($paginator['limit'])->order_by_desc('id')->find_many();
        $ui->assign('d',$d);
        $ui->assign('type','Service');
        $ui->assign('paginator',$paginator);
        $ui->assign('xheader', '
<link rel="stylesheet" type="text/css" href="' . $_theme . '/css/modal.css"/>

');
        $ui->assign('xfooter', '
                <script type="text/javascript" src="' . $_theme . '/lib/modal.js"></script>
<script type="text/javascript" src="' . $_theme . '/lib/ps-list.js"></script>
');
        $ui->display('ps-list.tpl');
        break;


    case 'edit-post':
        $msg = '';
        $id = _post('id');
        $price = _post('price');
        $price = Finance::amount_fix($price);
        $name = _post('name');
        $item_number = _post('item_number');
        $description = _post('description');
        $location = _post('location');
        if($name == ''){
            $msg .= 'Name is Required <br>';
        }
        if(!is_numeric($price)){
            $msg .= 'Invalid Sales Price <br>';
        }


        $image_error = '';
        $new_image = ps_upload_image($image_error);
        if ($new_image === false) $msg .= $image_error . '<br>';

        if($msg == ''){
            $d = ORM::for_table('sys_items')->find_one($id);
            if($d){
                $old_image = $d['image'];
                $d->name = $name;
                $d->item_number = $item_number;
                $d->sales_price = $price;
                $d->description = $description;
                $d->location = $location;
                if ($new_image !== '') $d->image = $new_image;
                $d->save();
                if ($new_image !== '' && $old_image !== '' && basename($old_image) === $old_image) {
                    $old_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . $old_image;
                    if (is_file($old_path)) @unlink($old_path);
                }
                echo $d->id();
            }
            else{
                echo 'Not Found';
            }


        }
        else{
            echo $msg;
        }


        break;
    case 'delete':
        $id = $routes['2'];
        if($_app_stage == 'Demo'){
            r2(U . 'accounts/list', 'e', 'Sorry! Deleting Account is disabled in the demo mode.');
        }
        $d = ORM::for_table('sys_accounts')->find_one($id);
        if($d){
            $d->delete();
            r2(U . 'accounts/list', 's', $_L['account_delete_successful']);
        }

        break;

    case 'edit-form':

        $id = $routes['2'];
        $d = ORM::for_table('sys_items')->find_one($id);
        if($d){
            $price = number_format(($d['sales_price']),2,$config['dec_point'],$config['thousands_sep']);
            $safe_name = htmlspecialchars($d['name'], ENT_QUOTES, 'UTF-8');
            $safe_number = htmlspecialchars($d['item_number'], ENT_QUOTES, 'UTF-8');
            $safe_description = htmlspecialchars($d['description'], ENT_QUOTES, 'UTF-8');
            $safe_location = htmlspecialchars($d['location'], ENT_QUOTES, 'UTF-8');
            echo '
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h3>'.$_L['Edit'].'</h3>
</div>
<div class="modal-body">

<form class="form-horizontal" role="form" id="edit_form" method="post" enctype="multipart/form-data">
  <div class="form-group">
    <label for="name" class="col-sm-2 control-label">'.$_L['Name'].'</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" value="'.$safe_name.'" name="name" id="name">
    </div>
  </div>
  <div class="form-group">
    <label for="rate" class="col-sm-2 control-label">'.$_L['Item Number'].'</label>
    <div class="col-sm-2">
      <input type="text" class="form-control" name="item_number" value="'.$safe_number.'" id="item_number">
      <input type="hidden" name="id" value="'.$d['id'].'">
    </div>
  </div>
  <div class="form-group">
    <label for="rate" class="col-sm-2 control-label">'.$_L['Price'].'</label>
    <div class="col-sm-2">
      <input type="text" class="form-control" name="price" value="'.$price.'" id="price">
      <input type="hidden" name="id" value="'.$d['id'].'">
    </div>
  </div>
    <div class="form-group">
    <label for="name" class="col-sm-2 control-label">'.$_L['Description'].'</label>
    <div class="col-sm-10">
      <textarea id="description" name="description" class="form-control" rows="3">'.$safe_description.'</textarea>
    </div>
  </div>
  <div class="form-group">
    <label for="location" class="col-sm-2 control-label">Localização</label>
    <div class="col-sm-10"><input type="text" class="form-control" name="location" value="'.$safe_location.'" id="location"></div>
  </div>
  <div class="form-group">
    <label for="image" class="col-sm-2 control-label">Imagem</label>
    <div class="col-sm-10">';
            if ($d['image'] !== '') {
                echo '<p><img src="'.APP_URL.'/application/storage/products/'.rawurlencode($d['image']).'" alt="Imagem do produto" style="max-width:140px;max-height:100px;border:1px solid #ddd;padding:3px;"></p>';
            }
            echo '<input type="file" class="form-control" name="image" id="image" accept="image/jpeg,image/png,image/gif,image/webp"><small class="text-muted">Deixe em branco para manter a imagem atual. Máximo: 5 MB.</small></div>
  </div>
</form>

</div>
<div class="modal-footer">

	<button type="button" data-dismiss="modal" class="btn">'.$_L['Close'].'</button>
	<button id="update" class="btn btn-primary">'.$_L['Update'].'</button>
</div>';
        }
        else{
            echo 'not found';
        }



        break;



    case 'post':

        break;

    default:
        echo 'action not defined';
}
