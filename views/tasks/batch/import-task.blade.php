<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp mediacloud importFromCloud [--import-path=<string>] [--preserve-paths=preserve|replace|prepend] [--import-only]  [--skip-thumbnails]',
    'commandTitle' => 'Import From Cloud Storage',
    'commandLink'=> 'https://help.mediacloud.press/article/74-import-from-cloud-storage',
    'warning' => $warning,
    'taskClass' => $taskClass
])