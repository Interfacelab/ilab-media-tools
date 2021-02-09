<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp mediacloud:storage verify [--local] [--limit=<number>] [--offset=<number>] [--page=<number>]',
    'commandTitle' => 'Verify Library',
    'commandLink'=> 'https://kb.mediacloud.press/articles/advanced-usage/command-line/migrate-to-cloud-storage',
    'warning' => $warning,
    'taskClass' => $taskClass
])