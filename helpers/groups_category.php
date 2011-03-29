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
			$html .= category::display_category_checkbox($category, $selected_categories, $form_field);
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
					$html .= category::display_category_checkbox($child, $selected_categories, $form_field);
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
}
