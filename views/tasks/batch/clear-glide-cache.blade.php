<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp dynamicImages clearCache',
    'commandTitle' => 'Clear Dynamic Images Cache',
    'commandLink'=> 'https://help.mediacloud.press/article/77-clear-dynamic-image-cache',
    'warning' => $warning,
    'taskClass' => $taskClass
])