<?php
/**
 * @version    CVS: 1.0.1
 * @package    Com_Job79
 * @author     Khánh Hòa 79 <info@khanhhoa79.vn>
 * @copyright  Khánh Hòa 79
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$user = JFactory::getUser();
$userId = $user->get('id');
?>
<div class="portlet-body">
    <?php foreach ($this->items as $i => $item) : ?>
        <div class="row icon79">
            <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                <span class="tooltips" data-original-title="Tiêu đề"><i class="fa fa-bullseye"></i><a href="<?php echo JRoute::_('rao-vat/' . $item->id . '-' . Adverts79HelpersAdverts79::vn_to_str($item->title) . '.html'); ?>"><?php echo $this->escape($item->title); ?></a></span> <small><span class="tooltips font-red-mint" data-original-title="Ngày đăng">(<?php echo Adverts79HelpersAdverts79::formatDate($item->time_created, 'd/m/Y') ?>)</span></small>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                <?php
                if ($item->category_id) {
                    $category = Adverts79HelpersAdverts79::getCategories($item->category_id);
                    if (count($category) > 0) {
                        ?>
                        <span class="tooltips" data-original-title="Chuyên mục">
                            <i class="fa fa-list"></i>                                        
                            <a href="<?php echo JRoute::_('rao-vat/chuyen-muc-' . $category->alias . '.html'); ?>">
                                <?php echo $category->title; ?>
                            </a>
                        </span>
                        <?php
                    }
                }
                ?>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
                <span class="tooltips" data-original-title="Xem website"><i class="fa fa-dollar"></i><a href="<?php echo $this->escape($item->link); ?>" target="_blank">Link gốc</a></span>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6 text-right actions">
                <a href="<?php echo JRoute::_('/index.php?option=com_adverts79&task=adverts.unpublishadverts&id=' . $item->id, false, 2); ?>" class="tooltips badge advertspublish <?php echo $item->state ? '' : 'hide' ?>" data-original-title="Bỏ kích hoạt"><i class="fa fa-check-square-o"></i></a>
                <a href="<?php echo JRoute::_('/index.php?option=com_adverts79&task=adverts.publishadverts&id=' . $item->id, false, 2); ?>" class="tooltips badge advertspublish <?php echo $item->state ? 'hide' : '' ?>" data-original-title="Kích hoạt"><i class="fa fa-square-o"></i></a>
                <a class="tooltips badge" data-original-title="Chỉnh sửa" href="<?php echo JRoute::_('/index.php?option=com_adverts79&task=adverts79form.edit&id=' . $item->id, false, 2); ?>"><i class="fa fa-pencil"></i></a>
                <a class="tooltips badge" data-original-title="Xóa tin" href="<?php echo JRoute::_('/index.php?option=com_adverts79&task=adverts79form.remove&id=' . $item->id, false, 2); ?>"><i class="fa fa-trash"></i></a>
            </div>
        </div>
        <hr>
    <?php endforeach; ?>

    <div class="table-responsive">
        <div>
            <?php
            if (count($this->items) > 0)
                echo $this->pagination->getListFooter();
            ?>
        </div>
    </div>
</div>


