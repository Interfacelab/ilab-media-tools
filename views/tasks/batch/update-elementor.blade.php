<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp mediacloud updateElementor',
    'commandTitle' => 'Update Elementor',
    'commandLink'=> 'https://help.mediacloud.press/article/104-update-elementor',
    'warning' => $warning,
    'taskClass' => $taskClass
])