<?php /** @var string $taskClass */ ?>
@include('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp mediacloud:buddypress migrate',
    'commandTitle' => 'Migrate BuddyPress Media',
    'commandLink'=> 'https://kb.mediacloud.press/articles/advanced-usage/command-line/update-elementor',
    'warning' => $warning,
    'taskClass' => $taskClass
])