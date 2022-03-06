<?php

namespace SlimGenerator;

use PDO;
use Exception;
use SlimGenerator\CliHelper;

class Database
{
    protected $_instance;
    protected static $settings = array();

    const TYPES = array(
        'string' => 'VARCHAR',
        'text' => 'TEXT',
        'int' => 'INT',
        'float' => 'FLOAT',
        'datetime' => 'DATETIME'
    );

    public function __construct()
    {
        // Ask for credentials if never asked before
        if (count(self::$settings) === 0) {
            CliHelper::print('Database credentials:');
            self::$settings = [
                'driver' => CliHelper::getUserInput('Driver [mysql]:', 'mysql'),
                'host' => CliHelper::getUserInput('Host [127.0.0.1]:', '127.0.0.1'),
                'charset' => CliHelper::getUserInput('Charset [utf8]:', 'utf8'),
                'username' => CliHelper::getUserInput('Username:', ''),
                'password' => CliHelper::getUserInput('Password:', '', true)
            ];
        }

        // connect to the database
        $this->_instance = self::getPDOInstance(false);


        // select database if not selected before
        if (!isset(self::$settings['database']))  {
            CliHelper::print('✅ Connection to database: Success');

            $all_databases = $this->getAllDatabases();
            $total_databases = count($all_databases);
            if ($total_databases > 0) {
                CliHelper::print('Choose database or enter new one:');
                for($i = 0; $i < $total_databases; $i++) {
                    CliHelper::print('['.$i.'] - ' . $all_databases[$i]);
                }
                $database_choice = CliHelper::getUserInput('Choose an existing one or create:', '');
                $database = (int)$database_choice > 0 ? $all_databases[(int)$database_choice] : $database_choice;
            }
            else 
                $database = CliHelper::getUserInput('Database:', '');

            self::$settings['database'] = $database;
        }

        // create database if not exist
        if (!$this->databaseExist()) {
            CliHelper::print('Database "' . self::$settings['database'] . '" does not exist yet. Creating...');
            $this->_instance->exec('CREATE DATABASE IF NOT EXISTS ' . self::$settings['database']);
            CliHelper::print('Database "' . self::$settings['database'] . '" created');
        }
        else
            CliHelper::print('Database "' . self::$settings['database'] . '" choosed.');

        // connect to database with database selected
        $this->_instance = self::getPDOInstance();
    }

    protected static function getPDOInstance($select_database = true) {
        if (!$select_database) {
            $dsn_schema = '%s:host=%s;charset=%s';
            $dsn = sprintf(
                $dsn_schema, 
                self::$settings['driver'],
                self::$settings['host'],
                self::$settings["charset"]
            );
        }
        else 
        {
            $dsn_schema = '%s:host=%s;dbname=%s;charset=%s';
            $dsn = sprintf(
                $dsn_schema, 
                self::$settings['driver'],
                self::$settings['host'],
                self::$settings['database'],
                self::$settings["charset"]
            );
        }

        return new PDO(
            $dsn, 
            self::$settings['username'], 
            self::$settings['password'], 
            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
        );
    }

    public static function getInstance()
    {
        // return Database object with PDO instancied
        return new self();
    }

    public function query($sql)
    {
        return $this->_instance->query($sql);
    }

    public function exec($sql)
    {
        return $this->_instance->exec($sql);
    }

    public function databaseExist($db_name = false)
    {
        $db_name = $db_name === false ? self::$settings['database'] : $db_name;
        
        return in_array($db_name, $this->getAllDatabases());
    }

    public function getAllDatabases()
    {
        $req = $this->_instance->query('show databases');
        return array_column($req->fetchAll(PDO::FETCH_ASSOC), 'Database');
    }

    public function tableExist($table, $db_name = false)
    {
        $db_name = $db_name === false ? self::$settings['database'] : $db_name;

        return in_array(strtolower($table), $this->getAllTables());
    }

    public function getAllTables($db_name = false)
    {
        $db_name = $db_name === false ? self::$settings['database'] : $db_name;

        if (!self::databaseExist($db_name))
            throw new Exception('Database "'. $db_name .'" not found');

        $sql = 'use %s;';
        $this->_instance->exec(sprintf($sql, $db_name));

        $req = $this->_instance->query('show tables');
        $res = $req->fetchAll(PDO::FETCH_ASSOC);
        
        $all_tables = array();
        foreach ($res as $row)
            foreach ($row as $alias => $table_name)
                $all_tables[] = $table_name;

        return $all_tables;
    }
}