<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp mediacloud:storage import [--import-path=<string>] [--preserve-paths=preserve|replace|prepend] [--import-only]  [--skip-thumbnails]',
    'commandTitle' => 'Import From Cloud Storage',
    'commandLink'=> 'https://kb.mediacloud.press/articles/advanced-usage/command-line/import-from-cloud-storage',
    'warning' => $warning,
    'taskClass' => $taskClass
])