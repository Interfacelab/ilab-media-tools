<div class="troubleshooter-step">
    <div class="troubleshooter-step-icon">
        {% if ($success === true) %}
        <img src="{{ILAB_PUB_IMG_URL}}/icon-success.svg" width="32" height="32">
        {% else if ($success === false) %}
        <img src="{{ILAB_PUB_IMG_URL}}/icon-error.svg" width="32" height="32">
        {% else %}
        <img src="{{ILAB_PUB_IMG_URL}}/icon-warning.svg" width="32" height="32">
        {% endif %}
    </div>
    <div>
        <div class="troubleshooter-title">{{$title}}</div>
        <div class="troubleshooter-message">
            {% if ($success) %}
            {{ $success_message }}
            {% else %}
            {{ $error_message }}
            {% endif %}
        </div>
        {% if (!$success && !empty($errors)) %}
        <ul class="troubleshooter-errors">
            {% foreach($errors as $error) %}
            <li>{{$error}}</li>
            {% endforeach %}
        </ul>
        {% endif %}
    </div>
</div>