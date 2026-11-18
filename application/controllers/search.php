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
$ui->assign('_application_menu', 'contacts');
$ui->assign('_st', $_L['Search']);
$ui->assign('_title', $_L['Accounts'].'- '. $config['CompanyName']);
$action = $routes['1'];
$user = User::_info();
$ui->assign('user', $user);
switch ($action) {

    case 'ps':
$type = _post('stype');
$name = _post('txtsearch');
        $d = ORM::for_table('sys_items')->where('type',$type)->where_like('name',"%$name%")->order_by_asc('name')->find_many();
if($d){
    echo '<table class="table table-hover">
        <thead>
            <tr>
                <th style="width:70px">ID</th>
                <th style="width:90px">Imagem</th>
                <th>Produto</th>
                <th>Localização</th>
                <th>Estoque</th>
                <th>Preço</th>
                <th class="text-right">Ações</th>
            </tr>
        </thead>
        <tbody>';


    foreach ($d as $ds){
        $price = number_format($ds['sales_price'],2,$config['dec_point'],$config['thousands_sep']);
        $product_name = htmlspecialchars($ds['name'], ENT_QUOTES, 'UTF-8');
        $product_number = htmlspecialchars($ds['item_number'], ENT_QUOTES, 'UTF-8');
        $product_location = htmlspecialchars($ds['location'], ENT_QUOTES, 'UTF-8');
        $product_image = '<span class="text-muted"><i class="fa fa-image"></i><br><small>Sem imagem</small></span>';
        if ($ds['image'] !== '' && basename($ds['image']) === $ds['image']) {
            $product_image = '<img src="'.APP_URL.'/application/storage/products/'.rawurlencode($ds['image']).'" alt="'.$product_name.'" style="width:60px;height:60px;object-fit:cover;border:1px solid #ddd;border-radius:3px;">';
        }
        echo ' <tr>
                <td style="vertical-align:middle"><strong>#'.$ds['id'].'</strong></td>
                <td style="width:90px;text-align:center;vertical-align:middle">'.$product_image.'</td>
                <td class="project-title">
                    <a href="#" class="cedit"  id="t'.$ds['id'].'">'.$product_name.'</a>
                </td>
                <td style="vertical-align:middle">';
        if ($product_location !== '') echo '<i class="fa fa-map-marker"></i> '.$product_location;
        else echo '<span class="text-muted">Não informada</span>';
        echo '</td>
                <td style="vertical-align:middle">'.$product_number.'</td>
                <td style="vertical-align:middle">

                   '.$price.'

                </td>

                <td class="project-actions" style="vertical-align:middle">

                    <a href="#" class="btn btn-primary btn-sm cedit" id="e'.$ds['id'].'"><i class="fa fa-pencil"></i> '.$_L['Edit'].' </a>
                    <a href="#" class="btn btn-danger btn-sm cdelete" id="pid'.$ds['id'].'"><i class="fa fa-trash"></i> '.$_L['Delete'].' </a>
                </td>
            </tr>';
    }


    echo '
        </tbody>
    </table>';
}
else{
    echo '<h4>Nothing Found</h4>';
}

        break;


    case 'users':
echo '<table class="table table-bordered table-hover trclickable">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Access Level</th>
                    <th>Active</th>
                </tr>
                </thead>
                <tbody>
                <tr id="_tr120">
                    <td>1</td>
                    <td>Mark</td>
                    <td>Otto</td>
                    <td><div class="switch">
                            <div class="onoffswitch">
                                <input type="checkbox" class="onoffswitch-checkbox" data-on-text="Yes">
                                <label class="onoffswitch-label" for="fixednavbar">
                                    <span class="onoffswitch-inner"></span>
                                    <span class="onoffswitch-switch"></span>
                                </label>
                            </div>
                        </div></td>
                </tr>

                </tbody>
            </table>';
        break;

    default:
        echo 'action not defined';
}
