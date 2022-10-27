<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp mediacloud:storage regenerate',
    'commandTitle' => 'Regenerate Thumbnails',
    'commandLink'=> 'https://kb.mediacloud.press/articles/advanced-usage/command-line/regenerate-thumbnails',
    'warning' => $warning,
    'taskClass' => $taskClass
])