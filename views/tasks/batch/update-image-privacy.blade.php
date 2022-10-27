<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp mediacloud:image-size update [--limit=<number>] [--offset=<number>] [--page=<number>] [--order-by=date|title|filename] [--order=asc|desc]',
    'commandTitle' => 'Update Image Privacy',
    'commandLink'=> 'https://kb.mediacloud.press/articles/advanced-usage/command-line/update-image-privacy',
    'warning' => $warning,
    'taskClass' => $taskClass
])