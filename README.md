
# Plataforma Geração Rica

Plataforma de venda e entrega de produtos digitais, construída para comercializar cursos e e-books. O cliente adiciona produtos ao carrinho, escolhe o país e o meio de pagamento, e recebe acesso ao conteúdo depois da confirmação do pagamento.

O projeto suporta pagamentos automáticos por Stripe e PayPal, além de pagamento manual por transferência bancária ou Multicaixa Express, com envio e revisão de comprovativo pelo painel administrativo.

## Funcionalidades

- Catálogo de produtos dos tipos `course` e `ebook`.
- Produtos ativos/inativos, URL amigável por slug e eliminação lógica.
- Carrinho e checkout com criação de pedidos.
- Seleção de país e conversão do preço base em AOA para moedas suportadas.
- Checkout automático com Stripe e PayPal.
- Pagamento manual com upload de comprovativo.
- Webhooks de Stripe e PayPal para confirmação assíncrona.
- Painel administrativo protegido por autenticação e pela role `admin`.
- Gestão de produtos no painel.
- Fila de pagamentos manuais e revisão de comprovativos.
- Links de acesso aos conteúdos associados aos produtos comprados.
- Autenticação Jetstream, verificação de e-mail, 2FA, sessões e tokens Sanctum.
- Notificações por e-mail e processamento assíncrono através da fila Laravel.

## Stack

- PHP `^8.2`
- Laravel `^12.0`
- Livewire `^3.6`
- Laravel Jetstream `^5.5`
- Laravel Sanctum `^4.0`
- Stripe PHP SDK `^21.3`
- Vite `^7.0`
- Tailwind CSS `^3.4`
- PHPUnit `^11.5`
- MySQL por padrão, com sessões, cache e fila persistidos na base de dados

## Requisitos

- PHP 8.2 ou superior, com as extensões exigidas pelo Laravel.
- Composer.
- Node.js e npm.
- MySQL ou outro driver suportado pelo Laravel, configurado no `.env`.
- Credenciais de Stripe, PayPal e ExchangeRate-API quando os respetivos fluxos forem usados.

## Instalação

Clone o projeto e entre na pasta:

```bash
git clone <url-do-repositorio>
cd plataforma-geracao-rica
```

O script de setup existente instala as dependências PHP e JavaScript, cria o `.env`, gera a chave, executa as migrações e compila os assets:

```bash
composer run setup
```

Se preferir executar cada etapa separadamente:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

No Windows, a cópia do ambiente pode ser feita manualmente:

```powershell
Copy-Item .env.example .env
```

Antes de executar as migrações, configure pelo menos `DB_*` no `.env`. O `DatabaseSeeder` cria um utilizador de teste (`test@example.com`) e executa o `CountrySeeder`; ele não cria automaticamente um utilizador administrador nem produtos.

## Configuração do ambiente

As variáveis abaixo são lidas diretamente pela aplicação:

| Variável | Finalidade |
| --- | --- |
| `APP_NAME`, `APP_URL`, `APP_ENV`, `APP_DEBUG` | Identidade e ambiente da aplicação |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Ligação à base de dados |
| `SESSION_DRIVER` | Driver das sessões; o padrão do projeto é `database` |
| `CACHE_STORE` | Driver do cache; o padrão do projeto é `database` |
| `QUEUE_CONNECTION` | Driver da fila; o padrão do projeto é `database` |
| `MAIL_*`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | Envio e remetente de e-mails |
| `STRIPE_KEY`, `STRIPE_SECRET` | Credenciais da API Stripe |
| `STRIPE_WEBHOOK_SECRET` | Validação do webhook Stripe |
| `PAYPAL_MODE` | `sandbox` ou `live`; o padrão é `sandbox` |
| `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET` | Credenciais da API PayPal |
| `PAYPAL_WEBHOOK_ID` | Identificador do webhook PayPal |
| `PAYMENT_BANK_NAME` | Nome do banco mostrado no pagamento manual |
| `PAYMENT_ACCOUNT_HOLDER` | Titular da conta |
| `PAYMENT_IBAN`, `PAYMENT_ACCOUNT_NUMBER` | Dados da transferência |
| `PAYMENT_MCX_NUMBER` | Número do Multicaixa Express |
| `EXCHANGERATE_API_KEY` | Chave do fornecedor de taxas de câmbio |
| `EXCHANGERATE_API_BASE_URL` | URL base opcional do fornecedor de câmbio |
| `CURRENCY_FALLBACK` | Moeda alternativa quando a moeda do país não é suportada; padrão `USD` |

O `.env.example` usa `MAIL_MAILER=log`, adequado para desenvolvimento. Em produção, configure um transportador de e-mail real e não ative `APP_DEBUG`.

## Desenvolvimento

Para iniciar o servidor Laravel, a fila e o Vite em conjunto:

```bash
composer run dev
```

Esse comando executa:

- `php artisan serve`
- `php artisan queue:listen --tries=1`
- `npm run dev`

Para iniciar os processos individualmente:

```bash
php artisan serve
php artisan queue:work
npm run dev
```

Após compilar para produção, os assets ficam prontos com:

```bash
npm run build
```

## Fluxo de compra e pagamentos

1. O cliente adiciona um produto ativo ao carrinho em `/carrinho`.
2. Em `/checkout`, informa os dados necessários, incluindo país, contacto WhatsApp e método de pagamento.
3. O preço do produto é mantido em AOA. Para clientes fora de Angola, a aplicação regista a moeda cobrada e a taxa de câmbio usada no pedido.
4. Stripe e PayPal redirecionam o cliente para o respetivo checkout. A confirmação definitiva acontece pelo webhook.
5. No pagamento manual, o cliente vê os dados configurados, envia o comprovativo e o pedido passa para `awaiting_confirmation`.
6. Um administrador revê o comprovativo no painel. Ao aprovar, o pagamento é concluído e os acessos são concedidos; ao rejeitar, o pedido e o pagamento ficam rejeitados.

### Gateways e moedas

Os gateways disponíveis são:

- `stripe`: pagamento automático por cartão/Stripe.
- `paypal`: pagamento automático via PayPal.
- `manual`: transferência bancária ou Multicaixa Express, com validação administrativa.

A moeda base de todos os produtos é `AOA`. A aplicação aceita diretamente `USD`, `EUR`, `GBP`, `BRL`, `CAD`, `AUD`, `ZAR` e `CHF`; se a moeda do país não estiver nessa lista, usa `CURRENCY_FALLBACK`. A configuração está em `config/Currency.php`.

## Estados do domínio

### Pedido

| Estado | Significado |
| --- | --- |
| `pending` | Pedido criado, ainda sem confirmação de pagamento |
| `awaiting_confirmation` | Comprovativo manual aguardando revisão |
| `paid` | Pagamento confirmado e acesso concedido |
| `rejected` | Pagamento/pedido rejeitado |
| `cancelled` | Pedido cancelado |

### Pagamento

| Estado | Significado |
| --- | --- |
| `pending` | Pagamento iniciado ou aguardando revisão |
| `completed` | Gateway confirmou o pagamento |
| `failed` | Tentativa automática falhou |
| `rejected` | Comprovativo manual rejeitado |

## Rotas principais

| Rota | Uso |
| --- | --- |
| `/` | Página inicial |
| `/dashboard` | Dashboard autenticado |
| `/carrinho` | Carrinho do cliente |
| `/checkout` | Checkout |
| `/checkout/{order}/pagamento-manual` | Envio de pagamento manual |
| `/checkout/{order}/stripe` | Início/retorno do Stripe |
| `/checkout/{order}/paypal` | Início/retorno do PayPal |
| `/webhooks/stripe` | Webhook Stripe, sem CSRF |
| `/webhooks/paypal` | Webhook PayPal, sem CSRF |
| `/admin` | Dashboard administrativo |
| `/admin/produtos` | Gestão de produtos |
| `/admin/pagamentos` | Fila de pagamentos manuais |
| `/api/user` | Utilizador autenticado via Sanctum |

Os webhooks devem ser configurados nos painéis Stripe e PayPal apontando para a URL pública correspondente. O bootstrap da aplicação exclui apenas esses dois caminhos da proteção CSRF.

## Estrutura do projeto

```text
app/
	Actions/             Ações Jetstream/Fortify
	Enum/                Estados, gateways e tipos de produto
	Http/Controllers/    Checkout, webhooks e downloads administrativos
	Jobs/                Webhooks, e-mails e atualização de câmbio
	Livewire/            Carrinho, checkout e painel administrativo
	Mail/                Mensagens de confirmação e rejeição
	Models/              Produtos, pedidos, pagamentos e acessos
	Services/            Serviços de carrinho e regras de aplicação
config/
	Currency.php         Moeda base, fallback e taxas
	Payment.php          Dados do pagamento manual
database/
	migrations/          Esquema de utilizadores, catálogo, pedidos e pagamentos
	seeders/              Países e dados iniciais
resources/views/       Views Blade da aplicação
routes/
	shop.php             Carrinho, checkout e webhooks
	admin.php            Área administrativa
```

## Base de dados

As entidades centrais são:

- `products`: título, slug, tipo, preço base, capa e estado ativo.
- `product_access_links`: links e conteúdos entregues por produto.
- `countries` e `exchange_rates`: países e taxas utilizadas na conversão.
- `orders`: snapshot do cliente, método, moeda, taxa e totais do pedido.
- `order_items`: snapshot dos produtos comprados.
- `payments`: tentativas, transações, payloads dos webhooks e comprovativos.
- `order_access_grants`: concessão de acesso após o pagamento.
- Tabelas padrão do Laravel/Jetstream para utilizadores, sessões, cache, jobs, tokens e 2FA.

O pedido guarda tanto `total_base_aoa` como `total_charged`, permitindo auditar o preço original e o valor cobrado na moeda escolhida.

## Testes e qualidade

Executar toda a suíte:

```bash
composer run test
```

Ou diretamente:

```bash
php artisan test
```

O projeto também inclui Laravel Pint para formatação PHP:

```bash
./vendor/bin/pint
```

Os testes existentes cobrem sobretudo autenticação Jetstream/Fortify, perfil, password reset, 2FA, sessões e tokens Sanctum. Ao alterar checkout, webhooks, conversão cambial ou revisão manual, acrescente testes de integração para esses fluxos.

## Produção

Antes de publicar:

1. Use `APP_ENV=production`, `APP_DEBUG=false` e uma `APP_URL` pública com HTTPS.
2. Configure banco, e-mail, armazenamento de ficheiros e credenciais dos gateways.
3. Registe os webhooks Stripe e PayPal com URLs públicas e valide os segredos/IDs.
4. Execute `php artisan migrate --force` e `npm run build`.
5. Mantenha um worker de fila ativo para jobs, e-mails e processamento assíncrono.
6. Proteja os comprovativos enviados e os dados bancários; não coloque credenciais no código ou no repositório.
7. Configure backups da base de dados e monitorização dos logs de webhook e pagamentos.

## Licença

Este projeto utiliza o framework Laravel e as dependências indicadas em `composer.json`. A licença e os termos de distribuição específicos do produto devem ser definidos pelos responsáveis pelo projeto antes de uma distribuição pública.
In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
