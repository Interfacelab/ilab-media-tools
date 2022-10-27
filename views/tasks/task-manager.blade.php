<?php /** @var \MediaCloud\Plugin\Tasks\TaskManager $manager */?>
<?php
use function \MediaCloud\Plugin\Utilities\arrayPath;
?>
@extends('../templates/sub-page')

@section('header')
<div class="header-actions">
    <a type="button" class="button button-primary" href="{{admin_url('admin.php?page=media-cloud-settings&tab=batch-processing')}}">Task Manager Settings</a>
</div>
@endsection
@section('main')
    <div id="task-manager" class="settings-body" data-status-nonce="{{wp_create_nonce('mcloud_task_status')}}">
        <p>The Task Manager allows you to view all of your currently running tasks, tasks that have completed and any scheduled tasks about to happen.</p>
        <p>To test your system, try launching a Test Task to see how it performs.</p>
        <div class="available-tasks">
            <h2>Available Tasks</h2>
            <div class="buttons">
                @foreach(\MediaCloud\Plugin\Tasks\TaskManager::registeredTasks() as $id => $taskClass)
                    @continue(!$taskClass::runFromTaskManager())
                    <button type="button" class="button button-primary button-small button-start-task" data-task-id="{{$id}}" data-has-options="{{!empty($taskClass::taskOptions())}}" data-nonce="{{wp_create_nonce('mcloud_start_task')}}">{{$taskClass::title()}}</button>
                @endforeach
            </div>
            <div class="actions">
                <button type="button" class="button button-nuke-all button-small" data-nuke-all-nonce="{{wp_create_nonce('mcloud_nuke_all_tasks')}}">Reset All Task Data</button>
            </div>
        </div>

        <div class="task-list running-tasks">
            <h2>
                Running Tasks
                <div class="actions">
                    <button type="button" class="button button-cancel-all button-small" data-cancel-all-nonce="{{wp_create_nonce('mcloud_cancel_all_tasks')}}" disabled="disabled">Cancel All</button>
                </div>
            </h2>
            <table class="task-table" id="running-tasks" style="display:none">
                <thead>
                <tr>
                    <th class="task">Task</th>
                    <th class="status">Status</th>
                    <th class="started">Started</th>
                    <th class="total-time">Total Time</th>
                    <th class="remaining">Remaining</th>
                    <th class="time-per">Time Per</th>
                    <th class="memory-per">Memory Per</th>
                    <th class="current">Processed</th>
                    <th class="progress">Progress</th>
                    <th class="actions">Actions</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div class="no-results" style="display:none">
                No Running Tasks
            </div>
        </div>

        <div class="task-list scheduled-tasks">
            <h2>
                Scheduled Tasks
            </h2>
            <table class="task-table" id="scheduled-tasks" style="display:none">
                <thead>
                <tr>
                    <th class="task">Task</th>
                    <th class="recurring">Recurring</th>
                    <th class="last-run">Last Run</th>
                    <th class="next-run">Next Run</th>
                    <th class="schedule">Schedule</th>
                    <th class="actions">Actions</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div class="no-results" style="display:none">
                No Scheduled Tasks
            </div>
        </div>

        <div class="task-list past-tasks">
            <h2>
                Past Tasks
                <div class="actions">
                    <button type="button" class="button button-clear-history button-small" data-clear-history-nonce="{{wp_create_nonce('mcloud_clear_task_history')}}" disabled="disabled">Clear History</button>
                </div>
            </h2>
            <table class="task-table" id="past-tasks" style="display:none">
                <thead>
                <tr>
                    <th class="task">Task</th>
                    <th class="status">Status</th>
                    <th class="started">Started</th>
                    <th class="completed">Completed</th>
                    <th class="total-time">Total Time</th>
                    <th class="current">Processed</th>
                    <th class="time-per">Time Per</th>
                    <th class="memory-per">Memory Per</th>
                    <th class="progress">Progress</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div class="no-results" style="display:none">
                No Past Tasks
            </div>
        </div>
    </div>

    <script type="application/json" id="task-manager-running-tasks">
        {!! json_encode(array_values($manager->runningTasks()), JSON_PRETTY_PRINT) !!}
    </script>
    <script type="application/json" id="task-manager-past-tasks">
        {!! json_encode(\MediaCloud\Plugin\Tasks\Task::completeTasks(), JSON_PRETTY_PRINT) !!}
    </script>
    <script type="application/json" id="task-manager-scheduled-tasks">
        {!! json_encode(\MediaCloud\Plugin\Tasks\TaskSchedule::scheduledTasks(), JSON_PRETTY_PRINT) !!}
    </script>

    <script type="text/html" id="tmpl-running-task-template">
        <tr>
            <td class="task">@{{ data.title }}</td>
            <td class="status status-@{{ data.stateName }}">@{{ data.stateName }}</td>
            <td class="started"></td>
            <td class="total-time"></td>
            <td class="remaining"></td>
            <td class="time-per"></td>
            <td class="memory-per">@{{ data.memoryPerString }}</td>
            <td class="current">@{{ data.currentItem }} of @{{ data.totalItems }}</td>
            <td class="progress">
                <div class="progress-bar">
                    <div class="bar"></div>
                    <span class="amount"></span>
                </div>
            </td>
            <td class="actions">
                <button type="button" class="button button-small button-cancel-task" data-cancel-nonce="{{wp_create_nonce('mcloud_cancel_task')}}">Cancel</button>
            </td>
        </tr>
    </script>

    <script type="text/html" id="tmpl-past-task-template">
        <tr class="status-@{{ data.stateName }}">
            <td class="task">@{{ data.title }}</td>
            <td class="status status-@{{ data.stateName }}">@{{ data.stateName }}</td>
            <td class="started"></td>
            <td class="completed"></td>
            <td class="total-time"></td>
            <td class="current">@{{ data.currentItem }} of @{{ data.totalItems }}</td>
            <td class="time-per"></td>
            <td class="memory-per">@{{ data.memoryPerString }}</td>
            <td class="progress">
                <div class="progress-bar">
                    <div class="bar"></div>
                    <span class="amount"></span>
                </div>
            </td>
        </tr>
    </script>

    <script type="text/html" id="tmpl-scheduled-task-template">
        <tr>
            <td class="task">@{{data.taskTitle}}</td>
            <td class="recurring"><# if (data.recurring) { #>Yes<# } else { #>No<# } #></td>
            <td class="last-run">@{{data.lastRun}}</td>
            <td class="next-run">@{{data.nextRun}}</td>
            <td class="schedule">@{{data.description}}</td>
            <td class="actions">
                <button type="button" class="button button-small button-execute-task" data-execute-nonce="{{wp_create_nonce('mcloud_execute_scheduled_task')}}">Execute</button>
                <button type="button" class="button button-small button-delete-task" data-delete-nonce="{{wp_create_nonce('mcloud_delete_scheduled_task')}}">Delete</button>
            </td>
        </tr>
    </script>

    @foreach(\MediaCloud\Plugin\Tasks\TaskManager::registeredTasks() as $identifier => $taskClass)
        @continue(empty($taskClass::taskOptions()))
    <script type="text/html" id="tmpl-{{$identifier}}-modal" data-options-for="{{$identifier}}">
        <div class="task-options-modal">
            <div class="task-modal">
                <h2>{{$taskClass::title()}} Options</h2>
                <form>
                    <ul>
                        @foreach($taskClass::taskOptions() as $optionName => $option)
                            <li>
                                <div>
                                    {!! $option['title'] !!}
                                </div>
                                <div>
                                    <div class="option-ui option-ui-{{$option['type']}}">
                                        @if($option['type'] == 'checkbox')
                                            @include('base/ui/checkbox', ['name' => $optionName, 'value' => $option['default'], 'description' => '', 'enabled' => true])
                                        @elseif($option['type'] == 'select')
                                            <select name="{{$optionName}}">
	                                            <?php $defaultValue = arrayPath($option, 'default', 'null'); ?>
                                                @foreach($option['options'] as $suboptionValue => $suboptionName)
                                                    <option {{($defaultValue == $suboptionValue) ? 'selected' : ''}} value="{{$suboptionValue}}">{{$suboptionName}}</option>
                                                @endforeach
                                            </select>
                                        @elseif($option['type'] == 'text')
                                            <input style="width: 100%; max-width: 500px;" type="text" name="{{$optionName}}" value="{{arrayPath($option, 'default', '')}}" placeholder="{{arrayPath($option, 'placeholder', '')}}">
                                        @elseif($option['type'] == 'url')
                                            <input style="width: 100%; max-width: 500px;" type="url" name="{{$optionName}}" value="{{arrayPath($option, 'default', '')}}" placeholder="{{arrayPath($option, 'placeholder', '')}}">
                                        @elseif($option['type'] == 'browser')
                                            <input type="text" name="{{$optionName}}" disabled="disabled" value="{{$option['default']}}"><button type="button" class="button button-small button-primary" data-nonce="{{wp_create_nonce('storage-browser')}}">Browse</button>
                                        @elseif($option['type'] == 'media-select')
                                            <div id="{{$optionName}}-display" class="media-select-label">All Media Items</div><input type="hidden" name="{{$optionName}}"><button type="button" class="button button-small button-primary button-select-media">Select Media</button><button type="button" class="button button-small button-primary button-clear-media">Clear Selection</button>
                                        @elseif($option['type'] == 'number')
                                            <input type="number" name="{{$optionName}}" value="{{$option['default']}}" min="{{$option['min']}}" max="{{$option['max']}}" step="{{$option['step']}}">
                                        @endif
                                    </div>
                                    <div class="description">{!! $option['description'] !!}</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </form>
                <div class="buttons">
                    <button type="button" class="button button-modal-cancel">Cancel</button>
                    <button type="button" class="button button-primary button-modal-run-task">Run Task</button>
                </div>
            </div>
        </div>
    </script>
    @endforeach
@endsection

