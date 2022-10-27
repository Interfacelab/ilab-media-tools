<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp mediacloud:storage syncLocal [--limit=<number>] [--offset=<number>] [--page=<number>]',
    'commandTitle' => 'Sync Local Files',
    'commandLink'=> null,
    'warning' => $warning,
    'taskClass' => $taskClass
])