<?php

namespace UniEngine\Utils\OneOffs\Interfaces;

/**
 * Provides a basic interface for all one-off scripts.
 *
 * Scripts are uniquely identified by their "date of creation" and nothing else
 * (eg. "20000101_120030", which would correspond to ISO 8601 date "2000-01-01T12:00:30).
 * The script UID should be the first part of script's filename, followed by
 * a short description (only for users' convenience).
 * Once committed and merged, script's UID should stay immutable, as it may be
 * stored in server instance's history log (although, currently not implemented).
 *
 * All script class names should start with "Script_" prefix,
 * eg. "Script_20000101_120030".
 */
interface Script {
    /**
     * A function responsible for doing all the script's work.
     *
     * @return void
     */
    public function execute();
}

?>
