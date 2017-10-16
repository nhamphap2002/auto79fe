<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Auto79
 * @author     Thang Tran <trantrongthang1207@gmail.com>
 * @copyright  2017 Thang Tran
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::register('Auto79Helper', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_auto79' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'auto79.php');

/**
 * Class Auto79FrontendHelper
 *
 * @since  1.6
 */
class Auto79HelpersAuto79 {

    /**
     * Get an instance of the named model
     *
     * @param   string  $name  Model name
     *
     * @return null|object
     */
    public static function getModel($name) {
        $model = null;

        // If the file exists, let's
        if (file_exists(JPATH_SITE . '/components/com_auto79/models/' . strtolower($name) . '.php')) {
            require_once JPATH_SITE . '/components/com_auto79/models/' . strtolower($name) . '.php';
            $model = JModelLegacy::getInstance($name, 'Auto79Model');
        }

        return $model;
    }

    /**
     * Gets the files attached to an item
     *
     * @param   int     $pk     The item's id
     *
     * @param   string  $table  The table's name
     *
     * @param   string  $field  The field's name
     *
     * @return  array  The files
     */
    public static function getFiles($pk, $table, $field) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
                ->select($field)
                ->from($table)
                ->where('id = ' . (int) $pk);

        $db->setQuery($query);

        return explode(',', $db->loadResult());
    }

    /**
     * Gets the edit permission for an user
     *
     * @param   mixed  $item  The item
     *
     * @return  bool
     */
    public static function canUserEdit($item) {
        $permission = false;
        $user = JFactory::getUser();

        if ($user->authorise('core.edit', 'com_auto79')) {
            $permission = true;
        } else {
            if (isset($item->created_by)) {
                if ($user->authorise('core.edit.own', 'com_auto79') && $item->created_by == $user->id) {
                    $permission = true;
                }
            } else {
                $permission = true;
            }
        }

        return $permission;
    }

    function vn_to_str($str) {
        $str = self::vt_remove_vietnamese_accent($str);

        // Replace special symbols with spaces or not
        $str = self::vt_remove_special_characters($str);

        // Replace Vietnamese characters or not
        $str = self::vt_replace_vietnamese_characters($str);

        $str = self::stringURLUnicodeSlug($str);

        $str = self::ampReplace($str);

        $str = self::cleanText($str);

        $str = self::stripImages($str);

        // Remove any '-' from the string since they will be used as concatenaters
        $str = str_replace('-', ' ', $str);

        $lang = JFactory::getLanguage();

        $str = $lang->transliterate($str);

        // Trim white spaces at beginning and end of alias and make lowercase
        $str = trim(JString::strtolower($str));

        // Remove any duplicate whitespace, and ensure all characters are alphanumeric
        $str = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', $str);

        // Trim dashes at beginning and end of alias
        $str = trim($str, '-');

        return $str;
    }

    /*
     * Remove 5 Vietnamese accent / tone marks if has Combining Unicode characters
     * Tone marks: Grave (`), Acute(�), Tilde (~), Hook Above (?), Dot Bellow(.)
     */

    public static function vt_remove_vietnamese_accent($str) {

        $str = preg_replace("/[\x{0300}\x{0301}\x{0303}\x{0309}\x{0323}]/u", "", $str);

        return $str;
    }

    /*
     * Remove or Replace special symbols with spaces
     */

    public static function vt_remove_special_characters($str, $remove = true) {

        // Remove or replace with spaces
        $substitute = $remove ? "" : " ";

        $str = preg_replace("/[\x{0021}-\x{002D}\x{002F}\x{003A}-\x{0040}\x{005B}-\x{0060}\x{007B}-\x{007E}\x{00A1}-\x{00BF}]/u", $substitute, $str);

        return $str;
    }

    /*
     * Replace Vietnamese vowels with diacritic and Letter D with Stroke with corresponding English characters
     */

    public static function vt_replace_vietnamese_characters($str) {

        $str = preg_replace("/[\x{00C0}-\x{00C3}\x{00E0}-\x{00E3}\x{0102}\x{0103}\x{1EA0}-\x{1EB7}]/u", "a", $str);
        $str = preg_replace("/[\x{00C8}-\x{00CA}\x{00E8}-\x{00EA}\x{1EB8}-\x{1EC7}]/u", "e", $str);
        $str = preg_replace("/[\x{00CC}\x{00CD}\x{00EC}\x{00ED}\x{0128}\x{0129}\x{1EC8}-\x{1ECB}]/u", "i", $str);
        $str = preg_replace("/[\x{00D2}-\x{00D5}\x{00F2}-\x{00F5}\x{01A0}\x{01A1}\x{1ECC}-\x{1EE3}]/u", "o", $str);
        $str = preg_replace("/[\x{00D9}-\x{00DA}\x{00F9}-\x{00FA}\x{0168}\x{0169}\x{01AF}\x{01B0}\x{1EE4}-\x{1EF1}]/u", "u", $str);
        $str = preg_replace("/[\x{00DD}\x{00FD}\x{1EF2}-\x{1EF9}]/u", "y", $str);
        $str = preg_replace("/[\x{0110}\x{0111}]/u", "d", $str);

        return $str;
    }

    /**
     * This method implements unicode slugs instead of transliteration.
     *
     * @param   string  $string  String to process
     *
     * @return  string  Processed string
     *
     * @since   11.1
     */
    public static function stringURLUnicodeSlug($string) {
        // Replace double byte whitespaces by single byte (East Asian languages)
        $str = preg_replace('/\xE3\x80\x80/', ' ', $string);

        // Remove any '-' from the string as they will be used as concatenator.
        // Would be great to let the spaces in but only Firefox is friendly with this

        $str = str_replace('-', ' ', $str);

        // Replace forbidden characters by whitespaces
        $str = preg_replace('#[:\#\*"@+=;!><&\.%()\]\/\'\\\\|\[]#', "\x20", $str);

        // Delete all '?'
        $str = str_replace('?', '', $str);

        // Trim white spaces at beginning and end of alias and make lowercase
        $str = trim(JString::strtolower($str));

        // Remove any duplicate whitespace and replace whitespaces by hyphens
        $str = preg_replace('#\x20+#', '-', $str);

        return $str;
    }

    /**
     * Replaces &amp; with & for XHTML compliance
     *
     * @param   string  $text  Text to process
     *
     * @return  string  Processed string.
     *
     * @since   11.1
     *
     * @todo There must be a better way???
     */
    public static function ampReplace($text) {
        $text = str_replace('&&', '*--*', $text);
        $text = str_replace('&#', '*-*', $text);
        $text = str_replace('&amp;', '&', $text);
        $text = preg_replace('|&(?![\w]+;)|', '&amp;', $text);
        $text = str_replace('*-*', '&#', $text);
        $text = str_replace('*--*', '&&', $text);

        return $text;
    }

    /**
     * Callback method for replacing & with &amp; in a string
     *
     * @param   string  $m  String to process
     *
     * @return  string  Replaced string
     *
     * @since   11.1
     */
    public static function _ampReplaceCallback($m) {
        $rx = '&(?!amp;)';

        return preg_replace('#' . $rx . '#', '&amp;', $m[0]);
    }

    /**
     * Cleans text of all formatting and scripting code
     *
     * @param   string  &$text  Text to clean
     *
     * @return  string  Cleaned text.
     *
     * @since   11.1
     */
    public static function cleanText(&$text) {
        $text = preg_replace("'<script[^>]*>.*?</script>'si", '', $text);
        $text = preg_replace('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is', '\2 (\1)', $text);
        $text = preg_replace('/<!--.+?-->/', '', $text);
        $text = preg_replace('/{.+?}/', '', $text);
        $text = preg_replace('/&nbsp;/', ' ', $text);
        $text = preg_replace('/&amp;/', ' ', $text);
        $text = preg_replace('/&quot;/', ' ', $text);
        $text = strip_tags($text);
        $text = htmlspecialchars($text, ENT_COMPAT, 'UTF-8');

        return $text;
    }

    /**
     * Strip img-tags from string
     *
     * @param   string  $string  Sting to be cleaned.
     *
     * @return  string  Cleaned string
     *
     * @since   11.1
     */
    public static function stripImages($string) {
        return preg_replace('#(<[/]?img.*>)#U', '', $string);
    }

}
