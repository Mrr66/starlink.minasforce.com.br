<?php

$d = ORM::for_table('sys_pg')->where('processor','pagseguro')->find_one();

if($d){

 $d->delete();

}