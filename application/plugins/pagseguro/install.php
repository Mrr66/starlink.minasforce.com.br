<?php

$d = ORM::for_table('sys_pg')->where('processor','pagseguro')->find_one();

if(!$d){

    $c = ORM::for_table('sys_pg')->create();
    $c->name = 'pagseguro';
    $c->settings = 'Token da Conta';
    $c->value = '';
    $c->processor = 'pagseguro';
    $c->ins = '';
    $c->c1 = '';
    $c->c2 = '';
    $c->c3 = '';
    $c->c4 = '';
    $c->c5 = '';
    $c->status = 'Active';
    $c->sorder = 1;
    $c->logo = 'apps/pagseguro/views/imgs/pagseguro-small.png';
    $c->mode = 'Dev';
    $c->save();

}

$p = ORM::for_table('sys_pmethods')->where('name','pagseguro')->find_one();

if(!$p){
    $p->name = 'pagseguro';
    $p->sorder = 1;
    $p->save();
}