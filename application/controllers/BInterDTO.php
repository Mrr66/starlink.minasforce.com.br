<?php
class BInterDTO{
    public $pagador;
    public $dataEmissao;
    public $seuNumero;
    public $dataLimite = "TRINTA";
    public $dataVencimento;
    public $mensagem;
    public $desconto1;
    public $desconto2;
    public $desconto3;
    public $valorNominal;
    public $valorAbatimento = 0;
    public $multa;
    public $mora;
    public $cnpjCPFBeneficiario = "38382317000102";
    public $numDiasAgenda = "TRINTA";

    function __construct($pagador, $mensagem = null, $desconto1 = null, $desconto2 = null, $desconto3 = null, $multa = null, $mora = null){
        $this->pagador = $pagador;
        $this->mensagem = $mensagem;
        $this->desconto1 = $desconto1;
        $this->desconto2 = $desconto2;
        $this->desconto3 = $desconto3;
        $this->multa = $multa;
        $this->mora = $mora;
    }
}

class Pagador{
    public $cnpjCpf;
    public $nome;
    public $email;
    public $telefone;
    public $cep;
    public $numero;
    public $complemento;
    public $bairro;
    public $cidade;
    public $uf;
    public $endereco;
    public $ddd;
    public $tipoPessoa;
}

class Mensagem {
    function __construct(){
        $this->linha2 = "";
        $this->linha3 = "";
        $this->linha4 = "";
        $this->linha5 = "";
    }
    public $linha1;
    public $linha2;
    public $linha3;
    public $linha4;
    public $linha5;
}

class Desconto {
    public $codigoDesconto;
    public $taxa;
    public $valor;
    public $data;
}

class Multa {
    public $codigoMulta;
    public $valor;
    public $taxa;
}

class Mora {
    public $codigoMora;
    public $valor;
    public $taxa;
}