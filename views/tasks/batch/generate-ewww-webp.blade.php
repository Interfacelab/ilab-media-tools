<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp mediacloud:storage makewebp [--limit=<number>] [--offset=<number>] [--page=<number>] [--order-by=date|title|filename] [--order=asc|desc]',
    'commandTitle' => 'Generate EWWW WebP',
    'commandLink'=> 'https://docs.mediacloud.press/articles/advanced-usage/command-line/generate-ewww-webp/',
    'warning' => $warning,
    'taskClass' => $taskClass
])