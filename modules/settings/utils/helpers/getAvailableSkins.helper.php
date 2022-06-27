<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Helpers;

function getAvailableSkins() {
    $knownSkinNames = [
        'xnova'             => 'XNova',
        'epicblue'          => 'EpicBlue Fresh',
        'epicblue_old'      => 'EpicBlue Standard',
    ];

    $skinsDir = 'skins';
    $skinsDirEntries = scandir("./{$skinsDir}/");
    $skinsDirEntries = !empty($skinsDirEntries) ? $skinsDirEntries : [];

    $availableSkins = [];

    foreach ($skinsDirEntries as $skinDirEntry) {
        if (
            strstr($skinDirEntry, '.') !== false ||
            !is_dir("./skins/{$skinDirEntry}")
        ) {
            continue;
        }

        $skinName = $skinDirEntry;

        if (empty($knownSkinNames[$skinName])) {
            $knownSkinNames[$skinName] = $skinName;
        }

        $availableSkins[] = [
            'path' => "{$skinsDir}/{$skinName}/",
            'name' => $knownSkinNames[$skinName],
        ];
    }

    return $availableSkins;
}

?>
