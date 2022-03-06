<?php

class Database {
    protected $_instance;

    public function __construct() {
        try {
            $dbsettings = require("dev/dbsettings.php");
            $this->_instance = new PDO($dbsettings['driver'] . ':host=' . $dbsettings['host'] . ';dbname=' . $dbsettings['database'] . ';charset=' . $dbsettings["charset"], $dbsettings['username'], $dbsettings['password'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        } catch (Exception $e) {
            throw new Error($e->getMessage());
        }
    }

    public function getInstance() {
        return $this->_instance;
    }
}