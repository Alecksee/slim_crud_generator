<?php

require("database.php");

class CreateCrud extends Database {
    protected $_name;
    protected $_sname;

    public function __construct($crudName) {
        parent::__construct();
        if ($this->isAlreadyExist($crudName)) {
            $wantContinue = getUserInput("This SQL table already exist, continue ? (y/n)");
            if (strtolower($wantContinue) !== "y")
                throw new Error("Model already exist");
            else
                echo "Please make sure you have createdAt and UpdatedAt timestamp column in your database.\n";
        }
        $this->_name = $crudName;
        $this->_sname = substr($crudName, 0, strlen($crudName) - 1);
        // add new table in BDD
        $this->createTable();
        // creating Model dir
        if (!@mkdir('src/Domain/'.ucfirst($this->_sname)))
            throw new Error("You already have a directory: " . 'src/Domain/' . ucfirst($this->_sname));
        // Creating Controller dir
        if (!@mkdir('src/Application/Actions/' . ucfirst($this->_sname)))
            throw new Error("You already have a directory: " . 'src/Application/Actions/' . ucfirst($this->_sname));
        $ucrudName = ucfirst($this->_sname);
        $schemasAssoc = [
            'ModelName'         => 'src/Domain/' . $ucrudName . '/' . $ucrudName . '.php',
            'ModelAction'       => 'src/Application/Actions/' . $ucrudName . '/' . $ucrudName . 'Action.php',
            'AddModelAction'    => 'src/Application/Actions/' . $ucrudName . '/Add' . $ucrudName . 'Action.php',
            'GetModelAction'    => 'src/Application/Actions/' . $ucrudName . '/Get' . $ucrudName . 'Action.php',
            'ListModelAction'   => 'src/Application/Actions/' . $ucrudName . '/List' . $ucrudName . 'sAction.php',
            'UpdateModelAction' => 'src/Application/Actions/' . $ucrudName . '/Update' . $ucrudName . 'Action.php',
            'DeleteModelAction' => 'src/Application/Actions/' . $ucrudName . '/Delete' . $ucrudName . 'Action.php',
        ];

        foreach ($schemasAssoc as $schema => $path) {
            $ressource = fopen($path, 'w');
            fwrite($ressource, $this->getParsedSchema($schema));
            fclose($ressource);
        }
        $this->generateRoutes();
    }

    protected function isAlreadyExist($modelName) {
        $allTables = $this->getTables();
        if (is_array($allTables))
            return in_array($modelName, $this->getTables());
        return $modelName === $allTables;
    }

    protected function getTables() {
        $req = $this->getInstance()->query("
            SELECT table_name AS tables 
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
        ");
        return $req->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    protected function createTable() {
        $req = $this->getInstance()->query('
            CREATE TABLE IF NOT EXISTS `' . $this->_name . '` (
                `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255),
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
        ');
    }

    protected function getParsedSchema($schemaName) {
        $schema = file_get_contents('dev/src/createCrud/schemas/'.$schemaName.'.txt');
        $lowerReplaced = str_replace("SMODELNAME", $this->_sname, $schema);
        return str_replace("MODELNAME", ucfirst($this->_sname), $lowerReplaced);
    }

    protected function generateRoutes() {
        $ressource = fopen("app/routes.php", "r+");
        $ucrudName = ucfirst($this->_sname);
        $lines;
        $uses = [
            'use App\Application\Models\\' . $ucrudName . ";\r\n",
            'use App\Application\Actions\\' . $ucrudName . '\Add' . $ucrudName . 'Action' . ";\r\n",
            'use App\Application\Actions\\' . $ucrudName . '\Get' . $ucrudName . 'Action' . ";\r\n",
            'use App\Application\Actions\\' . $ucrudName . '\List' . $ucrudName . 'sAction' . ";\r\n",
            'use App\Application\Actions\\' . $ucrudName . '\Delete' . $ucrudName . 'Action' . ";\r\n",
            'use App\Application\Actions\\' . $ucrudName . '\Update' . $ucrudName . 'Action' . ";\r\n"
        ];
        while (($buffer = fgets($ressource)) !== false) {
            $lines[] = $buffer;
        }
        array_splice($lines, 4, 0, $uses);
        array_splice($lines, count($lines) - 1, 0, $this->getParsedSchema("routesGroup"));
        rewind($ressource);
        foreach($lines as $line) {
            fwrite($ressource, $line);
        }
        fclose($ressource);
    }
}