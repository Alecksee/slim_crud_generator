# slim_crud_generator

CLI tool for PHP Slim Framework.


# Prerequisites
Having a Slim Framework project connected with Eloquent ORM. 

@see 
 - documentation from Slim Framework v3: [Using Eloquent with Slim (en)](https://www.slimframework.com/docs/v3/cookbook/database-eloquent.html)
 - my article from my personnal blog: [Créez une API avec Slim 4 et Eloquent (fr)](https://alexisallais.fr/creez_une_api_avec_slim_4_et_eloquent/) 


# How to use
Clone the project on your machine.
> `git clone git@github.com:Alecksee/slim_crud_generator.git`

Enter in the cloned directory
> `cd slim_crud_generator`

Execute the main php file with 2 parameters:

1. the command name
2. /path/to/your/slim/project

Example:
> `php slim_generator.php crud/create /path/to/your/slim/project`

## Available commands:

### crud/create
- create table into your database
- generate model linked with the created table
- generate routes and basics controllers.

> Example with Animals table
>
>Method | Route
>---:|:---
>GET | /aminal/all
>POST| /animal
>GET | /animal/{id}
>PUT | /animal/{id}
>DELETE | /animal/{id}

#### Notes
v2
