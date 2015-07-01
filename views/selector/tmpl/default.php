<?php
/**
 * @package		HUBzero CMS
 * @author		Alissa Nedossekina <alisa@purdue.edu>
 * @copyright	Copyright 2005-2009 by Purdue Research Foundation, West Lafayette, IN 47906
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 *
 * Copyright 2005-2009 by Purdue Research Foundation, West Lafayette, IN 47906.
 * All rights reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License,
 * version 2 as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// No direct access
defined('_HZEXEC_') or die();

if (!$this->ajax)
{
	$this->css('selector');
}

// Get attachment type model
$attModel = new \Components\Publications\Models\Attachments($this->database);

// Filter URL
$filterUrl = Route::url( $this->publication->link('editversionid') . '&active=files&action=filter&p=' . $this->props . '&ajax=1&no_html=1');

$elId 	 = $this->element;

// Get requirements
$element = $this->publication->curation('blocks', $this->step, 'elements', $this->element);
$params  = $element->params;
$max 	 = $params->max;
$min 	 = $params->min;
$required= $params->required;
$role 	 = $params->role;
$allowed = $params->typeParams->allowed_ext;
$reqext  = $params->typeParams->required_ext;
$reuse   = isset($params->typeParams->reuse) ? $params->typeParams->reuse : 1;

$minName = \Components\Projects\Helpers\Html::getNumberName($min);
$maxName = \Components\Projects\Helpers\Html::getNumberName($max);

// Spell out requirement
$req = Lang::txt('PLG_PROJECTS_FILES_SELECTOR_CHOOSE') . ' ';
if ($min && $max > $min)
{
	if ($max > 100)
	{
		// Do not say how many
		$req .= '<strong>' . $minName . ' ' . Lang::txt('PLG_PROJECTS_FILES_SELECTOR_OR_MORE') . '</strong>';
	}
	else
	{
		$req .= '<strong>' . $min . '-' . $max . ' ' . Lang::txt('PLG_PROJECTS_FILES_SELECTOR_FILES') . '</strong>';
	}
}
elseif ($min && $min == $max)
{
	$req .= ' <strong>' . $minName . ' ' . Lang::txt('PLG_PROJECTS_FILES_SELECTOR_FILE');
	$req .= $min > 1 ? 's' : '';
	$req .= '</strong>';
}
else
{
	$req .= $max == 1 ? Lang::txt('PLG_PROJECTS_FILES_SELECTOR_COUNT', $max) : Lang::txt('PLG_PROJECTS_FILES_S');
}

if (!empty($allowed))
{
	$req .= ' ' . Lang::txt('PLG_PROJECTS_FILES_SELECTOR_OF_FORMAT');
	$req .= count($allowed) > 1 ? 's - ' : ' - ';
	$x = 1;
	foreach ($allowed as $al)
	{
		$req .= '.' . strtoupper($al);
		$req .= $x == count($allowed) ? '' : ', ';
		$x++;
	}
}
else
{
	$req .= ' ' . Lang::txt('PLG_PROJECTS_FILES_SELECTOR_OF_ANY_TYPE');
}
$req .= ':';

// Get attached items
$attachments = $this->publication->attachments();
$attachments = isset($attachments['elements'][$elId]) ? $attachments['elements'][$elId] : NULL;
$attachments = $attModel->getElementAttachments($elId, $attachments, $params->type);

$used = array();
if (!$reuse && $this->publication->_attachments['elements'])
{
	foreach ($this->publication->_attachments['elements'] as $o => $elms)
	{
		if ($o != $elId)
		{
			foreach ($elms as $elm)
			{
				$used[] = $elm->path;
			}
		}
	}
}

// Get preselected items
$selected = array();
if ($attachments)
{
	foreach ($attachments as $attach)
	{
		$selected[] = $attach->path;
	}
}

// Refreshing file list
if ($this->task == 'filter')
{
	// Show files
	$view = new \Hubzero\Plugin\View(
		array(
			'folder'	=>'projects',
			'element'	=>'files',
			'name'		=>'selector',
			'layout'	=>'selector'
		)
	);
	$view->option 		= $this->option;
	$view->model 		= $this->model;
	$view->items		= $this->items;
	$view->showLevels 	= $this->filter ? false : true;
	$view->requirements = $params;
	$view->publication  = $this->publication;
	$view->selected		= $selected;
	$view->allowed		= $allowed;
	$view->used			= $used;
	echo $view->loadTemplate();

	return;
}

// Get folder array
$subdirOptions = array();
$subdirOptions[] = array('path' => '', 'label' => 'home directory');
if ($this->items)
{
	foreach ($this->items as $item)
	{
		if ($item->get('type') == 'folder')
		{
			$subdirOptions[] = array('path' => $item->get('localPath'), 'label' => $item->get('localPath'));
		}
	}
}

?>
<script src="<?php echo rtrim(Request::base(true), '/'); ?>/core/plugins/projects/files/assets/js/fileselector.js"></script>
<div id="abox-content">
<h3><?php echo Lang::txt('PLG_PROJECTS_FILES_SELECTOR'); ?> 	<span class="abox-controls">
		<a class="btn btn-success active" id="b-filesave"><?php echo Lang::txt('PLG_PROJECTS_FILES_SELECTOR_SAVE_SELECTION'); ?></a>
		<?php if ($this->ajax) { ?>
		<a class="btn btn-cancel" id="cancel-action"><?php echo Lang::txt('PLG_PROJECTS_FILES_CANCEL'); ?></a>
		<?php } ?>
	</span></h3>
<form id="select-form" class="select-form" method="post" enctype="multipart/form-data" action="<?php echo Route::url( $this->publication->link('edit')); ?>">
	<fieldset >
		<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
		<input type="hidden" name="id" value="<?php echo $this->model->get('id'); ?>" />
		<input type="hidden" name="version" value="<?php echo $this->publication->get('version_number'); ?>" />
		<input type="hidden" name="ajax" value="<?php echo $this->ajax; ?>" />
		<input type="hidden" id="selecteditems" name="selecteditems" value="" />
		<input type="hidden" id="maxitems" name="maxitems" value="<?php echo $max; ?>" />
		<input type="hidden" id="minitems" name="minitems" value="<?php echo $min; ?>" />
		<input type="hidden" id="p" name="p" value="<?php echo $this->props; ?>" />
		<input type="hidden" id="filterUrl" name="filterUrl" value="<?php echo $filterUrl; ?>" />
		<input type="hidden" name="pid" value="<?php echo $this->publication->get('id'); ?>" />
		<input type="hidden" name="vid" value="<?php echo $this->publication->get('version_id'); ?>" />
		<input type="hidden" name="section" value="<?php echo $this->block; ?>" />
		<input type="hidden" name="element" value="<?php echo $elId; ?>" />
		<input type="hidden" name="el" value="<?php echo $elId; ?>" />
		<input type="hidden" name="step" value="<?php echo $this->step; ?>" />
		<input type="hidden" name="active" value="publications" />
		<input type="hidden" name="action" value="apply" />
		<input type="hidden" name="move" value="continue" />
		<?php if ($this->model->isProvisioned()) { ?>
			<input type="hidden" name="task" value="submit" />
			<input type="hidden" name="ajax" value="0" />
		<?php }  ?>
	</fieldset>
	<div id="search-filter" class="search-filter">
		<label><input type="text" value="<?php echo $this->filter; ?>" name="filter" id="item-search" /></label>
	</div>

	<p class="requirement" id="req"><?php echo $req; ?></p>
	<div id="content-selector" class="content-selector">
		<?php
			// Show files
			$view = new \Hubzero\Plugin\View(
				array(
					'folder'	=>'projects',
					'element'	=>'files',
					'name'		=>'selector',
					'layout'	=>'selector'
				)
			);
			$view->option 		= $this->option;
			$view->model 		= $this->model;
			$view->items		= $this->items;
			$view->showLevels 	= $this->filter ? false : true;
			$view->requirements = $params;
			$view->publication  = $this->publication;
			$view->selected		= $selected;
			$view->allowed		= $allowed;
			$view->used			= $used;
			echo $view->loadTemplate();
		?>
	</div>
	</form>
	<form id="upload-form" class="upload-form" method="post" enctype="multipart/form-data" action="<?php echo Route::url('index.php?option=' . $this->option . '&alias=' . $this->model->get('alias')); ?>">

	<fieldset >
		<input type="hidden" name="id" value="<?php echo $this->model->get('id'); ?>" />
		<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
		<input type="hidden" name="ajax" value="<?php echo $this->ajax; ?>" />
		<input type="hidden" name="pid" value="<?php echo $this->publication->id; ?>" />
		<input type="hidden" name="vid" value="<?php echo $this->publication->version_id; ?>" />
		<input type="hidden" name="alias" value="<?php echo $this->model->get('alias'); ?>" />
		<input type="hidden" name="active" value="files" />
		<input type="hidden" name="action" value="save" />
		<input type="hidden" name="json" value="1" />
		<input type="hidden" name="ajax" value="1" />
		<input type="hidden" name="no_html" value="1" />
	</fieldset>
	<div id="status-box"></div>
	<?php if ($this->model->isProvisioned()) { ?>
		<input type="hidden" name="provisioned" id="provisioned" value="1" />
		<input type="hidden" name="task" value="submit" />
	<div class="asset-uploader">
		<h5 class="add"><?php echo Lang::txt('PLG_PROJECTS_FILES_PROV_UPLOAD'); ?>
			<span class="abox-controls">
				<input type="submit" value="<?php echo Lang::txt('PLG_PROJECTS_FILES_UPLOAD_NOW'); ?>" class="btn btn-success" id="f-upload"  />
			</span>
		</h5>
		<div id="ajax-uploader" data-action="<?php echo Route::url( $this->publication->link('edit') . '&active=files&action=save&no_html=1&ajax=1'); ?>" >
			<label class="addnew">
				<input name="upload[]" type="file" class="option uploader" id="uploader" multiple="multiple" />
			</label>
			<div id="upload-body">
				<ul id="u-selected" class="qq-upload-list">
				</ul>
			</div>
		</div>
	</div>
	<?php } else { ?>
	<div id="quick-upload" class="quick-uploader">
		<p><?php echo Lang::txt('PLG_PROJECTS_FILES_SELECTOR_NEED_ADD_FILES'); ?> <?php echo Lang::txt('PLG_PROJECTS_FILES_SELECTOR_QUICK_UPLOAD'); ?>:</p>

		<label>
			<input name="upload[]" type="file" id="uploader" multiple="multiple" />
		</label>

		<?php if (count($subdirOptions) > 1) { ?>
		<label><?php echo Lang::txt('PLG_PROJECTS_FILES_UPLOAD_INTO_SUBDIR'); ?>
			<select name="subdir">
				<?php foreach ($subdirOptions as $sd) { ?>
					<option value="<?php echo $sd['path']; ?>"><?php echo $sd['label']; ?></options>
				<?php } ?>
			</select>
		</label>
		<?php } ?>
		<input type="submit" value="<?php echo Lang::txt('PLG_PROJECTS_FILES_UPLOAD'); ?>" class="upload-file" id="upload-file" />
	</div>
	<?php } ?>
	</form>
</div>