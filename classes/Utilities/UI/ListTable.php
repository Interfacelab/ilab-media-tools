<?php

namespace MediaCloud\Plugin\Utilities\UI;

/**
 * Extends \WP_List_Table and fixes an issue when embedded in Metaboxes
 */
abstract class ListTable extends \WP_List_Table {
	protected function display_tablenav( $which ) {
		?>
		<div class="tablenav <?php echo esc_attr($which); ?>">

			<?php if($this->has_items()): ?>
				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions($which); ?>
				</div>
			<?php endif;
			$this->extra_tablenav($which);
			$this->pagination($which);
			?>

			<br class="clear"/>
		</div>
		<?php
	}
}