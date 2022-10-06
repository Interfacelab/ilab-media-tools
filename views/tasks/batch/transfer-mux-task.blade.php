<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => null,
    'commandTitle' => 'Migrate To Cloud Storage',
    'commandLink'=> 'https://support.mediacloud.press/articles/advanced-usage/command-line/migrate-to-cloud-storage',
    'warning' => $warning,
    'taskClass' => $taskClass
])