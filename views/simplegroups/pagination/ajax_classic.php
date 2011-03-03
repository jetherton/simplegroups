<?php 
/**
 * pagination view.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     API Controller
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

/**
 * Ushahidi pagination style
 * 
 * @preview  Pages: 1 … 4 5 6 7 8 … 15
 */
?>

<ul class="pager">

	<li class="first"><?php echo $total_pages . " " . Kohana::lang('ui_main.pages') ?></li>

	<?php if ($current_page > 10): ?>
		<li><a href="#" id="pagination_1" onClick="pagination(1); return false;">1</a></li>
		<?php if ($current_page != 11) echo '&hellip;' ?>
	<?php endif ?>


	<?php for ($i = $current_page - 9, $stop = $current_page + 10; $i < $stop; ++$i): ?>

		<?php if ($i < 1 OR $i > $total_pages) continue ?>

		<?php if ($current_page == $i): ?>
			<li><a href="#" id="pagination_active_page" value="<?php echo $i ?>" class="active"><?php echo $i ?></a></li>
		<?php else: ?>
			<li><a href="#" id="pagination_<?php echo $i; ?>" onClick="pagination(<?php echo $i ?>); return false;"><?php echo $i ?></a></li>
		<?php endif ?>

	<?php endfor ?>


	<?php if ($current_page <= $total_pages - 10): ?>
		<?php if ($current_page != $total_pages - 10) echo '&hellip;' ?>
		<li><a href="#" id="pagination_<?php echo $total_pages; ?>" onClick="pagination(<?php echo $total_pages ?>); return false;"><?php echo $total_pages?></a></li>
	<?php endif ?>

</ul>