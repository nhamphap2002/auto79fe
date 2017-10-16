<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Auto79
 * @author     Thang Tran <trantrongthang1207@gmail.com>
 * @copyright  2017 Thang Tran
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Auto79 records.
 *
 * @since  1.6
 */
class Auto79ModelArticless extends JModelList {

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see        JController
     * @since      1.6
     */
    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'ordering', 'a.ordering',
                'state', 'a.state',
                'created_by', 'a.created_by',
                'modified_by', 'a.modified_by',
                'link', 'a.link',
                'category_id', 'a.category_id',
                'province', 'a.province',
                'approval', 'a.approval',
                'timeapproval', 'a.timeapproval',
                'hasget', 'a.hasget',
                'hasapproval', 'a.hasapproval',
                'time_created', 'a.time_created',
                'cronid', 'a.cronid',
                'postid', 'a.postid',
                'user_approval', 'a.user_approval',
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   Elements order
     * @param   string  $direction  Order direction
     *
     * @return void
     *
     * @throws Exception
     *
     * @since    1.6
     */
    protected function populateState($ordering = null, $direction = null) {
        $app = JFactory::getApplication();
        $list = $app->getUserState($this->context . '.list');

        $ordering = isset($list['filter_order']) ? $list['filter_order'] : null;
        $direction = isset($list['filter_order_Dir']) ? $list['filter_order_Dir'] : null;

        $list['limit'] = (int) JFactory::getConfig()->get('list_limit', 20);
        $list['start'] = $app->input->getInt('start', 0);
        $list['ordering'] = $ordering;
        $list['direction'] = $direction;

        $app->setUserState($this->context . '.list', $list);
        $app->input->set('list', null);

        // List state information.
        parent::populateState($ordering, $direction);

        $app = JFactory::getApplication();

        $ordering = $app->getUserStateFromRequest($this->context . '.ordercol', 'filter_order', $ordering);
        $direction = $app->getUserStateFromRequest($this->context . '.orderdirn', 'filter_order_Dir', $ordering);

        $this->setState('list.ordering', $ordering);
        $this->setState('list.direction', $direction);

        $start = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'int');
        $limit = $app->getUserStateFromRequest($this->context . '.limit', 'limit', 0, 'int');

        if ($limit == 0) {
            $limit = $app->get('list_limit', 0);
        }

        $this->setState('list.limit', $limit);
        $this->setState('list.start', $start);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return   JDatabaseQuery
     *
     * @since    1.6
     */
    protected function getListQuery() {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query
                ->select(
                        $this->getState(
                                'list.select', 'DISTINCT a.*'
                        )
        );

        $query->from('`#__auto79_articles` AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS uEditor');
        $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

        // Join over the created by field 'created_by'
        $query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

        // Join over the created by field 'modified_by'
        $query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');

        if (!JFactory::getUser()->authorise('core.edit', 'com_auto79')) {
            $query->where('a.state = 1');
        }

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
            }
        }


        // Filtering category_id
        $filter_category_id = $this->state->get("filter.category_id");
        if ($filter_category_id != '') {
            $query->where("a.category_id = '" . $db->escape($filter_category_id) . "'");
        }

        // Filtering province
        $filter_province = $this->state->get("filter.province");
        if ($filter_province != '') {
            $query->where("a.province = '" . $db->escape($filter_province) . "'");
        }

        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');

        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
    }

    /**
     * Method to get an array of data items
     *
     * @return  mixed An array of data on success, false on failure.
     */
    public function getItems() {
        $items = parent::getItems();

        foreach ($items as $oneItem) {
            $oneItem->advertsid = $oneItem->postid;
            if (isset($oneItem->postid)) {
                $values = explode(',', $oneItem->postid);

                $textValue = array();
                foreach ($values as $value) {
                    if (!empty($value)) {
                        $db = JFactory::getDbo();
                        $query = "SELECT title FROM #__adverts79_adverts WHERE id =" . $value;
                        $db->setQuery($query);
                        $results = $db->loadObject();
                        if ($results) {
                            $textValue[] = $results->title;
                        }
                    }
                }

                $oneItem->postid = !empty($textValue) ? implode(', ', $textValue) : '';
            }

            if (isset($oneItem->category_id)) {
                $values = explode(',', $oneItem->category_id);

                $textValue = array();
                foreach ($values as $value) {
                    if (!empty($value)) {
                        $db = JFactory::getDbo();
                        $query = "SELECT title FROM #__adverts79_categories WHERE id =" . $value;
                        $db->setQuery($query);
                        $results = $db->loadObject();

                        if ($results) {
                            $textValue[] = $results->title;
                        }
                    }
                }

                $oneItem->category_id = !empty($textValue) ? implode(', ', $textValue) : $oneItem->category_id;
            }

            if (isset($oneItem->province)) {
                $values = explode(',', $oneItem->province);

                $textValue = array();
                foreach ($values as $value) {
                    if (!empty($value)) {
                        $db = JFactory::getDbo();
                        $query = "SELECT id, title FROM #__address79_province HAVING id LIKE '" . $value . "'";
                        $db->setQuery($query);
                        $results = $db->loadObject();

                        if ($results) {
                            $textValue[] = $results->title;
                        }
                    }
                }

                $oneItem->province = !empty($textValue) ? implode(', ', $textValue) : $oneItem->province;
            }
        }

        return $items;
    }

    /**
     * Overrides the default function to check Date fields format, identified by
     * "_dateformat" suffix, and erases the field if it's not correct.
     *
     * @return void
     */
    protected function loadFormData() {
        $app = JFactory::getApplication();
        $filters = $app->getUserState($this->context . '.filter', array());
        $error_dateformat = false;

        foreach ($filters as $key => $value) {
            if (strpos($key, '_dateformat') && !empty($value) && $this->isValidDate($value) == null) {
                $filters[$key] = '';
                $error_dateformat = true;
            }
        }

        if ($error_dateformat) {
            $app->enqueueMessage(JText::_("COM_AUTO79_SEARCH_FILTER_DATE_FORMAT"), "warning");
            $app->setUserState($this->context . '.filter', $filters);
        }

        return parent::loadFormData();
    }

    /**
     * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
     *
     * @param   string  $date  Date to be checked
     *
     * @return bool
     */
    private function isValidDate($date) {
        $date = str_replace('/', '-', $date);
        return (date_create($date)) ? JFactory::getDate($date)->format("Y-m-d") : null;
    }

}
