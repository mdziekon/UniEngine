<?php

namespace UniEngine\Utils\OneOffs;

class ScriptsGenerator {
    const SCRIPTS_DIRECTORY = "./utils/one_offs/scripts";
    const SCRIPTS_TEMPLATE_FILEPATH = "./utils/one_offs/src/script.tpl";

    private $fsHandler;

    /**
     * @param array $options Array containing general options.
     *      $options = [
     *          'rootPath' => (string)
     *              The base location of the project. Necessary to locate
     *              "/scripts" directory.
     *      ]
     */
    function __construct($options) {
        $this->fsHandler = new FSHandler([
            "rootPath" => $options["rootPath"]
        ]);
    }

    /**
     * @param array $options Array containing generator options.
     *      $options = [
     *          'name' => (string) [required]
     *              Script's name to be used in the file name.
     *      ]
     */
    public function generateNewScript($options) {
        if (empty($options["name"])) {
            throw new \InvalidArgumentException("\$options[\"name\"] was not defined");
        }

        $content = $this->fsHandler->loadfile(
            self::SCRIPTS_TEMPLATE_FILEPATH
        );

        $newScriptID = date(
            "Ymd_His",
            time()
        );

        $content = str_replace(
            "{{SCRIPT_ID}}",
            $newScriptID,
            $content
        );

        $this->fsHandler->saveFile(
            $this->getScriptFilepath(
                $newScriptID . "_" . $options["name"] . ".php"
            ),
            $content
        );
    }

    private function getScriptFilepath($filename) {
        return (
            self::SCRIPTS_DIRECTORY .
            "/" .
            $filename
        );
    }
}

?>
