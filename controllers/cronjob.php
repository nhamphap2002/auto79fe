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

class Auto79ControllerCronJob extends JControllerLegacy {

    public function cronlinkjob() {
        $id = JRequest::getInt('id');
        if ($id > 0) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            // Select the required fields from the table.
            $query->select('cr.*');
            $query->from('`#__auto79_cron` AS cr');
            $query->select("el.numpage, el.cateloopli, el.postlink");
            $query->join("INNER", "#__auto79_element AS el ON el.id = cr.elemid");
            $query->where('cr.id =' . $id);
            $query->where('cr.state = 1');
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
                            $link = $item->link;
                            if ($link != '') {
                                $link .= 'page-' . $i;
                                $ischeck = $this->getLinkPostByCategory($link, $item);
                                if (!$ischeck) {
                                    $this->updatePageLoop($item, 1);
                                    break;
                                }
                            }
                            if ($loop == $i) {
                                $this->updatePageLoop($item, ($to + $step));
                            }
                        }
                    } else {
                        $this->updatePageLoop($item, 1);
                    }
                } else {
                    for ($i = $to; $i <= $loop; $i++) {
                        $item->goto = $i;
                        $link = $item->link;
                        if ($link != '') {
                            $link .= 'page-' . $i;
                            $ischeck = $this->getLinkPostByCategory($link, $item);
                            if (!$ischeck) {
                                $this->updatePageLoop($item, 1);
                                break;
                            }
                        }
                        if ($loop == $i) {
                            $this->updatePageLoop($item, ($to + $step));
                        }
                    }
                }
//                }
            } else {
                echo "Ban nghi nay khong ton tai";
            }
        } else {
            echo "Ban nghi nay khong ton tai";
        }
    }

    public function updatePageLoop($item, $pageto) {
        $db = JFactory::getDbo();
        $date = JFactory::getDate();
        $query = $db->getQuery(true);
        if ($pageto == 1) {
            $params = JComponentHelper::getParams('com_auto79');
            $resetto = $params->get('resetto');
            $resetloop = $params->get('resetloop');
            $resetfrom = $params->get('resetfrom');
            $fields = array(
                $db->quoteName('pageto') . ' = ' . $resetto,
                $db->quoteName('pagestep') . ' = ' . $resetloop,
                $db->quoteName('pagefrom') . ' = ' . $resetfrom,
            );
            $params = JComponentHelper::getParams('com_auto79');
            $emailadmin = $params->get('emailadmin');
            $vendorEmail = 'thang.testdev@gmail.com';
            $vendorName = 'Cron job';
            $subject = 'Cron job ' . $item->title . ' da chay hoan thang';
            $body = 'Cong viec nay da hoan thanh ban hay kiem tra lai du lieu';
            $mailer = JFactory::getMailer();
            $mailer->addReplyTo($emailadmin, $vendorName);
            $mailer->addRecipient($vendorEmail);
            $mailer->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
            $mailer->isHTML(TRUE);
            $mailer->setBody($body);
            $mailer->Send();
        } else {
            $fields = array(
                $db->quoteName('pageto') . ' = ' . $pageto,
            );
        }
        $conditions = array(
            $db->quoteName('id') . ' = ' . $item->id,
        );
        $query->update($db->quoteName('#__auto79_cron'))->set($fields)->where($conditions);
        //echo $query;
        $db->setQuery($query);
        $db->execute();
    }

    /*
     * ALTER TABLE `tv_auto79_cron` ADD `elem_page` VARCHAR(256) NOT NULL AFTER `type_news`;
     * ALTER TABLE `tv_auto79_cron` ADD `elem_cate_li` VARCHAR(256) NOT NULL AFTER `elem_page`;
     * ALTER TABLE `tv_auto79_cron` ADD `elem_cate_title` VARCHAR(256) NOT NULL AFTER `elem_cate_li`;
     */

    public function getLinkPostByCategory($url, $item) {
        $html = $this->getRemoteForm($url);
        if (strlen(trim($html)) > 0) {
            $dom = new simple_html_dom();
            $dom->load($html);
            $urlbase = $dom->find('base', 0)->href;
            $i = 0;
            $page = $dom->find($item->numpage, 0)->plaintext;
            $arrPage = explode(' ', $page);
            $pageCurr = $arrPage[3];
            if ($pageCurr < $item->goto) {
                $this->updatePageLoop($item->id, 1);
                return false;
            }
            foreach ($dom->find($item->cateloopli) as $li) {
                //$form = $li->outertext;
                if (is_object($li->find($item->postlink, 1)))
                    $href = $li->find($item->postlink, 1)->href;
                else
                    $href = $li->find($item->postlink, 0)->href;
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
            return true;
        }
    }

    public function AddLink($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        //file_put_contents(dirname(__FILE__) . '/post.log', 'Post:' . print_r($data, true) . " \n", FILE_APPEND);
        if ($data['link'] != '') {
            $query->select('id');
            $query->from('#__auto79_job');
            $query->where('link =' . $db->quote($data['link']));
            $db->setQuery($query);
            if (!$db->loadResult()) {
                $query = $db->getQuery(true);
                $date = JFactory::getDate();
                $data['time_created'] = $date->toSql();
                $timeapproval = new JDate('now +' . $data['timeapproval'] . ' minutes');
                $columns = array(
                    'state',
                    'link',
                    'category_id',
                    'province',
                    'created_by',
                    'approval',
                    'timeapproval',
                    'time_created',
                    'cronid',
                    'user_approval'
                );
                $values = array(
                    1,
                    $db->quote($data['link']),
                    $db->quote($data['category_id']),
                    $db->quote($data['province']),
                    $db->quote($data['created_by']),
                    $db->quote($data['approval']),
                    $db->quote($timeapproval),
                    $db->quote($data['time_created']),
                    $data['cronid'],
                    $db->quote($data['created_by'])
                );
                $query
                        ->insert($db->quoteName('#__auto79_job'))
                        ->columns($db->quoteName($columns))
                        ->values(implode(',', $values));
                //file_put_contents(dirname(__FILE__) . '/query.log', 'Query:' . $query . " \n", FILE_APPEND);
                $db->setQuery($query);
                $db->execute();
            }
        }
        return true;
    }

    public function cronjob() {
        $id = JRequest::getInt('id');
        if ($id > 0) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            // Select the required fields from the table.
            $query->select('*');
            $query->from('`#__auto79_job`');
            $query->where('cronid =' . $id);
            $query->where('hasget = 0');
            $query->where('state = 1');
            $query->order('id ASC');
            $start = 0;
            $value = $this->getNumberNews($id);
            $db->setQuery($query, $start, $value);
            if ($items = $db->loadObjectList()) {
                foreach ($items as $item) {
                    $this->getNews($item);
                }
            } else {
                echo "Ban nghi nay khong ton tai";
                return true;
            }
        } else {
            echo "Ban nghi nay khong ton tai";
            return true;
        }
        return true;
    }

    function getNumberNews($id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        // Select the required fields from the table.
        $query->select('numbernews');
        $query->from('`#__auto79_cron`');
        $query->where('id =' . $id);
        $query->where('state = 1');
        $db->setQuery($query);
        if ($id = $db->loadResult()) {
            return $id;
        } else {
            return 1;
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
        $han_nop = new JDate('now +1 month');
        //file_put_contents(dirname(__FILE__) . '/post.log', 'Post:' . print_r($data, true) . " \n", FILE_APPEND);
        if ($data['alias'] != '') {
            $query->select('id, linkpostid');
            $query->from('#__job79_jobs');
            $query->where('alias =' . $db->quote($data['alias']));
            $db->setQuery($query);
            if (!$item = $db->loadObject()) {
                $query = $db->getQuery(true);
                $date = JFactory::getDate();
                $data['time_created'] = $date->toSql();
                $columns = array(
                    'state',
                    'title',
                    'alias',
                    'mo_ta',
                    'time_created',
                    'loai_tin',
                    'han_nop',
                    'add_province',
                    'cronid',
                    'created_by',
                    'linkpostid');
                $values = array(
                    $data['state'],
                    $db->quote($data['title']),
                    $db->quote($data['alias']),
                    $db->quote($data['mo_ta']),
                    $db->quote($data['time_created']),
                    1,
                    $db->quote($han_nop),
                    $data['province'],
                    $data['cronid'],
                    $data['created_by'],
                    $data['linkpostid']);
                $query
                        ->insert($db->quoteName('#__job79_jobs'))
                        ->columns($db->quoteName($columns))
                        ->values(implode(',', $values));
                //echo $query;
                $db->setQuery($query);
                $db->execute();
                //echo $query;
                if ($postid = $db->insertid()) {
                    //file_put_contents(dirname(__FILE__) . '/postid.log', 'Postid:' . $postid . " \n", FILE_APPEND);
                    $this->updateLinkCronPostId($postid, $data['linkpostid']);
                    return $postid;
                } else {
                    return 0;
                }
            } else {
                $this->updateLinkCronPostId($item->id, $item->linkpostid);
            }
        } else {
            return 0;
        }
    }

    public function updateLinkCronPostId($postid, $cronid) {
        $db = JFactory::getDbo();
        $date = JFactory::getDate();
        $query = $db->getQuery(true);

        $fields = array(
            $db->quoteName('jobid') . ' = ' . $postid,
            $db->quoteName('hasget') . ' = 1'
        );
        /*
          $vendorEmail = 'thang.testdev@gmail.com';
          $vendorName = 'Cron job';
          $subject = 'Cron job id ' . $id . ' da chay hoan thang';
          $body = 'Cong viec nay da hoan thanh van hay kiem tra lai du lieu';
          $mailer = JFactory::getMailer();
          //$mailer->addReplyTo($vendorEmail, $vendorName);
          $mailer->addRecipient($vendorEmail);
          $mailer->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
          $mailer->isHTML(TRUE);
          $mailer->setBody($body);
          $mailer->Send();
         */
        $conditions = array(
            $db->quoteName('id') . ' = ' . $cronid,
        );
        $query->update($db->quoteName('#__auto79_job'))->set($fields)->where($conditions);
        //file_put_contents(dirname(__FILE__) . '/query.log', 'Query:' . $query . " \n", FILE_APPEND);
        $db->setQuery($query);
        $db->execute();
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

    /*
     * ALTER TABLE `tv_auto79_cron` ADD `elem_post_title` VARCHAR(256) NOT NULL AFTER `elem_cate_title`;
     */

    public function getNews($item) {
        $html = self::getRemoteForm($item->link);
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select("el.titlepost, el.postloopli, el.postimg, el.postcontent");
        $query->from('`#__auto79_cron` AS cr');
        $query->join("INNER", "#__auto79_element AS el ON el.id = cr.elemid");
        $query->where('cr.id =' . $item->cronid);
        $query->where('cr.state = 1');
        $db->setQuery($query);
        $elem = $db->loadObject();
        if (strlen(trim($html)) > 0 && $elem) {
            $dom = new simple_html_dom();
            $dom->load($html);
            $i = 0;
            if (!is_object($dom->find($elem->titlepost, 0)))
                return '';
            $title = $dom->find($elem->titlepost, 0)->outertext;
            $title = preg_replace('/<span([^<]+)([^>]+)\/span>/i', '', $title);
            $html_title = str_get_html($title);
            $title = $html_title->plaintext;
            $arrHref = explode('/', $item->link);
            $arrAlias = explode('.', $arrHref[4]);
            $alias = $arrAlias[0];

            $params = JComponentHelper::getParams('com_adverts79');
            $replaceContent = $params->get('replacecontent');
            $replaceTitle = $params->get('replacetitle');
            $arrReplaceText = explode(';', $replaceContent);
            $arrReplaceTitle = explode(';', $replaceTitle);
            if (count($arrReplaceTitle) > 0) {
                for ($k = 0; $k < count($arrReplaceTitle); $k++) {
                    $arrTitle = explode('|', $arrReplaceTitle[$k]);
                    if (count($arrTitle) > 1) {
                        $title = trim(str_replace($arrTitle[0], $arrTitle[1], $title));
                    }
                }
            }
            $alias = Auto79HelpersAuto79::vn_to_str($title);
            foreach ($dom->find($elem->postloopli) as $li) {
                $liId = $li->id;
                $postId = explode('-', $liId);               
                if ($i == 0) {
                    $html_link_sub = str_get_html($li);
                    $arrImg = array();
                    $ig = 1;
                    $content = $li->find($elem->postcontent, 0)->outertext;
                    //Replace <script>
                    $content = preg_replace('/<script([^<]+)([^>]+)\/script>/i', '', $content);
                    //Replace <style>
                    $content = preg_replace('/<style([^<]+)([^>]+)\/style>/i', '', $content);
                    //Replace HTML tag, only use <p><img><b><i><strong><table><th><tr><td><caption><colgroup><col><thead><tbody><tfoot>
                    $content = strip_tags($content, '<br>');
                    //Replace HTML atract
                    //$content = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i", '<$1$2>', $content);
                    //$content = preg_replace("/<br[^>]+\>/i", " ", $content);
                    $content = preg_replace("/<img[^>]+\>/i", " ", $content);
                    $content = preg_replace('/(?:\s*<br[^>]*>\s*){3,}/s', "<br>", $content);

                    if (count($arrReplaceText) > 0) {
                        for ($j = 0; $j < count($arrReplaceText); $j++) {
                            $arrText = explode('|', $arrReplaceText[$j]);
                            if (count($arrText) > 1) {
                                $content = str_replace($arrText[0], $arrText[1], $content);
                            }
                        }
                    }                    
                    $data = array(
                        'title' => $title,
                        'alias' => $alias,
                        'mo_ta' => $content,
                        'href' => $item->link,
                        'province' => $item->province,
                        'created_by' => $item->created_by,
                        'modified_by' => $item->created_by,
                        'state' => $item->approval,
                        'cronid' => $item->cronid,
                        'linkpostid' => $item->id
                    );
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

    public function autojob() {
        $id = JRequest::getInt('id');
        $datenow = new JDate('now');
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('id, jobid, cronid, user_approval');
        $query->from('#__auto79_job');
        $query->where('timeapproval < ' . $db->quote($datenow));
        //$query->where('timeapproval >' . $db->quote('NOW()'));
        $query->where('hasget > 0');
        $query->where('jobid > 0');
        $query->where('approval = 0');
        $query->where('cronid = ' . $id);
        $start = 0;
        $value = 50;
        $db->setQuery($query, $start, $value);
        if ($items = $db->loadObjectList()) {
            foreach ($items as $item) {
                $query = $db->getQuery(true);
                $fields = array(
                    $db->quoteName('state') . ' = ' . 1,
                    $db->quoteName('modified_by') . ' = ' . $item->user_approval,
                    $db->quoteName('time_updated') . ' = ' . $db->quote($datenow)
                );
                $conditions = array(
                    $db->quoteName('linkpostid') . ' = ' . $item->id,
                    $db->quoteName('cronid') . ' = ' . $item->cronid,
                );
                $query->update($db->quoteName('#__job79_jobs'))->set($fields)->where($conditions);
                $db->setQuery($query);
                if ($db->execute()) {
                    $query = $db->getQuery(true);
                    $fields = array(
                        $db->quoteName('approval') . ' = ' . 1,
                        $db->quoteName('hasapproval') . ' = ' . 1,
                    );
                    $conditions = array(
                        $db->quoteName('id') . ' = ' . $item->id,
                    );
                    $query->update($db->quoteName('#__auto79_job'))->set($fields)->where($conditions);
                    $db->setQuery($query);
                    $db->execute();
                }
            }
        }
        return;
    }

}

/*
 * UPDATE `tv_auto79_articles` SET `timeapproval`= DATE_SUB(`time_created`, INTERVAL -30 MINUTE) WHERE `cronid`=1
 * UPDATE `tv_auto79_articles` SET `timeapproval`= DATE_SUB(`time_created`, INTERVAL -300 MINUTE) WHERE `cronid`=1
 * 
 */
