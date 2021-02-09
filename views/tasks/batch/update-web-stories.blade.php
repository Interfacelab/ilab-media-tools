<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp mediacloud:integrations updateWebStories --report',
    'commandTitle' => 'Update Web Stories',
    'commandLink'=> 'https://kb.mediacloud.press/articles/advanced-usage/command-line/update-elementor',
    'warning' => $warning,
    'taskClass' => $taskClass
])