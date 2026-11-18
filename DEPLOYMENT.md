# Publicacao pelo GitHub Actions

O workflow `.github/workflows/deploy.yml` valida a sintaxe dos arquivos PHP e,
se a validacao terminar com sucesso, sincroniza a branch `main` com o servidor.

Cadastre os seguintes secrets no environment `production` do repositorio:

- `DEPLOY_HOST`: dominio ou IP SSH do servidor.
- `DEPLOY_USER`: usuario SSH, por exemplo `integrate`.
- `DEPLOY_PORT`: porta SSH, normalmente `22`.
- `DEPLOY_PATH`: caminho absoluto da aplicacao, por exemplo
  `/usr/share/nginx/pagamentosuper/starlink.minasforce.com.br`.
- `SSH_PRIVATE_KEY`: chave SSH privada exclusiva para o GitHub Actions.
- `SSH_KNOWN_HOSTS`: chave publica do host retornada por `ssh-keyscan`.

Arquivos locais e dados persistentes, incluindo `application/config.php`,
certificados, imagens enviadas e temporarios, nao sao alterados pelo deploy.

Todo push para `main` inicia uma publicacao. Tambem e possivel executar o
workflow manualmente na aba Actions do GitHub.
