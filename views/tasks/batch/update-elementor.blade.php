<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp mediacloud:elementor update --report --skip-widget-cache',
    'commandTitle' => 'Update Elementor',
    'commandLink'=> 'https://kb.mediacloud.press/articles/advanced-usage/command-line/update-elementor',
    'warning' => $warning,
    'taskClass' => $taskClass
])