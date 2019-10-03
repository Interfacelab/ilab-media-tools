<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp mediacloud regenerate',
    'commandTitle' => 'Regenerate Thumbnails',
    'commandLink'=> 'https://help.mediacloud.press/article/75-regenerate-thumbnails',
    'warning' => $warning,
    'taskClass' => $taskClass
])