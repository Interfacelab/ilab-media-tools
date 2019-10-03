<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp vision process',
    'commandTitle' => 'Process Images With Vision',
    'commandLink'=> 'https://help.mediacloud.press/article/103-process-image-with-vision',
    'warning' => $warning,
    'taskClass' => $taskClass
])