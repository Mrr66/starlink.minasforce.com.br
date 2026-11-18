<?php
require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Exception\RequestException;

class Authorization
{
    public $url_base;
    public $token = null;
    public $expiration;

    private $client;
    private $usuario;
    private $senha;

    public function __construct($usuario, $senha, $url)
    {
        $this->usuario = $usuario;
        $this->senha = $senha;
        $this->url_base = rtrim($url, '/');

        $handler = HandlerStack::create();
        $this->client = new Client(array('handler' => $handler));
    }

    public function getToken()
    {
        try {
            $result = $this->client->post($this->url_base . '/api/login/v1', array(
                'query' => array(
                    'usuario' => $this->usuario,
                    'senha' => $this->senha
                ),
                'headers' => array(
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '
                ),
                'body' => ''
            ));

            return (string) $result->getBody();
        } catch (RequestException $e) {
            error_log('Erro de autenticacao na API Notifique-me: ' . $e->getMessage());
            return null;
        }
    }

    public function getHeaders()
    {
        $token = $this->Token();
        if (!$token) {
            return array();
        }

        return array('Authorization' => 'Bearer ' . $token);
    }

    public function Token()
    {
        $json = $this->getToken();
        if (!$json) {
            return null;
        }

        $result = json_decode($json);
        if (!isset($result->token)) {
            error_log('A API Notifique-me nao retornou um token valido.');
            return null;
        }

        $this->token = $result->token;
        $this->expiration = isset($result->expiration) ? $result->expiration : null;
        return $this->token;
    }
}
