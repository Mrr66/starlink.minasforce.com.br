<?php
require_once __DIR__ . '/Authorization.php';

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Exception\RequestException;

class WhatsApp
{
    private $urlBase = 'https://api.notifique-me.com';
    private $clienteId = '';
    private $usuario = '';
    private $senha = '';
    private $token = null;
    private $client;

    public function __construct()
    {
        // As variaveis de ambiente permitem trocar as credenciais sem editar o sistema.
        $this->urlBase = getenv('NOTIFIQUE_ME_URL') ?: $this->urlBase;
        $this->clienteId = getenv('NOTIFIQUE_ME_CLIENTE_ID') ?: $this->clienteId;
        $this->usuario = getenv('NOTIFIQUE_ME_USUARIO') ?: $this->usuario;
        $this->senha = getenv('NOTIFIQUE_ME_SENHA') ?: $this->senha;

        if ($this->clienteId === '' || $this->usuario === '' || $this->senha === '') {
            throw new RuntimeException('Configure NOTIFIQUE_ME_CLIENTE_ID, NOTIFIQUE_ME_USUARIO e NOTIFIQUE_ME_SENHA.');
        }

        $handler = HandlerStack::create();
        $this->client = new Client(array('handler' => $handler));
    }

    public function Send($msg, $numero, $titulo = 'Fatura: ', $type = 0, $status = 0)
    {
        $numero = preg_replace('/\D+/', '', (string) $numero);
        if ($numero === '') {
            throw new InvalidArgumentException('Numero de WhatsApp invalido.');
        }

        // A API anterior interpretava "\n" no texto. A Notifique-me espera
        // caracteres reais de quebra de linha no JSON.
        $texto = (string) $titulo . (string) $msg;
        $texto = str_replace(array('\\r\\n', '\\n'), array("\r\n", "\n"), $texto);

        $payload = array(
            'Msg' => $texto,
            'ClienteId' => $this->clienteId,
            'Type' => (int) $type,
            'Numero' => (int) $numero,
            'Status' => (int) $status
        );

        return $this->sendRequest($payload, true);
    }

    private function sendRequest($payload, $retry)
    {
        if (!$this->token) {
            $this->token = $this->Token($this->usuario, $this->senha);
        }

        if (!$this->token) {
            throw new RuntimeException('Nao foi possivel autenticar na API Notifique-me.');
        }

        try {
            $response = $this->client->post(rtrim($this->urlBase, '/') . '/api/Notificacao/v1', array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode($payload)
            ));

            return (string) $response->getBody();
        } catch (RequestException $e) {
            $response = $e->getResponse();
            if ($retry && $response && $response->getStatusCode() == 401) {
                $this->token = null;
                return $this->sendRequest($payload, false);
            }

            error_log('Erro ao enviar WhatsApp pela API Notifique-me: ' . $e->getMessage());
            throw $e;
        }
    }

    public function authorization($usuario = null, $senha = null)
    {
        $auth = new Authorization($usuario ?: $this->usuario, $senha ?: $this->senha, $this->urlBase);
        return $auth->getToken();
    }

    public function Headers($usuario = null, $senha = null)
    {
        $auth = new Authorization($usuario ?: $this->usuario, $senha ?: $this->senha, $this->urlBase);
        return $auth->getHeaders();
    }

    public function Token($usuario = null, $senha = null)
    {
        $auth = new Authorization($usuario ?: $this->usuario, $senha ?: $this->senha, $this->urlBase);
        return $auth->Token();
    }
}
