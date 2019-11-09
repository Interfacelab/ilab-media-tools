<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp mediacloud migrateNGG',
    'commandTitle' => 'Migrate NextGen Galleries',
    'warning' => $warning,
    'taskClass' => $taskClass
])