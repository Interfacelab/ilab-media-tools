<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => null,//'wp mediacloud:storage regenerate',
    'commandTitle' => 'Regenerate Filmstrip',
    'commandLink'=> 'https://docs.mediacloud.press/articles/advanced-usage/command-line/regenerate-thumbnails',
    'warning' => $warning,
    'taskClass' => $taskClass
])