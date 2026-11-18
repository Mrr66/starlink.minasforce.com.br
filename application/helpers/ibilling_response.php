<?php
class Response{
    public $Status;
    public $Code;
    public $Message;

    public function __construct($status, $code, $msg){
        $this->Status = $status;
        $this->Code = $code;
        $this->Message = $msg;
    }

    public function get(){
        return $this;
    }
}