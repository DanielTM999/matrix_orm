# Documentação do ORM (Object-Relational Mapping)

Este ORM foi desenvolvido para facilitar o mapeamento de objetos para tabelas de banco de dados em PHP. Ele fornece uma estrutura básica para a realização de operações CRUD (Create, Read, Update, Delete) em seu banco de dados.

## Pré-requisitos

Certifique-se de que você tenha as seguintes dependências instaladas e configuradas:

1. **PHP**: Certifique-se de ter o PHP instalado em seu ambiente de desenvolvimento. Este ORM foi desenvolvido em PHP.

2. **Banco de Dados**: Este ORM assume que você já configurou sua conexão com o banco de dados. Certifique-se de ter as informações de conexão corretas definidas em algum lugar de sua aplicação.

## Uso Básico

Para começar a usar este ORM, siga estas etapas:

### 1. Estenda a Classe `DbManager`

```php
namespace SeuNamespace;

use matrixOrm\DbManager;

class SuaClasseModel extends DbManager
{
    // Defina suas propriedades e métodos aqui
}

```

2. **Inicialize o Carregador de Classes**

Em seu código principal, você deve inicializar o carregador de classes `DbLoader`. Certifique-se de incluir o arquivo `DbLoader.php` que faz parte deste ORM.

```php
use matrixOrm\DbLoader;
include "./src/MappingQuerys/DbLoader.php";

DbLoader::autoloader();
DbLoader::init();
```
