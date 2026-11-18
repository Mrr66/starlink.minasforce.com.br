<?php
Event::bind('settings/pg_conf/label',function(){

    $arg_list = func_get_args();

    if(($arg_list[0][0]) == 'pagseguro'){

        global $ui;

        $label = array();

        $label['value'] = 'Token da Conta';
        $label['c1'] = 'E-mail da Conta';
        $label['c2'] = 'Application Secret';
        $label['c3'] = '';
        $label['c4'] = '';
        $label['c5'] = '';
        $label['mode'] = true;

        $ui->assign('label',$label);

        $help_txt = array();

        $help_txt['value'] = '';
        $help_txt['c1'] = '';
        $help_txt['c2'] = '';
        $help_txt['c3'] = '';
        $help_txt['c4'] = '';
        $help_txt['c5'] = '';
        $help_txt['mode'] = '';

        $ui->assign('help_txt',$help_txt);


    }


});

Event::bind('client/ipay/pg',function(){

    $arg_list = func_get_args();

    if(($arg_list[0][0]) == 'pagseguro'){

        $id = $arg_list[0][1];
        $token = $arg_list[0][2];

        r2(U."pagseguro/payments/make/$id/$token/");


    }


});