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

$canEdit = JFactory::getUser()->authorise('core.edit', 'com_auto79');

if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_auto79'))
{
	$canEdit = JFactory::getUser()->id == $this->item->created_by;
}
?>

<div class="item_fields">

	<table class="table">
		

		<tr>
			<th><?php echo JText::_('COM_AUTO79_FORM_LBL_CRON_LINK'); ?></th>
			<td><?php echo $this->item->link; ?></td>
		</tr>

		<tr>
			<th><?php echo JText::_('COM_AUTO79_FORM_LBL_CRON_ADCATEGORIES'); ?></th>
			<td><?php echo $this->item->adcategories; ?></td>
		</tr>

		<tr>
			<th><?php echo JText::_('COM_AUTO79_FORM_LBL_CRON_PAGETO'); ?></th>
			<td><?php echo $this->item->pageto; ?></td>
		</tr>

		<tr>
			<th><?php echo JText::_('COM_AUTO79_FORM_LBL_CRON_PAGEFROM'); ?></th>
			<td><?php echo $this->item->pagefrom; ?></td>
		</tr>

	</table>

</div>

<?php if($canEdit && $this->item->checked_out == 0): ?>

	<a class="btn" href="<?php echo JRoute::_('index.php?option=com_auto79&task=cron.edit&id='.$this->item->id); ?>"><?php echo JText::_("COM_AUTO79_EDIT_ITEM"); ?></a>

<?php endif; ?>

<?php if (JFactory::getUser()->authorise('core.delete','com_auto79.cron.'.$this->item->id)) : ?>

	<a class="btn btn-danger" href="#deleteModal" role="button" data-toggle="modal">
		<?php echo JText::_("COM_AUTO79_DELETE_ITEM"); ?>
	</a>

	<div id="deleteModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="deleteModal" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3><?php echo JText::_('COM_AUTO79_DELETE_ITEM'); ?></h3>
		</div>
		<div class="modal-body">
			<p><?php echo JText::sprintf('COM_AUTO79_DELETE_CONFIRM', $this->item->id); ?></p>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal">Close</button>
			<a href="<?php echo JRoute::_('index.php?option=com_auto79&task=cron.remove&id=' . $this->item->id, false, 2); ?>" class="btn btn-danger">
				<?php echo JText::_('COM_AUTO79_DELETE_ITEM'); ?>
			</a>
		</div>
	</div>

<?php endif; ?>