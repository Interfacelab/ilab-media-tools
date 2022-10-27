<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => null,
    'commandTitle' => 'Clean Uploads',
    'commandLink'=> null,
    'warning' => $warning,
    'taskClass' => $taskClass
])