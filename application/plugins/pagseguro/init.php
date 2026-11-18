<?php
$action = route(2);

$ui->assign('_application_menu', 'settings');
$ui->assign('_title', 'Pagseguro Payment Gateway'.' - '. $config['CompanyName']);
$ui->assign('_st', 'Pagseguro Payment Gateway');
$user = User::_info();
$ui->assign('user', $user);

require 'application/plugins/pagseguro/vendor/autoload.php';

switch($action){

    case 'location':

        $ui->assign('_include','location');

        $ui->assign('xfooter',$PluginManager->js('pagseguro/views/js/location'));

        $ui->display('wrapper.tpl');

        break;

    case 'get_location':


        $access_token = $_POST['access_token'];

        if($access_token == ''){
            ib_die('<hr> <p>Access token is required.</p>');
        }

        $location_api = new \pagseguroConnect\Api\LocationApi();

        try {

           $list_locations = $location_api->listLocations($access_token);

            $locations = json_decode($list_locations,true);


            $locations = $locations['locations'];

            $tbl = '
<hr>
<table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Timezone</th>

                            <th>ID</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>';

            foreach($locations as $location){




                $tbl .= '<tr>
<td>'.$location['name'].'</td>
<td>'.$location['timezone'].'</td>
<td>'.$location['id'].'</td>
<td><a href="#" class="sq_set btn btn-primary" data-locationId="'.$location['id'].'" data-accessToken="'.$access_token.'">Set</a></td>
</tr>';


            }

            $tbl .= '</tbody>
                    </table>';

//            var_dump($locations);

            echo $tbl;


        }

//catch exception
        catch(Exception $e) {
            echo '<hr>';
          //  echo 'Error Occurred: ' .$e->getMessage();
            echo 'Invalid Access Token';
        }




        break;

    case 'set_location':

        

        $access_token = $_POST['access_token'];

        $location_id = $_POST['location_id'];

        $d = ORM::for_table('sys_pg')->where('processor','pagseguro')->find_one();

        if($d){

            $d->value = $access_token;
            $d->c3 = $location_id;

            $d->save();

            echo $d->id();

        }


        break;



    default:

        echo 'No action defined';

}