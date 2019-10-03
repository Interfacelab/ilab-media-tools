<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp mediacloud unlink [--limit=<number>] [--offset=<number>] [--page=<number>]',
    'commandTitle' => 'Unlink From Cloud Storage',
    'commandLink'=> 'https://help.mediacloud.press/article/76-unlink-from-cloud-storage',
    'warning' => $warning,
    'taskClass' => $taskClass
])