<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Layout variables
 * ---------------------
 *
 * @var  string   $item The item id number
 * @var  string   $link The link text
 * @var  string   $label The label text
 */
extract($displayData);

echo JHtml::_(
	'bootstrap.renderModal',
	'versionsModal',
	array(
		'url'    => $link,
		'title'  => $label,
		'height' => '300px',
		'width'  => '800px',
		'footer' => '<button type="button" class="btn" data-dismiss="modal">'
			. JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
	)
);

?>
<button type="button" onclick="jQuery('#versionsModal').modal('show')" class="btn" data-toggle="modal" title="<?php echo $label; ?>">
	<span class="icon-archive" aria-hidden="true"></span><?php echo $label; ?>
</button>
