<?php

namespace UniEngine\Engine\Modules\Settings\Utils\Helpers;

/**
 * @param array $params
 * @param string $params['rootDir'] (default: "./")
 */
function getAvailableSkins($params = []) {
    $rootDir = !empty($params['rootDir']) ? $params['rootDir'] : "./";

    $knownSkinNames = [
        'xnova'             => 'XNova',
        'epicblue'          => 'EpicBlue Fresh',
        'epicblue_old'      => 'EpicBlue Standard',
    ];

    $skinsDir = 'skins';
    $skinsDirEntries = scandir("{$rootDir}/{$skinsDir}/");
    $skinsDirEntries = !empty($skinsDirEntries) ? $skinsDirEntries : [];

    $availableSkins = [];

    foreach ($skinsDirEntries as $skinDirEntry) {
        if (
            strstr($skinDirEntry, '.') !== false ||
            !is_dir("{$rootDir}/skins/{$skinDirEntry}")
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
