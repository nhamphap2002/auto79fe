<?php

/**
 * @version    CVS: 1.0.1
 * @package    Com_Auto79
 * @author     Khánh Hòa 79 <info@khanhhoa79.vn>
 * @copyright  Khánh Hòa 79
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Auto79 records.
 *
 * @since  1.6
 */
class Auto79ModelAdverts extends JModelList {

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
                'ordering', 'a.ordering',
                'state', 'a.state',
                'title', 'a.title',
                'alias', 'a.alias',
                'category_id', 'a.category_id',
                'add_province', 'a.add_province',
                'add_district', 'a.add_district',
                'advert_type', 'a.advert_type',
                'advert_sellertype', 'a.advert_sellertype',
                'advert_condition', 'a.advert_condition',
                'advert_price', 'a.advert_price',
                'advert_price_unit', 'a.advert_price_unit',
                'advert_price_negotiable', 'a.advert_price_negotiable',
                'advert_description', 'a.advert_description',
                'advert_images', 'a.advert_images',
                'advert_delivery', 'a.advert_delivery',
                'advert_vip', 'a.advert_vip',
                'advert_vipdate', 'a.advert_vipdate',
                'created_by', 'a.created_by',
                'modified_by', 'a.modified_by',
                'time_created', 'a.time_created',
                'time_updated', 'a.time_updated',
                'advert_views', 'a.advert_views',
                'id', 'a.id',
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
        $userid = JFactory::getUser()->id;

        // Select the required fields from the table.
        $query
                ->select(
                        $this->getState(
                                'list.select', 'DISTINCT a.id, a.title, a.time_created, a.category_id, a.state'
                        )
        );

        $query->from('`#__adverts79_adverts` AS a');

        $query->select('userApproval.link AS link');
        $query->join('INNER', '#__auto79_articles AS userApproval ON a.id = userApproval.postid');
        //$query->where('userApproval.user_approval = ' . $userid);
        
        $query->where('a.state = 0');
        
        $app = JFactory::getApplication();
        $getTemplate = $app->getTemplate('template');

        $province = $getTemplate->params['province'];
        if ($province) {
            $query->where('a.add_province = ' . $province);
        }

        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        if ($orderCol == '') {
            $orderCol = "a.time_updated";
        }
        if ($orderDirn == '') {
            $orderDirn = "DESC";
        }
        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }
//        echo $query;
//        exit();
        return $query;
    }

    /**
     * Method to get an array of data items
     *
     * @return  mixed An array of data on success, false on failure.
     */
    public function getItems() {

        $store = $this->getStoreId();
        $app = JFactory::getApplication();
        $limit = $app->get('list_limit');
        // Try to load the data from internal storage.
        if (isset($this->cache[$store])) {
            return $this->cache[$store];
        }
        $query = $this->getListQuery();
        $this->query = $query;

        $db = JFactory::getDbo();
        $db->setQuery($query, JRequest::getInt('start', 0), $limit);
        $items = $db->loadObjectList();
//        $this->cache[$store] = $rows;
//        $this->items = $rows;
//        $items = array_slice($rows, JRequest::getInt('start', 0), $limit);

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
            $app->enqueueMessage(JText::_("COM_ADVERTS79_SEARCH_FILTER_DATE_FORMAT"), "warning");
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

    public function getPagination() {
        // Get a storage key.
        $store = $this->getStoreId('getPagination');

        // Try to load the data from internal storage.
        if (isset($this->cache[$store])) {
            return $this->cache[$store];
        }

        // Create the pagination object.
        jimport('joomla.html.pagination');
        //$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
        $app = JFactory::getApplication();
        $limit = $app->get('list_limit');
        $page = new JPagination($this->getTotal(), JRequest::getInt('start', 0), $limit);

        // Add the object to the internal cache.
        $this->cache[$store] = $page;

        return $this->cache[$store];
    }

    function getTotal() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $userid = JFactory::getUser()->id;
        $query->select('COUNT(a.id) AS NumberOfAdvert');
        $query->from('`#__adverts79_adverts` AS a');
        $query->join('INNER', '#__auto79_articles AS userApproval ON a.id = userApproval.postid');
        //$query->where('userApproval.user_approval = ' . $userid);
        $db->setQuery($query);
        if ($total = $db->loadResult()) {
            return $total;
        } else {
            return 0;
        }
    }

}
