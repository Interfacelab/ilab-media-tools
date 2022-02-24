<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp mediacloud:storage unlink [--limit=<number>] [--offset=<number>] [--page=<number>]',
    'commandTitle' => 'Unlink From Cloud Storage',
    'commandLink'=> 'https://kb.mediacloud.press/articles/advanced-usage/command-line/unlink-from-cloud-storage',
    'warning' => $warning,
    'taskClass' => $taskClass
])