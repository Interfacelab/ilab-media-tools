<div class="wrap">
    <form action='options.php' method='post'>
        <?php
        settings_fields( $group );
        do_settings_sections( $page );
        submit_button();
        ?>
    </form>
</div>