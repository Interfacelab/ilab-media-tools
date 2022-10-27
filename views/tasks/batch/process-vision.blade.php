<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp vision process',
    'commandTitle' => 'Process Images With Vision',
    'commandLink'=> 'https://kb.mediacloud.press/articles/advanced-usage/command-line/process-image-with-vision',
    'warning' => $warning,
    'taskClass' => $taskClass
])