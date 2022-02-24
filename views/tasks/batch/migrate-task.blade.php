<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp mediacloud:storage migrate [--limit=<number>] [--offset=<number>] [--page=<number>] [--path-handling=preserve|replace|prepend] [--skip-thumbnails] [--skip-imported] [--order-by=date|title|filename] [--order=asc|desc] [--delete-migrated] [--allow-optimizers]',
    'commandTitle' => 'Migrate To Cloud Storage',
    'commandLink'=> 'https://kb.mediacloud.press/articles/advanced-usage/command-line/migrate-to-cloud-storage',
    'warning' => $warning,
    'taskClass' => $taskClass
])