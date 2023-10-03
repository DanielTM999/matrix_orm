# Documentação do ORM (Object-Relational Mapping)

Este ORM foi desenvolvido para facilitar o mapeamento de objetos para tabelas de banco de dados em PHP. Ele fornece uma estrutura básica para a realização de operações CRUD (Create, Read, Update, Delete) em seu banco de dados.

## Pré-requisitos

Certifique-se de que você tenha as seguintes dependências instaladas e configuradas:

1. **PHP**: Certifique-se de ter o PHP instalado em seu ambiente de desenvolvimento. Este ORM foi desenvolvido em PHP.

2. **Banco de Dados**: Este ORM usa .env como ambiente para pegar os dados de coneção por tanto deve-se criar um .env na
raiz do projeto onde foi definido geralmente /src

```.env

    HOST = localhost
    USER = root
    PASSWORD =
    DATABASE = seuBanco
    POST = 3306 #opicional caso esteza rodando em outra porta
    DIALECT = mysql #opicional caso esteza rodando em outro banco de dados

```

## Uso Básico

Para começar a usar este ORM, siga estas etapas:

### 1. Estenda a Classe `DbManager`

```php
namespace SeuNamespace;

use matrixOrm\DbManager;

    /**
     * @teble
     */
class SuaClasseModel extends DbManager
{
    // Defina suas propriedades e métodos aqui
        /**
         * @var varchar
         * @notnull
         * @unique
         */
        private $nome;
        /**
         * @var int
         */
        private $idade;
        /**
         * @var identity
         */
        private $id;

        function __construct($nome = "", $idade = "")
        {
            $this->nome = $nome;
            $this->idade = $idade;
        }
}

```
## 1.1 configuraçoes de tipagem
    @var varchar
    @var json
    @var blob
    @var int
    @var uuid
    @var float
    @var date
    @var datetime
    @var enum
    @var bit
    @var time
    @notnull
    @unique
## 1.2 configuraçoes de mapeamento
    Mapeia todas as classes que possuirem o em cima dela
    /**
     * @teble
     */

2. **Inicialize o Carregador de Classes**

Em seu código principal, você deve inicializar o carregador de classes `DbLoader`. Certifique-se de incluir o arquivo `DbLoader.php` que faz parte deste ORM.

```php
use matrixOrm\DbLoader;
include "./src/MappingQuerys/DbLoader.php";

DbLoader::autoloader();
DbLoader::init();
```

## Usando os Métodos da Classe `DbManager`

Agora que você estendeu a classe `DbManager` e inicializou o carregador de classes, você pode usar os métodos fornecidos por esta classe para interagir com o banco de dados. Alguns dos principais métodos disponíveis são:

- `findAll($withJoin = true(opicional))`: Recupera todos os registros da tabela associada à sua classe modelo. Use `$withJoin` para incluir ou excluir junções com outras tabelas.

- `findById($id, $withJoin = true(opicional))`: Recupera um registro pelo ID. Use `$withJoin` para incluir ou excluir junções com outras tabelas.

- Métodos mágicos como `findByPropertyName($value, $withJoin = true(opicional))`: Esses métodos permitem que você pesquise registros com base em propriedades específicas da classe modelo.

- `save(DbManager $entity)`: Salva um objeto na tabela associada. Certifique-se de passar um objeto da mesma classe modelo como argumento.

- `Create()`: Cria a tabela no banco de dados com base na estrutura da classe modelo. esse o proprio ORM ja vai automaticamente iniciar.

- `findBy(atrubuto Da sua Classe)($withJoin = true(opicional))`: Recupera todos os registros da tabela associada à sua classe modelo usando a condição do seu atrubuto. Use `$withJoin` para incluir ou excluir junções com outras tabelas.
### Exemplos de Uso

Aqui estão alguns exemplos de como usar este ORM:

#### Recuperar todos os registros

```php
$seuModel = new SuaClasseModel();
$resultados = $seuModel->findAll();
```

```php
$seuModel = new SuaClasseModel();
$resultados = $seuModel->findId(0);
```

```php
$seuModel = new SuaClasseModel();
//caso tenha $nome na sua variavel
$resultados = $seuModel->findByNome("nome");
```
