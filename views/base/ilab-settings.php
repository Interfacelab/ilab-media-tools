<div class="wrap">
    <form action='options.php' method='post'>
        <h2>{{$title}}</h2>

        <?php
        settings_fields( $group );
        do_settings_sections( $page );
        submit_button();
        ?>
    </form>
</div>