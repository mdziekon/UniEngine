<?php

namespace UniEngine\Utils\Migrations\Interfaces;

interface Migration {
    /**
     * A function applying all necessary work to make an instance of this project
     * work properly when migrating to the next version.
     *
     * @return void
     */
    public function up();
    /**
     * A function reverting all applied work by this migrations "up" function.
     * Run either on migrations' manual rollback, or when migration fails in the middle
     * of the process of batch application.
     *
     * @return void
     */
    public function down();

    /**
     * A function indicating whether it's needed to apply any manual work.
     * Notes should be added to the release, with a description of all necessary steps.
     *
     * @return bool
     */
    public function isPriorManualActionRequired();

    /**
     * A function returning the version prior to this migration being needed.
     * Usually used to indicate where to look for additional migration notes
     * (eg. when manual work is required).
     *
     * @return string
     *      Semver compatible string
     */
    public function getPreviousProjectVersion();
}

?>
