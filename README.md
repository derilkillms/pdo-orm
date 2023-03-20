# pdo-orm

## Instalation
```
composer require derilkillms/pdo-orm
```

### Information

**About ORM : ([wikipedia](https://en.wikipedia.org/wiki/Object%E2%80%93relational_mapping))**
Object–relational mapping (ORM, O/RM, and O/R mapping tool) in computer science is a programming technique for converting data between a relational database and the heap of an object-oriented programming language. This creates, in effect, a virtual object database that can be used from within the programming language.

....

**This Repository Based : PHP**
```php
unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbdriver  = 'pdo';
$CFG->dbtype    = 'mysql'; //mysql or pgsql
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'YOUR_DB_NAME';
$CFG->dbuser    = 'root';
$CFG->dbpass    = '';
$CFG->prefix    = 'd_';

require_once(__DIR__ .'/vendor/autoload.php');
$DB = new Database();
```
