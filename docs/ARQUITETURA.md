# Arquitetura do aplicativo

O projeto segue a estrutura padrão do Laravel, organizada por responsabilidade:

- `app/Http/Controllers`: recebe requisições e coordena respostas HTTP.
- `app/Http/Requests`: validação específica dos formulários.
- `app/Models`: entidades Eloquent e seus relacionamentos.
- `app/Services/Boleto`: regras do domínio de boletos que não pertencem ao HTTP ou ao banco diretamente.
- `resources/views`: apresentação Blade, separada por contexto (`auth`, `boletos`, `profile` e `layouts`).
- `routes`: definição dos endpoints web e de autenticação.
- `database`: migrations, factories e seeders.
- `tests`: testes de unidade e de funcionalidade.

## Serviços de boletos

- `BoletoCodigoService`: normaliza valores, converte linhas digitáveis e gera códigos de barras.
- `BeneficiarioService`: consulta, sugere e persiste beneficiários identificados.

`BoletoController` continua expondo os mesmos métodos e as mesmas rotas públicas. Ele apenas delega regras especializadas aos serviços, preservando os fluxos existentes.
