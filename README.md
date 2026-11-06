# API RESTful PHP - Usuários

API RESTful simples para gerenciamento de usuários, construída em PHP com SQLite
e preparada para deploy na [Vercel](https://vercel.com) usando o runtime `vercel-php`.

## Funcionalidades

- Listar todos os usuários (`GET`)
- Criar um novo usuário (`POST`)
- Atualizar um usuário existente (`PUT`)
- Remover um usuário (`DELETE`)
- Documentação interativa via Swagger UI (`/docs`)
- Especificação OpenAPI 3.0 (`/openapi.json`)

## Estrutura do projeto

```
api-restful-php/
├── api/
│   ├── index.php        # Ponto de entrada da API + roteamento + handlers
│   └── openapi.json     # Especificação OpenAPI 3.0
├── package.json         # Engines (Node 18.x) para a Vercel
├── vercel.json          # Configuração de rotas e runtime PHP
├── .gitignore
└── README.md
```

## Requisitos

- PHP 8.x com extensão SQLite3 habilitada
- (Opcional) Vercel CLI para deploy

## Executando localmente

```bash
php -S 127.0.0.1:8000 api/index.php
```

A API ficará disponível em `http://127.0.0.1:8000`.

## Endpoints

Base URL local: `http://127.0.0.1:8000`

### `GET /usuarios`

Lista todos os usuários cadastrados.

**Resposta (200):**

```json
[
  { "id": 1, "name": "João Silva", "email": "joao@example.com" }
]
```

### `POST /usuarios`

Cria um novo usuário. Corpo em `application/json`.

**Body:**

```json
{
  "name": "João Silva",
  "email": "joao@example.com"
}
```

**Resposta (200):**

```json
{ "success": "Usuário criado com sucesso" }
```

Erros possíveis: `{ "error": "Dados incompletos" }`.

### `PUT /usuarios`

Atualiza um usuário existente. Corpo em `application/x-www-form-urlencoded`.

**Body:**

```
id=1&name=João Silva&email=joao@example.com
```

**Resposta (200):**

```json
{ "success": "Usuário atualizado com sucesso" }
```

Erros possíveis: `{ "error": "Dados incompletos" }`.

### `DELETE /usuarios`

Remove um usuário pelo `id`. Corpo em `application/x-www-form-urlencoded`.

**Body:**

```
id=1
```

**Resposta (200):**

```json
{ "success": "Usuário deletado com sucesso" }
```

Erros possíveis: `{ "error": "ID não fornecido" }`.

## CORS

A API envia cabeçalhos CORS permissivos (`Access-Control-Allow-Origin: *`) e responde
a requisições `OPTIONS` (preflight) com `204 No Content`, permitindo acesso de
qualquer origem.

## Documentação interativa


Com o servidor rodando, acesse:

- Swagger UI: `http://127.0.0.1:8000/docs`
- OpenAPI JSON: `http://127.0.0.1:8000/openapi.json`

Em produção (Vercel), substitua o host pela URL do deploy.

## Exemplos com cURL

```bash
# Listar usuários
curl http://127.0.0.1:8000/usuarios

# Criar usuário
curl -X POST http://127.0.0.1:8000/usuarios \
  -H "Content-Type: application/json" \
  -d '{"name":"João Silva","email":"joao@example.com"}'

# Atualizar usuário
curl -X PUT http://127.0.0.1:8000/usuarios \
  -d "id=1&name=João Silva&email=joao@example.com"

# Deletar usuário
curl -X DELETE http://127.0.0.1:8000/usuarios \
  -d "id=1"
```

## Deploy na Vercel

1. Instale a Vercel CLI: `npm i -g vercel`
2. Execute `vercel` na raiz do projeto e siga as instruções.
3. A Vercel usará `vercel.json` para rotear todas as requisições para `api/index.php`.

> **Observação:** o banco SQLite é criado em `/tmp/database.db`. No ambiente serverless da Vercel,
> o sistema de arquivos é efêmero, portanto os dados não persistem entre invocações/cold starts.
> Para uso em produção, considere um banco de dados externo.

## Licença

Este projeto não possui uma licença explícita. Consulte o autor para mais informações.
