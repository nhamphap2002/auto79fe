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

if (!function_exists('file_get_html') && !function_exists('str_get_html') && !function_exists('dump_html_tree') && !class_exists('simple_html_dom_node')) {
    include_once dirname(dirname(__FILE__)) . '/helpers/simple_html_dom.php';
}

/**
 * Cron controller class.
 *
 * @since  1.6
 * 
 */
//*/30	*	*	*	*	/usr/bin/wget -O /dev/null "http://clone.khanhhoa79.vn/index.php?option=com_auto79&view=cron&task=cron.cronlinkpost&id=1"

class Auto79ControllerCron extends JControllerLegacy {

    /**
     * Method to check out an item for editing and redirect to the edit form.
     *
     * @return void
     *
     * @since    1.6
     */
    public function edit() {
        $app = JFactory::getApplication();

        // Get the previous edit id (if any) and the current edit id.
        $previousId = (int) $app->getUserState('com_auto79.edit.cron.id');
        $editId = $app->input->getInt('id', 0);

        // Set the user id for the user to edit in the session.
        $app->setUserState('com_auto79.edit.cron.id', $editId);

        // Get the model.
        $model = $this->getModel('Cron', 'Auto79Model');

        // Check out the item
        if ($editId) {
            $model->checkout($editId);
        }

        // Check in the previous user.
        if ($previousId && $previousId !== $editId) {
            $model->checkin($previousId);
        }

        // Redirect to the edit screen.
        $this->setRedirect(JRoute::_('index.php?option=com_auto79&view=cronform&layout=edit', false));
    }

    /**
     * Method to save a user's profile data.
     *
     * @return    void
     *
     * @throws Exception
     * @since    1.6
     */
    public function publish() {
        // Initialise variables.
        $app = JFactory::getApplication();

        // Checking if the user can remove object
        $user = JFactory::getUser();

        if ($user->authorise('core.edit', 'com_auto79') || $user->authorise('core.edit.state', 'com_auto79')) {
            $model = $this->getModel('Cron', 'Auto79Model');

            // Get the user data.
            $id = $app->input->getInt('id');
            $state = $app->input->getInt('state');

            // Attempt to save the data.
            $return = $model->publish($id, $state);

            // Check for errors.
            if ($return === false) {
                $this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
            }

            // Clear the profile id from the session.
            $app->setUserState('com_auto79.edit.cron.id', null);

            // Flush the data from the session.
            $app->setUserState('com_auto79.edit.cron.data', null);

            // Redirect to the list screen.
            $this->setMessage(JText::_('COM_AUTO79_ITEM_SAVED_SUCCESSFULLY'));
            $menu = JFactory::getApplication()->getMenu();
            $item = $menu->getActive();

            if (!$item) {
                // If there isn't any menu item active, redirect to list view
                $this->setRedirect(JRoute::_('index.php?option=com_auto79&view=crons', false));
            } else {
                $this->setRedirect(JRoute::_($item->link . $menuitemid, false));
            }
        } else {
            throw new Exception(500);
        }
    }

    /**
     * Remove data
     *
     * @return void
     *
     * @throws Exception
     */
    public function remove() {
        // Initialise variables.
        $app = JFactory::getApplication();

        // Checking if the user can remove object
        $user = JFactory::getUser();

        if ($user->authorise('core.delete', 'com_auto79')) {
            $model = $this->getModel('Cron', 'Auto79Model');

            // Get the user data.
            $id = $app->input->getInt('id', 0);

            // Attempt to save the data.
            $return = $model->delete($id);

            // Check for errors.
            if ($return === false) {
                $this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
            } else {
                // Check in the profile.
                if ($return) {
                    $model->checkin($return);
                }

                $app->setUserState('com_auto79.edit.inventory.id', null);
                $app->setUserState('com_auto79.edit.inventory.data', null);

                $app->enqueueMessage(JText::_('COM_AUTO79_ITEM_DELETED_SUCCESSFULLY'), 'success');
                $app->redirect(JRoute::_('index.php?option=com_auto79&view=crons', false));
            }

            // Redirect to the list screen.
            $menu = JFactory::getApplication()->getMenu();
            $item = $menu->getActive();
            $this->setRedirect(JRoute::_($item->link, false));
        } else {
            throw new Exception(500);
        }
    }

    public function cronlinkpost() {
        $id = JRequest::getInt('id');
        if ($id > 0) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            // Select the required fields from the table.
            $query->select('*');
            $query->from('`#__auto79_cron`');
            $query->where('id =' . $id);
            $db->setQuery($query);
            if ($item = $db->loadObject()) {
                $to = $item->pageto;
                $from = $item->pagefrom;
                $step = $item->pagestep;
                $linkId = $item->link;
                $cateId = $item->adcategories;
                $prId = $item->province;
                $loop = $step + $to;
                $item->loop = $loop;
                if ($from > 0) {
                    if ($to < $from) {
                        for ($i = $to; $i <= $loop; $i++) {
                            $item->goto = $i;
                            $link = $this->getLink($linkId);
                            if ($link != '') {
                                $link .= 'page-' . $i;
                                $this->getLinkPostByCategory($link, $item);
                            }
                            if ($loop == $i) {
                                $this->updatePageLoop($id, ($to + $step));
                            }
                        }
                    } else {
                        $this->updatePageLoop($id, 1);
                    }
                } else {
                    for ($i = $to; $i <= $loop; $i++) {
                        $item->goto = $i;
                        $link = $this->getLink($linkId);
                        if ($link != '') {
                            $link .= 'page-' . $i;
                            $this->getLinkPostByCategory($link, $item);
                        }
                        if ($loop == $i) {
                            $this->updatePageLoop($id, ($to + $step));
                        }
                    }
                }
            } else {
                echo "Ban nghi nay khong ton tai";
            }
        } else {
            echo "Ban nghi nay khong ton tai";
        }
    }

    public function updatePageLoop($id, $pageto) {
        $db = JFactory::getDbo();
        $date = JFactory::getDate();
        $query = $db->getQuery(true);
        $fields = array(
            $db->quoteName('pageto') . ' = ' . $pageto,
        );
        $conditions = array(
            $db->quoteName('id') . ' = ' . $id,
        );
        $query->update($db->quoteName('#__auto79_cron'))->set($fields)->where($conditions);
        //echo $query;
        $db->setQuery($query);
        $db->execute();
    }

    public function getLinkPostByCategory($url, $item) {
        $html = $this->getRemoteForm($url);
        if (strlen(trim($html)) > 0) {
            $dom = new simple_html_dom();
            $dom->load($html);
            $urlbase = $dom->find('base', 0)->href;
            $i = 0;
            $page = $dom->find('.afterDiscussionListHandle .pageNavHeader', 0)->plaintext;
            $arrPage = explode(' ', $page);
            $pageCurr = $arrPage[3];
            if ($pageCurr < $item->goto) {
                $this->updatePageLoop($item->id, 1);
                return;
            }
            foreach ($dom->find('.discussionListItems .discussionListItem') as $li) {
                //$form = $li->outertext;
                if (is_object($li->find('h3.title a', 1)))
                    $href = $li->find('h3.title a', 1)->href;
                else
                    $href = $li->find('h3.title a', 0)->href;
                $i++;
                $data['link'] = $urlbase . $href;
                $data['category_id'] = $item->adcategories;
                $data['province'] = $item->province;
                $data['created_by'] = $item->userid;
                $data['approval'] = $item->approval;
                $data['timeapproval'] = $item->timeapproval;
                $data['cronid'] = $item->id;
                $this->AddLink($data);
            }
        } else {
            return array();
        }
    }

    public function AddLink($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        //file_put_contents(dirname(__FILE__) . '/post.log', 'Post:' . print_r($data, true) . " \n", FILE_APPEND);
        if ($data['link'] != '') {
            $query->select('id');
            $query->from('#__auto79_linkpost');
            $query->where('link =' . $db->quote($data['link']));
            $db->setQuery($query);
            if (!$db->loadResult()) {
                $query = $db->getQuery(true);
                $date = JFactory::getDate();
                $data['time_created'] = $date->toSql();
                $columns = array(
                    'link',
                    'category_id',
                    'province',
                    'created_by',
                    'approval',
                    'timeapproval',
                    'time_created',
                    'cronid');
                $values = array(
                    $db->quote($data['link']),
                    $db->quote($data['category_id']),
                    $db->quote($data['province']),
                    $db->quote($data['created_by']),
                    $db->quote($data['approval']),
                    $db->quote($data['timeapproval']),
                    $db->quote($data['time_created']),
                    $data['cronid']);
                $query
                        ->insert($db->quoteName('#__auto79_linkpost'))
                        ->columns($db->quoteName($columns))
                        ->values(implode(',', $values));
                //file_put_contents(dirname(__FILE__) . '/query.log', 'Query:' . $query . " \n", FILE_APPEND);
                $db->setQuery($query);
                $db->execute();
            }
        }
        return true;
    }

    public function cronpost() {
        $id = JRequest::getInt('id');
        if ($id > 0) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            // Select the required fields from the table.
            $query->select('*');
            $query->from('`#__auto79_cron`');
            $query->where('id =' . $id);
            $db->setQuery($query);
            if ($item = $db->loadObject()) {
                $to = $item->pageto;
                $from = $item->pagefrom;
                $linkId = $item->link;
                $cateId = $item->adcategories;
                $prId = $item->province;
                for ($i = $to; $i <= $from; $i++) {
                    $link = $this->getLink($linkId);
                    if ($link != '') {
                        $link .= 'page-' . $i;
                        $this->getCategory($link, $cateId, $prId);
                    }
                }
            } else {
                echo "Ban nghi nay khong ton tai";
            }
        } else {
            echo "Ban nghi nay khong ton tai";
        }
    }

    public function getLink($id) {
        if ($id > 0) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            // Select the required fields from the table.
            $query->select('link');
            $query->from('`#__auto79_link`');
            $query->where('id =' . $id);
            //echo $query;//exit();
            $db->setQuery($query);
            if ($link = $db->loadResult()) {
                return $link;
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    public function insertAdverts($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        //file_put_contents(dirname(__FILE__) . '/post.log', 'Post:' . print_r($data, true) . " \n", FILE_APPEND);
        if ($data['alias'] != '') {
            $query->select('id');
            $query->from('#__adverts79_adverts');
            $query->where('alias =' . $db->quote($data['alias']));
            $db->setQuery($query);
            if (!$db->loadResult()) {
                $query = $db->getQuery(true);
                $date = JFactory::getDate();
                $data['time_created'] = $date->toSql();
                $columns = array('state',
                    'title',
                    'alias',
                    'advert_description',
                    'advert_images',
                    'time_created',
                    'category_id',
                    'advert_sellertype',
                    'advert_price_negotiable',
                    'advert_vip',
                    'add_province');
                $values = array(0,
                    $db->quote($data['title']),
                    $db->quote($data['alias']),
                    $db->quote($data['advert_description']),
                    $db->quote($data['advert_images']),
                    $db->quote($data['time_created']),
                    $data['cateId'],
                    1,
                    1,
                    1,
                    $data['province']);
                $query
                        ->insert($db->quoteName('#__adverts79_adverts'))
                        ->columns($db->quoteName($columns))
                        ->values(implode(',', $values));
                //echo $query;
                //file_put_contents(dirname(__FILE__) . '/query.log', 'Query:' . $query . " \n", FILE_APPEND);
                $db->setQuery($query);
                $db->execute();
            }
        }
    }

    public static function getRemoteForm($url) {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curlHandle, CURLOPT_ENCODING, "");
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_AUTOREFERER, true);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($curlHandle);
        curl_close($curlHandle);
        return $result;
    }

    public static function getRemotePost($url) {
        $result = file_get_contents($url);
        return $result;
    }

    public function getCategory($url, $cateId = 1, $prId = 26) {
        $html = $this->getRemoteForm($url);
        if (strlen(trim($html)) > 0) {
            $dom = new simple_html_dom();
            $dom->load($html);
            $urlbase = $dom->find('base', 0)->href;
            $i = 0;
            foreach ($dom->find('.discussionListItems .discussionListItem') as $li) {
                //$form = $li->outertext;
                if (is_object($li->find('h3.title a', 1)))
                    $href = $li->find('h3.title a', 1)->href;
                else
                    $href = $li->find('h3.title a', 0)->href;
                $i++;
                $this->getNews($urlbase . $href, $cateId, $prId);
            }
        } else {
            return array();
        }
    }

    public function getNews($href, $cateId, $prId = 26) {
        echo $href . '<br>';
        $html = self::getRemoteForm($href);
        if (strlen(trim($html)) > 0) {
            $dom = new simple_html_dom();
            $dom->load($html);
            $i = 0;
            $title = $dom->find('.mainContent .titleBar h1', 0)->outertext;
            $html_title = str_get_html($title);
            $title = $html_title->plaintext;
            $arrHref = explode('/', $href);
            $arrAlias = explode('.', $arrHref[4]);
            $alias = $arrAlias[0];
            foreach ($dom->find('.InlineModForm .messageList li') as $li) {
                $liId = $li->id;
                $postId = explode('-', $liId);
                if ($i == 0) {
                    $html_link_sub = str_get_html($li);
                    $arrImg = array();
                    foreach ($html_link_sub->find('.messageContent .SelectQuoteContainer img') as $box_sub) {
                        //echo $urlImg = $img->{'data-url'};
                        $urlImg = '';
                        if ($box_sub->src != '' && $box_sub->{'data-url'} == '') {
                            $urlImg = preg_replace('/_[0-9]{1,3}x[0-9]{1,3}/i', '', $box_sub->src);
                        }
                        if ($box_sub->{'data-url'} != '') {
                            $urlImg = preg_replace('/_[0-9]{1,3}x[0-9]{1,3}/i', '', $box_sub->{'data-url'});
                        }
                        //$urlImg = str_replace('http://[img]', '', $urlImg);
                        //echo strpos($urlImg, 'href=') . 'test' . '<br>';
                        if (!strpos($urlImg, 'href=') && !strpos($urlImg, '[img]') && !strpos($urlImg, 'error_code') && $urlImg != '' && !strpos($urlImg, 'copy') && !strpos($urlImg, 'clear') && !strpos($urlImg, '?')) {
                            //echo $urlImg . '<br>';
                            if ($this->downloadImg($urlImg) != '')
                                $arrImg[] = $this->downloadImg($urlImg);
                        }
                    }
                    $content = $li->find('.messageContent .SelectQuoteContainer', 0)->outertext;
                    //Replace <script>
                    $content = preg_replace('/<script([^<]+)([^>]+)\/script>/i', '', $content);
                    //Replace <style>
                    $content = preg_replace('/<style([^<]+)([^>]+)\/style>/i', '', $content);
                    //Replace HTML tag, only use <p><img><b><i><strong><table><th><tr><td><caption><colgroup><col><thead><tbody><tfoot>
                    $content = strip_tags($content, '<p><a><img><b><i><strong><br><h1><h2><h3><h4><h5><h6><code><em><q><small><ol><li><ul><dl><dt><dd><table><th><tr><td><caption><colgroup><col><thead><tbody><tfoot>');
                    //Replace HTML atract
                    //$content = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i", '<$1$2>', $content);
                    //$content = preg_replace("/<br[^>]+\>/i", " ", $content);
                    $content = preg_replace("/<img[^>]+\>/i", " ", $content);

                    //$alias = Auto79HelpersAuto79::vn_to_str($title);
                    $data = array('title' => $title, 'alias' => $alias, 'advert_description' => $content, 'advert_images' => implode(',', $arrImg), 'href' => $href, 'cateId' => $cateId, 'province' => $prId);
                    $this->insertAdverts($data);
                } else {
                    break;
                }
                $i++;
            }
        } else {
            return array();
        }
    }

    public function downloadImg($url, $postId = '') {
        if (@getimagesize($url)) {
            list($width, $height, $type, $attr) = @getimagesize($url);
            if ($width > 100) {
                $split_image = pathinfo($url);
                //file_put_contents(dirname(__FILE__) . '/split_image.log', 'split_image:' . print_r($split_image, true) . " \n", FILE_APPEND);
                //file_put_contents(dirname(__FILE__) . '/url.log', 'split_image:' . $url . " \n", FILE_APPEND);
                if (!empty($split_image['extension'])) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $response = curl_exec($ch);
                    curl_close($ch);
                    if (!strpos($response, 'DOCTYPE')) {
                        $file_name = JPATH_ROOT . '/files79/adverts79/images/adverts/' . $split_image['filename'] . "." . $split_image['extension'];
                        $file = fopen($file_name, 'w') or die("X_x");
                        fwrite($file, $response);
                        fclose($file);
                        return $split_image['filename'] . "." . $split_image['extension'];
                    } else {
                        return '';
                    }
                }
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

}
