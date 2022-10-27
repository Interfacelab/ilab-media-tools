@extends('../templates/sub-page', ['title' => $title])

@section('main')
    <div class="settings-body ilab-storage-browser" data-bucket="{{$bucketName}}" data-path="{{$path}}" data-base-url="{{admin_url('admin.php?page=media-tools-storage-browser')}}" data-nonce="{{wp_create_nonce('storage-browser')}}" data-uploads="{{($allowUploads && $directUploads) ? 'true' : 'false'}}" data-delete="{{($allowDeleting) ? 'true' : 'false'}}">
        <div class="mcsb-actions">
            <div class="ilab-storage-browser-header">
                <ul>
                </ul>
            </div>
            <div class="mcsb-buttons mcsb-action-buttons">
                <a href="https://kb.mediacloud.press/articles/documentation/tools/storage-browser" class="button button-primary button-help" target="_blank" data-article-sidebar="https://kb.mediacloud.press/articles/documentation/tools/storage-browser">@inline('ilab-ui-icon-help.svg') Help</a>
                @if($allowUploads && $directUploads)
                <a href="#" class="button button-primary button-upload">@inline('ilab-ui-icon-upload.svg') Upload</a>
                <a href="#" class="button button-primary button-create-folder">@inline('ilab-ui-icon-create-folder.svg') Create Folder</a>
                @endif
                <a href="#" class="button button-primary button-import disabled">@inline('ilab-ui-icon-import.svg') Import</a>
                @if($allowDeleting)
                <a href="#" class="button button-delete disabled">@inline('ilab-ui-icon-trash.svg') Delete</a>
                @endif
            </div>
        </div>

        <div class="mcsb-container">
            <table>
                <thead>
                <tr>
                    @if(empty($hideCheckBoxes))
                    <th class="checkbox">
                        <img class="loading" src="{{admin_url('images/spinner.gif')}}">
                        <input type="checkbox">
                    </th>
                    @endif
                    <th>Name</th>
                    <th>Last Modified</th>
                    <th>Size</th>
                    @if(empty($hideActions))
                    <th class="actions"></th>
                    @endif
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div class="mscb-loading">
                <img class="loader" width="16" height="16" src="{{admin_url('images/spinner.gif')}}"> Loading ...
            </div>
        </div>
    </div>
    
    <div id="mcsb-progress-modal" class="mcsb-modal hidden">
        <div class="mcsb-modal-contents mcsb-progress-container">
            <div class="mcsb-progress-label">Deleting 'some-file.jpg'</div>
            <div class="mcsb-progress-bar">
                <div id="mcsb-progress"></div>
            </div>
            <div class="mcsb-progress-actions">
                <a href="#" class="button button-delete button-cancel">Cancel</a>
            </div>
        </div>
    </div>

    <div id="ilab-upload-target">
        Drop Files to Upload
    </div>

    <div id="mcsb-import-options-modal" class="mcsb-modal hidden">
        <div class="mcsb-modal-contents mcsb-import-options-container">
            <h3>Import Options</h3>
            <div class="mcsb-import-options">
                <ul>
                    <li>
                        <div class="mcsb-option">
                            @include('base/fields/checkbox', ['name' => 'import-option-skip-download', 'value' => false, 'description' => '', 'conditions' => null])
                        </div>
                        <div class="mcsb-option-description">
                            <h4>Import Only</h4>
                            Don't download, import to database only.  If you are using Imgix or Dynamic Images, this setting is ignored.
                        </div>
                    </li>
                    <li>
                        <div class="mcsb-option">
                            @include('base/fields/checkbox', ['name' => 'import-option-preserve-paths', 'value' => false, 'description' => '', 'conditions' => null])
                        </div>
                        <div class="mcsb-option-description">
                            <h4>Preserve Paths</h4>
                            When downloading images, maintain the directory structure that is on cloud storage.
                        </div>
                    </li>
                    <li>
                        <div class="mcsb-option">
                            @include('base/fields/checkbox', ['name' => 'import-option-skip-thumbnails', 'value' => true, 'description' => '', 'conditions' => null])
                        </div>
                        <div class="mcsb-option-description">
                            <h4>Skip Thumbnails</h4>
                            Skips any images that look like they might be thumbnails.  If this option is on, you may import images that are thumbnails but they will be treated as individual images.
                        </div>
                    </li>
                </ul>

            </div>
            <div class="mcsb-buttons mcsb-import-buttons">
                <a href="#" class="button button-primary button-cancel">Cancel Import</a>
                <a href="#" class="button button-primary button-import">@inline('ilab-ui-icon-import.svg') Import</a>
            </div>
        </div>
    </div>

    <div id="mcsb-upload-modal" class="hidden">
        <div id="mcsb-upload-container">
            <div class="mcsb-upload-header">Upload</div>
            <div class="mcsb-upload-items">
                <div id="mcsb-upload-items-container">
                </div>
            </div>
        </div>
    </div>

    <script type="text/template" id="tmpl-mcsb-browser-row">
        <tr data-file-type="@{{data.type}}" data-key="@{{data.key}}" <# if (data.name != '..') { #> data-key="@{{data.key}}" <# } #> >
            @if(empty($hideCheckBoxes))
            <td class="checkbox">
                <# if (data.name != '..') { #>
                <input type="checkbox">
                <# } #>
            </td>
            @endif
            <td class="entry">
                <img class="loader" src="{{admin_url('images/spinner.gif')}}">
                <# if (data.name === '..') { #>
                <span class="row-icon icon-up"></span>
                <# } else { #>
                <span class="row-icon icon-@{{data.type}}"></span>
                <# } #>
                @{{{ data.name }}}
            </td>
            <td class="date">@{{data.date}}</td>
            <td class="size">@{{data.size}}</td>
            @if(empty($hideCheckBoxes))
            <td class="actions">
                <# if (data.type == 'file') { #>
                    <a href="@{{ data.url }}" class="ilab-browser-action-view button button-small @{{ data.disabled }}" target="_blank">View</a>
                    @if(!empty($allowDeleting))
                    <a href="#" class="ilab-browser-action-delete button button-small button-delete">Delete</a>
                    @endif
                <# } #>
            </td>
            @endif
        </tr>
    </script>
    <script type="text/template" id="tmpl-ilab-upload-cell">
        <div class="ilab-upload-item">
            <div class="ilab-upload-item-background"></div>
            <div class="ilab-upload-status-container">
                <div class="ilab-upload-status">Uploading ...</div>
                <div class="ilab-upload-progress">
                    <div class="ilab-upload-progress-track" style="width: 64%;"></div>
                </div>
            </div>
            <div class="ilab-loader-container" style="opacity:0;">
                <div class="ilab-loader"></div>
            </div>
        </div>
    </script>
{{--    <script>--}}
{{--        var browserCurrentPath = "{{$path}}";--}}
{{--        var browserBaseURL = "{{admin_url('admin.php?page=media-tools-storage-browser')}}";--}}
{{--        var browserNonce = "{{wp_create_nonce('storage-browser')}}";--}}

{{--        jQuery(document).ready(function($){--}}
{{--            new ilabStorageBrowser($, {{($allowUploads && $directUploads) ? 'true' : 'false'}}, {{($allowDeleting) ? 'true' : 'false'}});--}}
{{--        });--}}
{{--    </script>--}}
@endsection
