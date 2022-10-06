<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => null,
    'commandTitle' => 'Relink Mux Videos',
    'commandLink'=> 'https://support.mediacloud.press/articles/advanced-usage/command-line/migrate-to-cloud-storage',
    'warning' => $warning,
    'taskClass' => $taskClass
])