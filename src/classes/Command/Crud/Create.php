<?php

namespace SlimGenerator\Command\Crud;

use Exception;
use SlimGenerator\Database;
use SlimGenerator\CliHelper;
use SlimGenerator\StrHelper;
use SlimGenerator\Command\AbstractCommand;

class Create extends AbstractCommand
{
    protected $table_name = '';
    protected $model_name = '';
    protected $model_name_plural = '';
    protected $columns = array();

    public function process()
    {
        CLiHelper::print('Begin creation of CRUD ressource for your Slim project.');

        // get model name
        $model_name = CliHelper::getUserInput('Model name in the singular (ex: animal)', '');
        $model_name = ucfirst($model_name);
        $model_name_plural = StrHelper::pluralize($model_name);

        $this->table_name = strtolower($model_name_plural);
        $this->model_name = $model_name;
        $this->model_name_plural = $model_name_plural;

        // create table
        $this->createTable();

        // write files into slim project
        $this->createArborescence();
    }

    protected function createTable() 
    {
        if (Database::getInstance()->tableExist($this->table_name))
            throw new Exception('Table "'. $this->table_name .'" alreay exist.');
        
        // build columns
        $this->addProperties($this->table_name);

        // create table
        $sql_schema ='CREATE TABLE `%s` (
            `id_%s` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
            %s
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        )';
        Database::getInstance()->exec(sprintf($sql_schema, $this->table_name, strtolower($this->model_name), $this->columnsToSql()));
    
        CLiHelper::print('✅ Table "' . $this->table_name . '" created');
    }

    protected function createArborescence()
    {
        $domain_dir = $this->path_to_slim . '/src/Domain/';
        $action_dir = $this->path_to_slim . '/src/Application/Actions/';

        // creating Model dir
        $this->createDir($domain_dir);
        CliHelper::print('✅ Directory ' . $domain_dir . $this->model_name . ' created');

        // creating Controller dir
        $this->createDir($action_dir);
        CliHelper::print('✅ Directory ' . $action_dir . $this->model_name . ' created');

        // create model and controllers classes
        $this->createFiles();

        // edit routes files
        $this->generateRoutes();
    }

    protected function createDir($path)
    {
        if (is_dir($path . $this->model_name))
            throw new Exception('Directory ' . $path . $this->model_name . ' already exist.');

        if (!@mkdir($path . $this->model_name))
            throw new Exception('Directory ' . $path . $this->model_name . ' can\'t be created.');

    }

    protected function createFiles()
    {
        $schemasAssoc = [
            'ModelName'         => '/src/Domain/' . $this->model_name . '/' . $this->model_name . '.php',
            'ModelAction'       => '/src/Application/Actions/' . $this->model_name . '/' . $this->model_name . 'Action.php',
            'AddModelAction'    => '/src/Application/Actions/' . $this->model_name . '/Add' . $this->model_name . 'Action.php',
            'GetModelAction'    => '/src/Application/Actions/' . $this->model_name . '/Get' . $this->model_name . 'Action.php',
            'ListModelAction'   => '/src/Application/Actions/' . $this->model_name . '/List' . $this->model_name_plural . 'Action.php',
            'UpdateModelAction' => '/src/Application/Actions/' . $this->model_name . '/Update' . $this->model_name . 'Action.php',
            'DeleteModelAction' => '/src/Application/Actions/' . $this->model_name . '/Delete' . $this->model_name . 'Action.php',
        ];

        foreach ($schemasAssoc as $schema => $path) {
            $ressource = fopen($this->path_to_slim . $path, 'w');
            fwrite($ressource, $this->getParsedSchema($schema));
            fclose($ressource);
            CliHelper::print('✅ File ' . $this->path_to_slim . $path . ' created');
        }
    }

    protected function generateRoutes() {
        $ressource = fopen( $this->path_to_slim . '/app/routes.php', 'r+');

        // get routes file line per line
        $lines;
        while (($buffer = fgets($ressource)) !== false)
            $lines[] = $buffer;

        // add route group into array
        array_splice($lines, count($lines) - 1, 0, $this->getParsedSchema('routesGroup'));

        // rewrite the routes file line per line
        rewind($ressource);
        foreach($lines as $line)
            fwrite($ressource, $line);
        fclose($ressource);

        CliHelper::print('✅ File ' . $this->path_to_slim . '/app/routes.php updated');
    }

    protected function addProperties($table_name)
    {
        if (!CliHelper::confirm('Add property ?'))
            return false;

        // ask for column name
        $p_name = CliHelper::getUserInput('Property name:', '');

        // ask for column type
        CliHelper::print('Choose type:');
        $i = 0;
        foreach (array_keys(Database::TYPES) as $type) {
            CliHelper::print('[' . $i . '] - ' . $type);
            $i++;
        }
        unset($i);
        $p_type_number = CliHelper::getUserInput('Property type [number]:', '');
        $p_type = array_values(Database::TYPES)[(int) $p_type_number];

        // ask for column length
        $p_length = CliHelper::getUserInput('Property max length:', '', true);

        // ask for column is nullable
        $p_null = CliHelper::confirm('Nullable ?') ? 'NULL' : 'NOT NULL';

        // ask for column has default value
        $p_default = CliHelper::getUserInput('Default value:', '', true);

        // add column
        $this->columns[] = array(
            'name' => $p_name,
            'type' => $p_type,
            'length' => $p_length,
            'null' => $p_null,
            'default' => $p_default
        );

        // adding possibility to undo
        CliHelper::print('Recap:');
        CliHelper::print($this->columnToSql(count($this->columns) - 1));
        if (!CliHelper::confirm('Validate ?')) {
            array_pop($this->columns);
            CliHelper::print('Column removed.');
        }
        else
            CliHelper::print('✅ Column added.');

        // ask for adding other property
        $this->addProperties($table_name);
    }

    protected function columnsToSql()
    {
        $sql_final = array();
        foreach ($this->columns as $key => $column) {
            $sql_final[] = $this->columnToSql($key);
        }

        return implode(',', $sql_final) . ',';
    }

    protected function columnToSql($key)
    {
        $sql_schema = '`%s` %s %s %s';
        $column = $this->columns[$key];

        $type = $column['type'];
        $length = (int)$column['length'] > 0 ? '(' . $column['length'] . ')' : '';
        $type .= $length;
        $default = empty($column['default']) ? '' : 'DEFAULT "'.$column['default'].'"';

        return sprintf($sql_schema, $column['name'], $type,$column['null'], $default);
    }

    protected function getParsedSchema($schema_name) {
        $schemas_vars = [
            '{{slim_generator_model_name}}' => $this->model_name,
            '{{slim_generator_lower_model_name}}' => strtolower($this->model_name),
            '{{slim_generator_model_name_plural}}' => $this->model_name_plural,
        ];
        $schema = file_get_contents(SLIM_GENERATOR_SCHEMAS_DIR . $schema_name.'.txt');

        // add custom columns to AddAction
        $add_fields = '';
        foreach ($this->columns as $column)
            $add_fields .= '${{slim_generator_lower_model_name}}->'. $column['name'] .' = $data["'. $column['name'] .'"];' . "\r\n";

        $schema_updated = sprintf($schema, $add_fields);

        return str_replace(array_keys($schemas_vars), array_values($schemas_vars), $schema_updated);
    }
}