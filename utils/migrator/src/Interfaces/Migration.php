<?php

namespace UniEngine\Utils\Migrations\Interfaces;

interface Migration {
    public function up();
    public function down();

    public function isPriorManualActionRequired();
}

?>
