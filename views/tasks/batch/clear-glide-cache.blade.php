<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp dynamicImages clearCache',
    'commandTitle' => 'Clear Dynamic Images Cache',
    'commandLink'=> 'https://docs.mediacloud.press/articles/advanced-usage/command-line/clear-dynamic-image-cache',
    'warning' => $warning,
    'taskClass' => $taskClass
])