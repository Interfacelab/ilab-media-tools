<?php /** @var \MediaCloud\Plugin\Tools\ToolsManager $manager */ ?>
@extends('../templates/sub-page')

<?php
        /** @var \MediaCloud\Plugin\Tools\Network\NetworkSettings $settings */
        $settings = class_exists('\\MediaCloud\\Plugin\\Tools\\Network\\NetworkSettings') ? \MediaCloud\Plugin\Tools\Network\NetworkSettings::instance() : null;

        /** @var \MediaCloud\Plugin\Tasks\Task[] $tasks */
        $tasks = \MediaCloud\Plugin\Tasks\TaskManager::registeredTasks();
?>

@section('main')
    @if(!empty($manager->multisiteTools()))
        <h2>Tools</h2>
        @foreach($manager->multisiteTools() as $tool)
        <div class="ilab-settings-section ilab-settings-features">
            <h4>{{$tool->toolInfo['name']}}</h4>
            <p>{!! $tool->toolInfo['description'] !!}</p>
            <a class="button" href="{{admin_url('admin.php?page='.$tool->optionsPage())}}">Launch Tool</a>
        </div>
        @endforeach
    @endif
    @if((empty($settings) || empty($settings->hideBatchTools)) && !empty($tasks))
        <h2>Batch Tools</h2>
        @foreach($tasks as $taskId => $taskClass)
            @continue(empty($taskClass::userTask()))
            @continue(empty($taskClass::showInMenu()))
            <div class="ilab-settings-section ilab-settings-features">
                <h4>{{$taskClass::title()}}</h4>
                @if(!empty($taskClass::instructionView()))
                @include($taskClass::instructionView(), ['description' => true])
                @endif
                <a class="button" href="{{admin_url('admin.php?page=mcloud-task-'.$taskClass::identifier())}}">Launch Tool</a>
            </div>
        @endforeach
    @endif
@endsection