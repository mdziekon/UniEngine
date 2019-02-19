<?php

namespace UniEngine\Utils\Migrations;

/**
 * Config providing class.
 * Supports config format used in v1.0.0 and above.
 *
 * Expects the "config.php" file to store its data as
 * "$__ServerConnectionSettings" associative array with these properties:
 * - server
 * - user
 * - pass
 * - name
 * - prefix
 * - secretword
 *
 */
class PHPConfigProvider {
    protected $filepath;
    /**
     * The internal representation of config file, loaded into memory
     * and transformed into more consistent object.
     */
    protected $config;

    public function __construct($filepath = "./config.php") {
        $this->filepath = $filepath;
    }

    public function getConfig() {
        if (!$this->config) {
            $this->config = $this->loadConfigObject();
        }

        return $this->config;
    }

    public function getDatabaseConfig() {
        $config = $this->getConfig();

        return $config["database"];
    }

    protected function loadConfigObject() {
        $path = $this->filepath;

        if (!file_exists($path)) {
            throw new \Exception("\"{$path}\" does not exist");
        }

        if (!is_readable($path)) {
            throw new \Exception("\"{$path}\" is not readable");
        }

        define("INSIDE", true);

        @include_once($path);

        if (empty($__ServerConnectionSettings)) {
            throw new Exception("\"{$path}\" could not be loaded");
        }

        if (empty($__ServerConnectionSettings["server"])) {
            throw new Exception("\"{$path}\" | \"server\" property is empty");
        }
        if (empty($__ServerConnectionSettings["user"])) {
            throw new Exception("\"{$path}\" | \"user\" property is empty");
        }
        if (empty($__ServerConnectionSettings["pass"])) {
            throw new Exception("\"{$path}\" | \"pass\" property is empty");
        }
        if (empty($__ServerConnectionSettings["name"])) {
            throw new Exception("\"{$path}\" | \"name\" property is empty");
        }

        return [
            "database" => [
                "host" => $__ServerConnectionSettings["server"],
                "username" => $__ServerConnectionSettings["user"],
                "password" => $__ServerConnectionSettings["pass"],
                "dbname" => $__ServerConnectionSettings["name"],
                "tables_prefix" => $__ServerConnectionSettings["prefix"],
                "port" => null,
                "socket" => null
            ],
            "authentication" => [
                "passwordhash_secretword" => $__ServerConnectionSettings["secretword"]
            ]
        ];
    }
}

?>
