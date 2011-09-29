<?php
/**
 * Category helper. Displays categories on the front-end.
 *
 * @package    Category
 * @author     Ushahidi Team
 * @copyright  (c) 2008 Ushahidi Team
 * @license    http://www.ushahidi.com/license.html
 */
class groups_category_Core {

	
	/**
	 * Display category tree with input checkboxes.
	 */
	public static function tree($categories, array $selected_categories, $form_field, $columns = 1)
	{
		$html = '';

		// Validate columns
		$columns = (int) $columns;
		if ($columns == 0)
		{
			$columns = 1;
		}

		$categories_total = $categories->count();

		// Format categories for column display.
		$this_col = 1; // column number
		$maxper_col = round($categories_total/$columns); // Maximum number of elements per column
		$i = 1;  // Element Count
		foreach ($categories as $category)
		{

			// If this is the first element of a column, start a new UL
			if ($i == 1)
			{
				$html .= '<ul id="category-column-'.$this_col.'">';
			}

			// Display parent category.
			$html .= '<li style="width:100%;">';
			$html .= groups_category::display_category_checkbox($category, $selected_categories, $form_field);
			if(!$category->category_visible)
			{
				$html .= " - ".Kohana::lang("simplegroups.not_visible");
			}

			// Display child categories.
			if ($category->children->count() > 0)
			{
				$html .= '<ul>';
				foreach ($category->children as $child)
				{
					$html .= '<li>';
					$html .= groups_category::display_category_checkbox($child, $selected_categories, $form_field);
					if(!$child->category_visible)
					{
						$html .= " - ".Kohana::lang("simplegroups.not_visible");
					}
				}
				$html .= '</ul>';
			}
			$i++;

			// If this is the last element of a column, close the UL
			if ($i > $maxper_col || $i == $categories_total)
			{
				$html .= '</ul>';
				$i = 1;
				$this_col++;
			}
		}

		return $html;
	}
	
	
	/**
	 * Displays a single category checkbox.
	 */
	public static function display_category_checkbox($category, $selected_categories, $form_field, $enable_parents = FALSE)
	{
		$html = '';

		$cid = $category->id;

		// Get locale
		$l = Kohana::config('locale.language.0');
		
		

		$category_title = $category->category_title;
		$category_color = $category->category_color;

		// Category is selected.
		$category_checked = in_array($cid, $selected_categories);
		
		// Visible Child Count
		$vis_child_count = 0;
		foreach ($category->children as $child)
		{
			$child_visible = $child->category_visible;
			if ($child_visible)
			{
				// Increment Visible Child count
				++$vis_child_count;
			}
		}

		$disabled = "";
		if (!$enable_parents AND $category->children->count() > 0 AND $vis_child_count >0)
		{
			$disabled = " disabled=\"disabled\"";	
		}

		$html .= form::checkbox($form_field.'[]', $cid, $category_checked, ' class="check-box"'.$disabled);
		$html .= $category_title;

		return $html;
	}
}
