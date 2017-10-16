<?php

/**
 * @version    CVS: 1.0.1
 * @package    Com_Auto79
 * @author     Khánh Hòa 79 <info@khanhhoa79.vn>
 * @copyright  Khánh Hòa 79
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access.
defined('_JEXEC') or die;

/**
 * Adverts list controller class.
 *
 * @since  1.6
 */
class Auto79ControllerAdverts extends Auto79Controller {

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional
     * @param   array   $config  Configuration array for model. Optional
     *
     * @return object	The model
     *
     * @since	1.6
     */
    public function &getModel($name = 'Adverts', $prefix = 'Auto79Model', $config = array()) {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));

        return $model;
    }

    public function uptop() {
        $userid = JFactory::getUser()->id;
        if ($userid > 0) {
            $id = JRequest::getVar('id');
            $numupdate = JRequest::getVar('nd');
            $db = JFactory::getDbo();
            $date = JFactory::getDate();
            $query = $db->getQuery(true);
            $fields = array(
                $db->quoteName('time_updated') . ' = ' . $db->quote($date->toSql()),
                $db->quoteName('numupdate') . ' = ' . ($numupdate + 1)
            );
            $conditions = array(
                $db->quoteName('id') . ' = ' . $id,
            );
            $query->update($db->quoteName('#__adverts79_adverts'))->set($fields)->where($conditions);
            //echo $query;
            $db->setQuery($query);
            if ($db->execute()) {
                $mess['return'] = 1;
                $mess['msg'] = JText::_('COM_ADVERTS79_UP_TOP_SUCCESSFUL');
            } else {
                $mess['return'] = 0;
                $mess['msg'] = JText::_('COM_ADVERTS79_UP_TOP_SUCCESSFUL');
            }
        } else {
            $mess['return'] = 2;
            $mess['msg'] = JText::_('COM_ADVERTS79_MUST_LOGIN');
        }
        echo json_encode($mess);
        exit();
    }

    public function addwishlist() {
        $userid = JFactory::getUser()->id;
        if ($userid > 0) {
            $id = JRequest::getVar('id');
            $db = JFactory::getDbo();

            $date = JFactory::getDate();
            $query = $db->getQuery(true);
            $columns = array(
                'customer_id',
                'advert_id',
                'date_added'
            );

            $values = array(
                $_REQUEST['userid'],
                $_REQUEST['id'],
                $db->quote($date->toSql()),
            );

            $query
                    ->insert($db->quoteName('#__advert_wishlist'))
                    ->columns($db->quoteName($columns))
                    ->values(implode(',', $values));
            $db->setQuery($query);
            if ($db->execute()) {
                $mess['return'] = 1;
                $mess['msg'] = JText::_('COM_ADVERTS79_ADD_WISTLIST_SUCCESSFUL');
            } else {
                $mess['return'] = 0;
                $mess['msg'] = JText::_('COM_ADVERTS79_ADD_WISTLIST_UNSUCCESSFUL');
            }
        } else {
            $mess['return'] = 2;
            $mess['msg'] = JText::_('COM_ADVERTS79_MUST_LOGIN');
        }
        echo json_encode($mess);
        exit();
    }

    public function removewishlist() {
        $userid = JFactory::getUser()->id;
        if ($userid > 0) {
            $id = JRequest::getVar('id');
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $conditions = array(
                $db->quoteName('advert_id') . ' = ' . $id,
                $db->quoteName('customer_id') . ' = ' . $userid
            );
            $query->delete($db->quoteName('#__advert_wishlist'));
            $query->where($conditions);
            $db->setQuery($query);
            if ($db->execute()) {
                $mess['return'] = 1;
                $mess['msg'] = JText::_('COM_ADVERTS79_REMOVE_WISTLIST_SUCCESSFUL');
            } else {
                $mess['return'] = 0;
                $mess['msg'] = JText::_('COM_ADVERTS79_REMOVE_WISTLIST_SUCCESSFUL');
            }
        } else {
            $mess['return'] = 2;
            $mess['msg'] = JText::_('COM_ADVERTS79_MUST_LOGIN');
        }
        echo json_encode($mess);
        exit();
    }

    public function unpublishadverts() {
        $userid = JFactory::getUser()->id;
        if ($userid > 0) {
            $id = JRequest::getVar('id');
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $fields = array(
                $db->quoteName('state') . ' = ' . 0
            );
            $conditions = array(
                $db->quoteName('id') . ' = ' . $id,
            );
            $query->update($db->quoteName('#__adverts79_adverts'))->set($fields)->where($conditions);
            //echo $query;
            $db->setQuery($query);
            if ($db->execute()) {
                $mess['return'] = 1;
                $mess['msg'] = JText::_('COM_ADVERTS79_UNPUBLISH_SUCCESSFUL');
            } else {
                $mess['return'] = 0;
                $mess['msg'] = JText::_('COM_ADVERTS79_UNPUBLISH_UNSUCCESSFUL');
            }
        } else {
            $mess['return'] = 2;
            $mess['msg'] = JText::_('COM_ADVERTS79_MUST_LOGIN');
        }
        echo json_encode($mess);
        exit();
    }

    public function publishadverts() {
        $userid = JFactory::getUser()->id;
        if ($userid > 0) {
            $id = JRequest::getVar('id');
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $fields = array(
                $db->quoteName('state') . ' = ' . 1
            );
            $conditions = array(
                $db->quoteName('id') . ' = ' . $id,
            );
            $query->update($db->quoteName('#__adverts79_adverts'))->set($fields)->where($conditions);
            //echo $query;
            $db->setQuery($query);
            if ($db->execute()) {
                $mess['return'] = 1;
                $mess['msg'] = JText::_('COM_ADVERTS79_PUBLISH_SUCCESSFUL');
            } else {
                $mess['return'] = 0;
                $mess['msg'] = JText::_('COM_ADVERTS79_PUBLISH_UNSUCCESSFUL');
            }
        } else {
            $mess['return'] = 2;
            $mess['msg'] = JText::_('COM_ADVERTS79_MUST_LOGIN');
        }
        echo json_encode($mess);
        exit();
    }

    public function deleteimg() {
        $userid = JFactory::getUser()->id;
        $id = JRequest::getVar('id');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $fields = array(
            $db->quoteName('advert_images') . ' = ' . $db->quote(implode(',', $_REQUEST['img']))
        );
        $conditions = array(
            $db->quoteName('id') . ' = ' . $id,
        );
        $query->update($db->quoteName('#__adverts79_adverts'))->set($fields)->where($conditions);
        //echo $query;
        $db->setQuery($query);
        if ($db->execute()) {
            $oldFile = JPATH_ROOT . '/images/adverts79/' . $_REQUEST['imgdelete'];
            if (file_exists($oldFile) && !is_dir($oldFile)) {
                unlink($oldFile);
            }
            $mess['return'] = 1;
            $mess['img'] = implode(',', $_REQUEST['img']);
            $mess['msg'] = JText::_('COM_ADVERTS79_DELETE_SUCCESSFUL');
        } else {
            $mess['return'] = 0;
            $mess['msg'] = JText::_('COM_ADVERTS79_DELETE_UNSUCCESSFUL');
        }
        echo json_encode($mess);
        exit();
    }

}
