<?php

namespace UniEngine\Utils\Migrations;

use UniEngine\Utils\Migrations\Exceptions\DBDriverException;

/**
 * Database driver class, useful when performing DB-related migrations.
 *
 * All queries applied to public methods of this class are executed as raw queries
 * (not prepared statements).
 *
 * Available query formatters:
 *  - "{{table($tableName)}}"
 *      Behaves as a function call, $tableName argument will be transformed into
 *      a prefixed table name (using "tables_prefix" provided by configProvider dependency).
 */
class DBDriver {
    protected $dbConfig;
    protected $dbConnection;

    protected $configProvider;

    /**
     * @param array $dependencies Array containing drivers dependencies to be injected.
     *      $dependencies = [
     *          'configProvider' => (function (): array)
     *              A callable function with no arguments, returning an associative
     *              array that has to contain these properties needed for DB connection:
     *              - host (String)
     *              - username (String)
     *              - password (String)
     *              - dbname (String)
     *              - tables_prefix (String)
     *              - port (Number | null)
     *              - socket (String | null)
     *      ]
     */
    public function __construct($dependencies) {
        if (!is_callable($dependencies["configProvider"])) {
            throw new \InvalidArgumentException("\$dependencies[\"configProvider\"] is not a function");
        }

        $this->configProvider = new \ReflectionFunction($dependencies["configProvider"]);
    }

    public function __destruct() {
        $this->closeConnection();
    }

    public function prefixTableName($tableName) {
        return (
            ($this->getDatabaseConfig())["tables_prefix"] .
            $tableName
        );
    }

    public function executeQuery($query) {
        return $this->sendDBQuery($query);
    }

    public function fetchRow($query) {
        return $this->sendDBQuery($query)->fetch_assoc();
    }

    public function fetchAllRows($query) {
        return $this->sendDBQuery($query)->fetch_all(MYSQLI_ASSOC);
    }

    protected function parseQuery($query) {
        $finalQuery = $query;

        $finalQuery = preg_replace_callback(
            "/\{\{table\((.*?)\)\}\}/is",
            function ($matches) {
                return $this->prefixTableName($matches[1]);
            },
            $finalQuery
        );

        return $finalQuery;
    }

    protected function sendDBQuery($query) {
        $connection = $this->getDBConnection();

        $parsedQuery = $this->parseQuery($query);

        $result = $connection->query($parsedQuery);

        if (
            $result === false ||
            $connection->errno !== 0
        ) {
            throw new DBDriverException(
                "Query execution failure (errno: " . $connection->errno . "):\n" .
                $connection->error . "\n" .
                "Executed query: " . $parsedQuery
            );
        }

        return $result;
    }

    protected function provideDatabaseConfig() {
        return $this->configProvider->invoke();
    }

    protected function getDatabaseConfig() {
        if (!($this->dbConfig)) {
            $this->dbConfig = $this->provideDatabaseConfig();
        }

        return $this->dbConfig;
    }

    protected function getDBConnection() {
        if (!$this->dbConnection) {
            $this->initConnection();
        }

        if ($this->dbConnection->connect_error) {
            throw new DBDriverException("Database connection unavailable, cannot perform any operations");
        }

        return $this->dbConnection;
    }

    protected function initConnection() {
        $config = $this->getDatabaseConfig();

        $this->dbConnection = new \mysqli(
            $config["host"],
            $config["username"],
            $config["password"],
            $config["dbname"],
            $config["port"],
            $config["socket"]
        );

        if ($this->dbConnection->connect_error) {
            throw new DBDriverException("Database connection failure");
        }
    }

    protected function closeConnection() {
        if (!$this->dbConnection) {
            return;
        }

        if ($this->dbConnection->connect_error) {
            return;
        }

        $this->dbConnection->close();
    }
}

?>
