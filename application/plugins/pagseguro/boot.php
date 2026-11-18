<?php
Event::bind('settings/pg_conf/label',function(){

    $arg_list = func_get_args();

    if(($arg_list[0][0]) == 'pagseguro'){

        global $ui;

        $label = array();

        $label['value'] = 'Token Produção';
        $label['c1'] = 'Token Sandbox';
        $label['c2'] = 'E-mail da Conta';
        $label['c3'] = 'Conta de Recebimento';
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

        $crm_fields_cpf = ORM::for_table('crm_customfields')->where('fieldname','cpf')->find_one();

        if(!$crm_fields_cpf){

            $crm_fields  = ORM::for_table('crm_customfields')->create();

            $crm_fields->ctype = 'crm';
            $crm_fields->relid = '0';
            $crm_fields->fieldname = 'CPF';
            $crm_fields->fieldtype = 'text';
            $crm_fields->showinvoice = 'Yes';
            $crm_fields->sorder = '0';
            $crm_fields->save();

        }



});

Event::bind('client/ipay/pg',function(){

    $arg_list = func_get_args();

    if(($arg_list[0][0]) == 'pagseguro'){

        $id = $arg_list[0][1];
        $token = $arg_list[0][2];

        r2(U."pagseguro/payments/pay/$id/$token/");


    }


});