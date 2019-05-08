<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Seo_model created for make operations with seo dictionary data
 */
class Seo_model extends CI_Model
{
    const WORDS_LIMIT = 50;
    const WORDS_PAIR_DELIMITER = ' ... ';
    const DEFAULT_LANG_CODE = '';
    const DEFAULT_LANG = 'english';
    const FORWARD_PREFIX = 'forward';
    const REVERSE_PREFIX = 'reverse';
    const SEARCH_LIMIT = 100;

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    public function submit_seo_statistics($insert_array)
    {
        $this->db->insert('pageview_log', $insert_array);
    }

    /**
     * Get data from entry_id from the seo_translations table
     * @param $id
     * @return mixed
     */
    public function get_seo_translation($id)
    {
        $this->db->select('seo_english_id, translation_language, translation, source');
        $this->db->from('seo_translations');
        $this->db->where('id', $id);
        $result = $this->db->get();

        return $result;
    }

    /**
     * Get more translation by seo english id
     * @param $seo_english_id
     * @return mixed
     */
    public function get_more_translations($seo_english_id)
    {
        $this->db->select('id, translation_language, translation');
        $this->db->from('seo_translations');
        $this->db->where('seo_english_id', $seo_english_id);
        $this->db->where('translation !=', "");

        $result = $this->db->get();

        return $result;
    }

    /**
     * Get list of english words by lang and first letter
     * @param $first_letter
     * @param $lang
     * @return array of objects
     */
    public function get_words($first_letter, $lang)
    {
        $sql = "SELECT se.word FROM seo_translations AS st
                  JOIN seo_english AS se ON se.id=st.seo_english_id
                WHERE st.first_letter=? AND st.translation_language=?
                ORDER BY se.word";
        //check memcached first
        $key = self::FORWARD_PREFIX . $first_letter . $lang;
        $words = $this->mc->get($key);
        if (!$words) {
            $query = $this->db->query($sql, array($first_letter, $lang));
            $words = $query->result();
            //set memcache again
            $this->mc->set($key, $words);
        }
        return $words;
    }

    /**
     * Get list of current lang words by lang and first letter
     * @param $first_letter
     * @param $lang
     * @return array of objects
     */
    public function get_words_revers($first_letter, $lang)
    {
        //check memcached first
        $key = self::REVERSE_PREFIX . $first_letter . $lang;
        $words = $this->mc->get($key);
        if (!$words) {
            $this->db->select('LOWER(translation) AS word');
            $this->db->from('seo_translations');
            $this->db->where('translation_language', $lang);
            $this->db->like('translation', $first_letter, 'after');
            $this->db->order_by('word', 'asc');
            $words = $this->db->get()->result();
            //set memcache again
            $this->mc->set($key, $words);
        }
        return $words;
    }


    /**
     * Check on condition than make words pair and add it to array
     * @param $pairs
     * @param $first_word
     * @param $second_word
     * @param $remainderPart
     * @return array
     */
    public function add_condition_words_pair($pairs, $first_word, $second_word, $remainderPart)
    {
        if ($remainderPart == 1) {
            $pairs[] = $first_word . self::WORDS_PAIR_DELIMITER . $second_word;
        }
        return $pairs;
    }

    /**
     * Make words pair and add it to array
     * @param $pairs
     * @param $first_word
     * @param $second_word
     * @return array
     */
    public function add_words_pair($pairs, $first_word, $second_word)
    {
        $pairs[] = $first_word . self::WORDS_PAIR_DELIMITER . $second_word;
        return $pairs;
    }

    /**
     * Get list of words by (lang and first letter and revers flag)
     * @param $first_letter
     * @param $lang
     * @param $revers
     * @return array
     */
    public function get_words_list($first_letter, $lang, $revers)
    {
        if ($revers) {
            return $this->get_words_revers($first_letter, $lang);
        } else {
            return $this->get_words($first_letter, $lang);
        }
    }

    /**
     * Make words pairs from ordered array of words
     * @param $first_letter
     * @param $lang
     * @return array
     */
    public function make_words_pairs($first_letter, $lang, $revers)
    {
        $words = $this->get_words_list($first_letter, $lang, $revers);
        //set counters
        $rows_counter = 0;
        $limit_counter = 1;
        $int_counter = 0;
        //get integer part and remainder from division
        $words_count = count($words);
        $intPart = intval($words_count / self::WORDS_LIMIT);
        $remainderPart = $words_count % self::WORDS_LIMIT;
        //set containers
        $pairs = [];
        $first_word = '';

        foreach ($words as $item) {
            $word = $item->word;
            //remainder part algorithm
            if ($int_counter == $intPart && $intPart != 0 && $limit_counter == 1) {//set first word for remainder pair
                $first_word = $word;
                $pairs = $this->add_condition_words_pair($pairs, $first_word, $word, $remainderPart);
            } elseif ($intPart == 0 && $rows_counter == 0) {//if count of records less than WORDS_LIMIT(we have integer part=0)
                $first_word = $word;
                $pairs = $this->add_condition_words_pair($pairs, $first_word, $word, $remainderPart);
            } elseif ($rows_counter == ($words_count - 1) && $limit_counter != self::WORDS_LIMIT) {//if end of array set second word for remainder and make pair
                $pairs = $this->add_words_pair($pairs, $first_word, $word);
            }
            //usual integer part algorithm
            if ($limit_counter == self::WORDS_LIMIT) {//set second word and make pair
                $pairs = $this->add_words_pair($pairs, $first_word, $word);
                $limit_counter = 0;
                $int_counter++;
            }
            if ($limit_counter == 1) {//set first word
                $first_word = $word;
            }
            $limit_counter++;
            $rows_counter++;
        }
        return $pairs;
    }

    /**
     * Get language code by name
     * @param $name
     * @return string
     */
    public function get_lang_code($name)
    {
        $this->load->library('translate');
        $lang_list = $this->translate->get_languages_list();
        $name = ucwords(ucwords($name), '(');
        $lang_code = array_search($name, $lang_list);
        if ($lang_code !== FALSE) {
            return $lang_code;
        } else {
            return self::DEFAULT_LANG_CODE;
        }
    }

    /**
     * Get current language from pair
     * @param $first_lang
     * @param $second_lang
     * @return string
     */
    public function get_current_lang($first_lang, $second_lang)
    {
        if ($first_lang == self::DEFAULT_LANG) {
            return $second_lang;
        } else {
            return $first_lang;
        }
    }

    /**
     * Get revers flag for lang pair
     * @param $first_lang
     * @param $second_lang
     * @return bool
     */
    public function get_revers_for_lang_pair($first_lang, $second_lang)
    {
        if ($first_lang == self::DEFAULT_LANG) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * Get words pairs from ordered array of words
     * @param $first_lang
     * @param $second_lang
     * @param $first_letter
     * @return array
     */
    public function get_words_pairs($first_lang, $second_lang, $first_letter)
    {
        $revers = $this->get_revers_for_lang_pair($first_lang, $second_lang);
        $current_lang = $this->get_current_lang($first_lang, $second_lang);
        $land_code = $this->get_lang_code($current_lang);
        return $this->make_words_pairs($first_letter, $land_code, $revers);
    }

    /**
     * Get words list for english - current lang pair
     * @param $params
     * @return array of objects
     */
    public function get_words_list_forward($params)
    {
        $sql = "SELECT DISTINCT se.word, st.id AS translation_id FROM seo_translations AS st
                  JOIN seo_english AS se ON se.id=st.seo_english_id
                WHERE st.first_letter=? AND st.translation_language=? AND se.word BETWEEN ? AND ?
                ORDER BY se.word";
        //check memcached first
        $key = self::FORWARD_PREFIX . $params->first_letter . $params->lang_code . $params->first_word . $params->second_word;
        $words = $this->mc->get($key);
        if (!$words) {
            $query = $this->db->query($sql, array(
                $params->first_letter,
                $params->lang_code,
                $params->first_word,
                $params->second_word
            ));
            $words = $query->result();
            //set memcache again
            $this->mc->set($key, $words);
        }
        return $words;
    }

    /**
     * Get words list for current lang - english pair
     * @param $params
     * @return array of objects
     */
    public function get_words_list_revers($params)
    {
        $sql = "SELECT DISTINCT LOWER(translation) AS word, id AS translation_id FROM seo_translations
                WHERE LEFT(translation, 1)=? AND translation_language=? AND translation BETWEEN ? AND ?
                ORDER BY word";
        //check memcached first
        $key = self::REVERSE_PREFIX . $params->first_letter . $params->lang_code . $params->first_word . $params->second_word;
        $words = $this->mc->get($key);
        if (!$words) {
            $query = $this->db->query($sql, array(
                $params->first_letter,
                $params->lang_code,
                $params->first_word,
                $params->second_word
            ));
            $words = $query->result();
            //set memcache again
            $this->mc->set($key, $words);
        }
        return $words;
    }

    /**
     * Select needed words list
     * @param $params
     * @return array
     */
    public function get_words_list_by_params($params)
    {
        if ($params->revers) {
            return $this->get_words_list_revers($params);
        } else {
            return $this->get_words_list_forward($params);
        }
    }

    /**
     * Get words list by current words pair
     * @param $params
     * @return array
     */
    public function get_words_list_by_pair($params)
    {
        //init params
        $params_obj = new stdClass();
        $params_obj->first_lang = $params['first_lang'];
        $params_obj->second_lang = $params['second_lang'];
        $params_obj->first_letter = $params['letter'];
        $params_obj->first_word = $params['first_word'];
        $params_obj->second_word = $params['second_word'];

        //get current language name and code
        $current_lang = $this->get_current_lang($params_obj->first_lang, $params_obj->second_lang);
        $params_obj->lang_code = $this->get_lang_code($current_lang);
        $params_obj->revers = $this->get_revers_for_lang_pair($params_obj->first_lang, $params_obj->second_lang);

        return $this->get_words_list_by_params($params_obj);
    }

    /**
     * Get language for word translation
     * @param $first_lang
     * @param $second_lang
     * @return string
     */
    public function get_translation_lang($first_lang, $second_lang)
    {
        if ($first_lang == self::DEFAULT_LANG) {
            return $second_lang;
        } else {
            return self::DEFAULT_LANG;
        }
    }

    /**
     * Check if have access to reverse functionality
     * @param $first_lang
     * @param $second_lang
     * @return bool
     */
    public function have_access_to_reverse($first_lang, $second_lang)
    {
        $this->config->load('seo_dictionary');
        $denied_list = $this->config->item('denied_languages_reverse');
        $current_lang = $this->get_current_lang($first_lang, $second_lang);
        $reverse = $this->get_revers_for_lang_pair($first_lang, $second_lang);
        if ($reverse) {
            if (in_array($current_lang, $denied_list)) {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * Replace special symbols into string
     * @param $string
     * @param $direction
     * @return string
     */
    public function replace_symbols($string, $direction)
    {
        $string = urldecode($string);
        if ($direction == 'forward') {
            return preg_replace(['/\(/', '/\)/', '/ /'], ['<', '>', '_'], $string);
        } elseif ($direction == 'reverse') {
            return preg_replace(['/\</', '/\>/', '/_/'], ['(', ')', ' '], $string);
        } else {
            return $string;
        }
    }

    /**
     * Search translations by word
     * @param $lang
     * @param $word
     * @param $forward_flag
     * @return mixed
     */
    public function search($lang, $word, $forward_flag)
    {
        $lang_code = $this->get_lang_code($lang);
        if ($forward_flag) {
            return $this->forward_search($lang_code, $word);
        } else {
            return $this->reverse_search($lang_code, $word);
        }
    }

    /**
     * Search translations by word in forward direction(example english-ukrainian)
     * @param $lang_code
     * @param $word
     * @return mixed
     */
    public function forward_search($lang_code, $word)
    {
        $sql = $this->get_search_sql(self::FORWARD_PREFIX);
        $query = $this->db->query($sql, array(
            $lang_code,
            $word . '%',
            self::SEARCH_LIMIT
        ));
        return $query->result();
    }

    /**
     * Search translations by word in reverse direction(example ukrainian-english)
     * @param $lang_code
     * @param $word
     * @return mixed
     */
    public function reverse_search($lang_code, $word)
    {
        $sql = $this->get_search_sql(self::REVERSE_PREFIX);
        $query = $this->db->query($sql, array(
            $lang_code,
            $word . '%',
            self::SEARCH_LIMIT
        ));
        return $query->result();
    }

    /**
     * Get search sql code by prefix(forward, reverse)
     * @param $type
     * @return string
     */
    public function get_search_sql($type)
    {
        if ($type == self::FORWARD_PREFIX) {
            return "SELECT se.word, CONCAT('/', 'english', '-', l.uri_slug, '/', LOWER(REPLACE(se.word, '\"', '')), '-', st.id) AS url
                FROM seo_english AS se
                JOIN seo_translations AS st ON st.seo_english_id=se.id
                JOIN languages AS l ON l.code=st.translation_language
                WHERE st.translation_language=? AND se.word LIKE ?
                ORDER BY se.word LIMIT ?";
        } elseif ($type == self::REVERSE_PREFIX) {
            return "SELECT st.translation AS word, CONCAT('/', l.uri_slug, '-', 'english', '/', LOWER(REPLACE(st.translation, '\"', '')), '-', st.id) AS url
                FROM seo_translations AS st
                JOIN languages AS l ON l.code=st.translation_language
                WHERE st.translation_language=? AND st.translation LIKE ?
                ORDER BY st.translation LIMIT ?";
        }
    }

}

