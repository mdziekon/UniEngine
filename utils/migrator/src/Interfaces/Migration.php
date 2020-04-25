<?php

namespace UniEngine\Utils\Migrations\Interfaces;

/**
 * Provides a basic interface for all migration scripts.
 *
 * Migrations are uniquely identified by their "date of creation" and nothing else
 * (eg. "20000101_120030", which would correspond to ISO 8601 date "2000-01-01T12:00:30).
 * The migration UID should be the first part of migration's filename, followed by
 * a short description (only for users' convenience).
 * Once committed and merged, migration's UID should stay immutable, as it will
 * be stored in users' databases when applied with migrations runner.
 *
 * All migrations class names should start with "Migration_" prefix,
 * eg. "Migration_20000101_120030".
 */
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
     * Notes should be displayed to the users in "getPriorManualActionDescription".
     * Notes should also be added to the release, with a description of all necessary steps.
     *
     * @return bool
     */
    public function isPriorManualActionRequired();

    /**
     * A function returning description of the manual action required before applying this migration.
     * Output of this function is ignored if no manual action is required.
     *
     * @return string[]
     */
    public function getPriorManualActionDescription();

    /**
     * A function returning the version prior to this migration being needed.
     * Usually used to indicate where to look for additional migration notes
     * (eg. when manual work is required).
     *
     * @return string
     *      Semver compatible string
     */
    public function getPreviousProjectVersion();

    /**
     * A function returning the migration ID (date) that has to be already applied
     * to allow this migration to be applied. When this constraint is not met,
     * the migrator will prevent any migrations (even the unconstrained ones)
     * from being applied, essentially requiring the users to roll-back their
     * code version to one that is still compatible (enforcing incremental migration).
     *
     * Returning an empty string means there's no constraint, and the migration can
     * be applied any time.
     *
     * @return string
     *      Migration ID (date) string
     */
    public function getMinimumMigrationLevelRequired();
}

?>
