<?php
/**
 * @package		HUBzero CMS
 * @author		Alissa Nedossekina <alisa@purdue.edu>
 * @copyright	Copyright 2005-2009 HUBzero Foundation, LLC.
 * @license		http://opensource.org/licenses/MIT MIT
 *
 * Copyright 2005-2009 HUBzero Foundation, LLC.
 * All rights reserved.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css()
     ->js();

// Project creator?
$creator = $this->model->access('owner') ? 1 : 0;

$i = 0;
?>
<div id="plg-header">
	<h3 class="files"><a href="<?php echo Route::url($this->model->link('files')); ?>"><?php echo $this->title; ?></a> &raquo; <span class="subheader"><?php echo Lang::txt('PLG_PROJECTS_FILES_CONNECT'); ?></span></h3>
</div>

<p><?php echo Lang::txt('PLG_PROJECTS_FILES_CONNECT_EXPLAIN'); ?></p>
<div id="connections">
	<div class="aside">
		<p class="hint"><?php echo Lang::txt('PLG_PROJECTS_FILES_CONNECT_ABOUT'); ?></p>
	</div>
	<div class="subject">
	<?php foreach ($this->services as $servicename)
	{
		$service 	= $this->connect->getConfigs($servicename, false);
		$connected 	= $this->oparams->get($servicename . '_token') ? 1 : 0;

		$service['active'] 	= $this->params->get($servicename . '_token');

		$allowed = ($creator || $service['active']) ? 1 : 0;

		if (!$service['active'])
		{
			$connected = 0;
		}

		$objO = $this->model->table('Owner');
		$numConnected = $objO->getConnected($this->model->get('id'), $servicename);
		$teamCount = $objO->countOwners($this->model->get('id'));

		// Skip unavailable services entirely
		if (!$service['on'])
		{
			continue;
		}

		$openUrl = $servicename == 'google'
			? 'https://drive.google.com/?authuser=0#folders/' . $service['remote_dir_id'] : '';

	?>
	<div class="connect-service <?php echo !$service['on'] ? 'inactive' : ''; ?> <?php echo $servicename; ?>">
		<?php if ($service['on'] && $allowed) { ?>
		<div class="connect-info">
			<?php if ($connected && $service['active']) { ?>
				<p><span class="connected"><?php echo ucfirst(Lang::txt('PLG_PROJECTS_FILES_CONNECT_CONNECTED')); ?></span></p>
				<p><?php echo $this->oparams->get($servicename . '_email'); ?></p>
			<?php } else { ?>
				<p class="connect-action"><a href="<?php echo Route::url($this->model->link('files') . '&action=connect&service=' . $servicename); ?>"><?php echo Lang::txt('PLG_PROJECTS_FILES_CONNECT'); ?></a></p>
			<?php } ?>
		</div>
		<?php } ?>
		<div class="service-info">
			<h5><?php echo $service['servicename']; ?></h5>
			<?php if (!$service['on']) { ?>
			<p><?php echo Lang::txt('PLG_PROJECTS_FILES_CONNECT_SERVICE_OFF'); ?></p>
			<?php }
			 elseif ($service['active'] || $connected) { ?>
			<p class="green prominent"><?php echo Lang::txt('PLG_PROJECTS_FILES_CONNECT_SERVICE_ACTIVE'); ?></p>
			<p><span class="prominent darker"><?php echo count($numConnected) . ' ' . Lang::txt('COM_PROJECTS_OUT_OF') . ' ' . $teamCount . ' ' . Lang::txt('COM_PROJECTS_TEAM_MEMBERS') . ' ' . Lang::txt('PLG_PROJECTS_FILES_CONNECTED'); ?></span></p>
			<p>
				<span><?php echo Lang::txt('PLG_PROJECTS_FILES_CONNECT_REMOTE_DIR'); ?>:</span> <span class="prominent darker"><?php echo $service['remote_dir']; ?></span> <?php if ($connected && $openUrl) { ?><span><a href="<?php echo $openUrl; ?>" rel="external">[open]</a></span><?php } ?>
			</p>

			<?php if ($connected) { ?>
			<?php $removeData = $creator ? '&removedata=1' : '';  ?>
			<p>
				<span class=" <?php echo $creator ? ' creator' : ''; ?>">
					<a href="<?php echo Route::url($this->model->link('files') . '&action=disconnect&service=' . $servicename . $removeData); ?>" id="disconnect"><?php echo ucfirst(Lang::txt('PLG_PROJECTS_FILES_CONNECT_DISCONNECT')); ?> &raquo;</a>
				</span>
				&nbsp; &nbsp;
				<span>
					<a href="<?php echo Route::url($this->model->link('files') . '&action=connect&reauth=1&service=' . $servicename); ?>"><?php echo Lang::txt('PLG_PROJECTS_FILES_CONNECT_REAUTH'); ?> &raquo;</a>
				</span>
			</p>
			<?php } ?>
			<?php }
			 else { ?>
			<p><?php echo $creator ? Lang::txt('PLG_PROJECTS_FILES_CONNECT_SERVICE_INACTIVE_CREATOR')
				: Lang::txt('PLG_PROJECTS_FILES_CONNECT_SERVICE_INACTIVE'); ?></p>
			<?php } ?>
		</div>
	</div>
	<?php $i++; } ?>

	<?php if ($i == 1 && count($this->services) > 1) {
		// There may be more services available in the future ?>
		<div class="connect-service infuture">
			<div class="service-info">
				<h5><?php echo Lang::txt('PLG_PROJECTS_FILES_CONNECT_MORE_TO_COME'); ?></h5>
				<p><?php echo Lang::txt('PLG_PROJECTS_FILES_CONNECT_MORE_TO_COME_EXPLAIN'); ?></p>
			</div>
		</div>
	<?php } ?>
	</div>
	<div class="clear"></div>
</div>
