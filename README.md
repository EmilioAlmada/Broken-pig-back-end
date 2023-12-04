#   Broken - Pig

##  Gestion de gastos y transferencias virtuales.

### Requerimientos para ejecutar el proyecto de forma local:

*   Tener instalado xampp, lampp o wampp 3.2 o superior pero con la version de php indicada a continuacion. 
*   Tener instalado php 7.4 u 8.0
*   Tener instalado Composer en su ultima version

### Previo a correr el proyecto de forma local:

*   Desde un cliente de base de datos conectados a la base de datos de xampp en localhost o desde phpMyAdmin en el navegador crear una basde de datos llamada "broken-pig-db"

### Comandos para correr el proyecto de forma local:

Pedir los datos de ambiente al adminstrador del proyecto. (.env)

*   Clonar el proyecto dentro de la carpeta /xampp/htdocs/

```
composer install
```
```
cp .env.example .env
```
*   Completar los datos de ambiente proporcionados por el adminstrador.
```
php artisan key generate
```
```
php artisan migrate
```
```
php artisan db:seed
```

####    Listo! el proyecto deberia estar corriendo en http://localhost/{Carpeta de instalacion del proyecto}/broken-pig/public/api

Ante cualquier inconveniente consultar con el administrador tecnico del proyecto.
