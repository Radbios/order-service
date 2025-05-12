<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

---

# Order Service - Sistema de E-commerce com Microsserviços

O order-service é responsável por registrar e gerenciar os pedidos dos usuários no sistema de e-commerce distribuído. Ele permite criar novos pedidos, consultar o histórico de compras, atualizar o status de um pedido e realizar análises sobre categorias mais compradas.

## Funcionalidades

* Registro de pedidos com múltiplos produtos
* Consulta de pedidos do usuário autenticado
* Atualização do status de pedidos para "pago"
* Identificação da categoria mais comprada por um usuário ou no sistema
* Consulta de detalhes de pedidos por ID

## Integração com outros serviços

Este microsserviço interage com:

| Serviço         | Finalidade                                                                 |
| --------------- | -------------------------------------------------------------------------- |
| catalog-service | Consulta de preço e categoria dos produtos no momento da criação do pedido |
| payment-service | Atualiza o status do pedido após pagamento                                 |
| gateway         | Todas as requisições externas passam por ele (`APP_GATEWAY`)               |

## Rotas disponíveis (protegidas com autenticação)

### GET `/api/service/order`

Retorna todos os pedidos do usuário autenticado.

### POST `/api/service/order`

Cria um novo pedido com base nos produtos enviados.

**Corpo da requisição:**

```json
{
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 3, "quantity": 1 }
  ],
  "shipping_cost": 10.0,
  "shipping_service": "Sedex",
  "payment_type": "pix"
}
```

**Resposta de sucesso:**

```json
{
  "order": {
    "id": 5,
    "total": 150.0,
    "status": "pending",
    "items": [ ... ]
  }
}
```

### GET `/api/service/order/{order}`

Consulta os detalhes de um pedido específico.

### PUT `/api/service/order/{order}`

Atualiza o status do pedido para "paid".

### GET `/api/service/order/my-top-category`

Retorna a categoria de produto mais comprada pelo usuário autenticado.

### GET `/api/service/order/top-category`

Retorna a categoria mais comprada entre todos os usuários.

## Estrutura principal

| Arquivo               | Descrição                                                 |
| --------------------- | --------------------------------------------------------- |
| `OrderController.php` | Controlador com todos os endpoints relacionados a pedidos |
| `Order.php`           | Modelo Eloquent do pedido, com relação aos itens          |
| `OrderItem.php`       | Modelo dos itens do pedido (produto, quantidade, preço)   |
| `routes/api.php`      | Define as rotas protegidas para operações de pedido       |

## Requisitos

* Laravel 11
* PHP 8.2+
* API Gateway configurado
* Token JWT válido para autenticação

## Observações

Durante a criação de pedidos, o serviço consulta o `catalog-service` para garantir a integridade dos dados (preço e categoria dos produtos). Após o pagamento, o `payment-service` atualiza o status para "paid" e o carrinho é limpo. Esse serviço é um elo crítico no fluxo de compras da aplicação.

---
