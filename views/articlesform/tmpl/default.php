<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Auto79
 * @author     Thang Tran <trantrongthang1207@gmail.com>
 * @copyright  2017 Thang Tran
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

// Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_auto79', JPATH_SITE);
$doc = JFactory::getDocument();
$doc->addScript(JUri::base() . '/media/com_auto79/js/form.js');

$user    = JFactory::getUser();
$canEdit = Auto79HelpersAuto79::canUserEdit($this->item, $user);


?>

<div class="articles-edit front-end-edit">
	<?php if (!$canEdit) : ?>
		<h3>
			<?php throw new Exception(JText::_('COM_AUTO79_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
		</h3>
	<?php else : ?>
		<?php if (!empty($this->item->id)): ?>
			<h1><?php echo JText::sprintf('COM_AUTO79_EDIT_ITEM_TITLE', $this->item->id); ?></h1>
		<?php else: ?>
			<h1><?php echo JText::_('COM_AUTO79_ADD_ITEM_TITLE'); ?></h1>
		<?php endif; ?>

		<form id="form-articles"
			  action="<?php echo JRoute::_('index.php?option=com_auto79&task=articles.save'); ?>"
			  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
			
	<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />

	<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />

	<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />

	<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />

	<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />

				<?php echo $this->form->getInput('created_by'); ?>
				<?php echo $this->form->getInput('modified_by'); ?>
	<?php echo $this->form->renderField('link'); ?>

	<?php foreach((array)$this->item->link as $value): ?>
		<?php if(!is_array($value)): ?>
			<input type="hidden" class="link" name="jform[linkhidden][<?php echo $value; ?>]" value="<?php echo $value; ?>" />';
		<?php endif; ?>
	<?php endforeach; ?>
	<?php echo $this->form->renderField('category_id'); ?>

	<?php foreach((array)$this->item->category_id as $value): ?>
		<?php if(!is_array($value)): ?>
			<input type="hidden" class="category_id" name="jform[category_idhidden][<?php echo $value; ?>]" value="<?php echo $value; ?>" />';
		<?php endif; ?>
	<?php endforeach; ?>
	<?php echo $this->form->renderField('province'); ?>

	<?php foreach((array)$this->item->province as $value): ?>
		<?php if(!is_array($value)): ?>
			<input type="hidden" class="province" name="jform[provincehidden][<?php echo $value; ?>]" value="<?php echo $value; ?>" />';
		<?php endif; ?>
	<?php endforeach; ?>
	<?php echo $this->form->renderField('approval'); ?>

	<?php echo $this->form->renderField('timeapproval'); ?>

	<?php echo $this->form->renderField('hasget'); ?>

	<?php echo $this->form->renderField('hasapproval'); ?>

	<?php echo $this->form->renderField('time_created'); ?>

	<?php echo $this->form->renderField('cronid'); ?>

	<?php echo $this->form->renderField('postid'); ?>

	<?php echo $this->form->renderField('user_approval'); ?>

			<div class="control-group">
				<div class="controls">

					<?php if ($this->canSave): ?>
						<button type="submit" class="validate btn btn-primary">
							<?php echo JText::_('JSUBMIT'); ?>
						</button>
					<?php endif; ?>
					<a class="btn"
					   href="<?php echo JRoute::_('index.php?option=com_auto79&task=articlesform.cancel'); ?>"
					   title="<?php echo JText::_('JCANCEL'); ?>">
						<?php echo JText::_('JCANCEL'); ?>
					</a>
				</div>
			</div>

			<input type="hidden" name="option" value="com_auto79"/>
			<input type="hidden" name="task"
				   value="articlesform.save"/>
			<?php echo JHtml::_('form.token'); ?>
		</form>
	<?php endif; ?>
</div>
