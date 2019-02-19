<?php

namespace UniEngine\Utils\Migrations;

use UniEngine\Utils\Migrations\Exceptions\FileIOException;
use UniEngine\Utils\Migrations\Exceptions\FileMissingException;

/**
 * A simplistic file system related operations handling class.
 * Wraps certain events into a simple interface and a set of well defined exceptions.
 * Database driver class, useful when performing DB-related migrations.
 */
class FSHandler {
    private $rootPath;

    /**
     * @param array $options Array containing general options.
     *      $options = [
     *          'rootPath' => (string)
     *              The base location for all file accesses.
     *              In most cases it will correspond to project's root.
     *      ]
     */
    function __construct($options) {
        $this->rootPath = $options["rootPath"];
    }

    public function getRealPath($path) {
        return ($this->rootPath . $path);
    }

    public function loadFile($filePath) {
        $path = $this->getRealPath($filePath);

        if (!file_exists($path)) {
            throw new FileMissingException("File does not exist");
        }

        if (!is_readable($path)) {
            throw new FileIOException("File is not readable");
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new FileIOException("File could not be loaded");
        }

        return $content;
    }

    public function loadFileLines($filePath) {
        $path = $this->getRealPath($filePath);

        if (!file_exists($path)) {
            throw new FileMissingException("File does not exist");
        }

        if (!is_readable($path)) {
            throw new FileIOException("File is not readable");
        }

        $fileLines = file($path, FILE_IGNORE_NEW_LINES);

        if ($fileLines === false) {
            throw new FileIOException("File could not be loaded");
        }

        return $fileLines;
    }

    public function saveFile($filePath, $data) {
        $path = $this->getRealPath($filePath);

        $accessCheckPath = $path;

        if (!file_exists($path)) {
            $accessCheckPath = dirname($path);
        } else {
            $accessCheckPath = $path;
        }

        if (!is_writeable($accessCheckPath)) {
            throw new FileIOException("File / file's directory is not writeable");
        }

        $result = file_put_contents($path, $data, LOCK_EX);

        if ($result === false) {
            throw new FileIOException("File could not be saved");
        }
    }

    public function saveFileLines($filePath, $fileLines) {
        return $this->saveFile(
            $filePath,
            implode("\n", $fileLines)
        );
    }

    public function loadDirectoryFilenames($dirPath) {
        $path = $this->getRealPath($dirPath);

        $filesList = scandir($path);

        if ($filesList === false) {
            throw new FileIOException("Could not load directory");
        }

        return $filesList;
    }
}

?>
