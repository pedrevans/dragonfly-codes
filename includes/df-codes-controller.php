<?php

/**
 * The main plugin controller
 *
 * @package DragonFly Codes Controller
 * @subpackage Main Plugin Controller
 * @since 0.1
 */

class DfCodesController {
    var $version = '1.0';

    static function send_win_mail_to_admin($post_id, $participant) {
        $email = $participant['email'];
        $real_name = $participant['realname'];
        $admin = get_post_meta($post_id, 'dragonfly_mail_to', true);
        $post = get_post( $post_id );
        $slug = $post->post_name;
        error_log('Mailing: to: '. $admin . ' subject: [dragonfly-winner] postid: '.$post_id.' '.$email. ' message: '.$email.__(' got all the codes right for postid '.$post_id));
        mail($admin, '[dragonfly-winner] postid: '.$post_id.' '.
            __('postname').': '. $slug. ' '.
            __('participant').': '.$real_name. ' <' .$email. '>',
            $real_name. ' <' .$email. '>' .__(' got all the codes right for postid '.$post_id).' '.__('postname ').$slug);
    }
    static function do_evaluate_codes($post_data) {
        error_log('do_evaluate_codes post_data: '.print_r($post_data,true));
        $codeList = $post_data['codes'];
        sort($codeList);
        $post_id = $post_data['postid'];
        $participant = array(
            'email' =>  $post_data['participant_email'],
            'realname' => $post_data['participant_realname']
        );
        error_log('do_evaluate_codes '.print_r($codeList, true));
        $message = __('You got all codes right!');
        $winners = self::get_winning_codes($post_id);
        $received = implode(' ', $codeList);
        $is_winner =  $received == $winners;
        $status['winner'] = $is_winner;
        if (!$is_winner) {
            $message = __('You did not have all the winning codes.');
        }
        $status = [
            'success' => true,
            'message' => $message,
            'winner' => $is_winner
        ];
        if ($is_winner) {
            self::send_win_mail_to_admin($post_id, $participant);
        }
        return $status;
    }
    public static function good_nonce($nonce, $nonce_id) {
        if (!$nonce) {
            return false;
        }
        if (!wp_verify_nonce($nonce, $nonce_id)) {
            return false;
        }
        return true;
    }

    public static function dragonfly_evaluate_callback() {
        // Expect POST parameters
        // - nonce
        // - codes - an array of codes
        // - postid
        error_log('dragonfly_evaluate_callback POST = '.print_r($_POST, true));
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : false;
        if (!self::good_nonce($nonce, 'dragonfly_evaluate_nonce')) {
            die('Naughty!');
        }
        $result = self::do_evaluate_codes($_POST);
        echo json_encode($result);
        die();
    }

    public function __construct() {
        error_log('DfCodesController constructor');
        add_action('wp_ajax_dragonfly_evaluate', array(__CLASS__, 'dragonfly_evaluate_callback'));
        add_action('wp', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'register_styles'));
        add_action('wp_enqueue_scripts', array($this, 'register_scripts'));
    }

    function init() {
        error_log('DfCodesController init');
        add_shortcode('dragonfly_codes', array($this, 'dragonfly_codes_shortcode'));
    }

    public function register_styles() {
        error_log('DfCodesController register_plugin_styles');
        wp_register_style('dragonfly_evaluate_ui_style', plugins_url("static/css/layout.css", dirname(__FILE__)), array(), $this->version, "all");
    }

    public function register_scripts() {
        error_log('DfCodesController enqueue_scripts');
        wp_register_script('dragonfly_evaluate_ui_script', plugins_url("static/js/form.js", dirname(__FILE__)), array('jquery-core'), $this->version, true);
    }

    public function dragonfly_codes_shortcode($attrs = array()) {
        wp_enqueue_script('dragonfly_evaluate_ui_script');
        wp_localize_script('dragonfly_evaluate_ui_script', 'dragonfly_ajax_object',
            array( 'ajax_url' => admin_url('admin-ajax.php' ))
        );
        wp_enqueue_style('dragonfly_evaluate_ui_style');
        $defaults = array(
            'codes' => null,
            'max_codes' => DRAGONFLY_CODE_MAX,
            'mail_to' => get_bloginfo('admin_email')
        );
        $options = shortcode_atts($defaults, $attrs);
        return $this->dragonfly_codes_ui_html($options);
    }
    static function codes_as_sorted_list($codes) {
        $codes = mb_strtolower($codes);
        $codeList = explode(' ', $codes);
        sort($codeList);
        return $codeList;
    }
    static function encode_codes($codes) {
        error_log('encode_codes '.$codes);
        $codeList = self::codes_as_sorted_list($codes);
        $codes = implode(' ', $codeList);
        $codes = crypt($codes, DRAGONFLY_SALT);
        return array('codeHash' => $codes, 'codeList' => $codeList);
    }
    function code_fields_html($max_codes) {
        $out = '';
        for ($i = 1; $i <= $max_codes; $i++) {
            $extra_class = ' '.(($i % 2) == 0 ? 'even' : 'odd');
            if ($i == 1)
                $extra_class .= " first";
            if ($i == $max_codes)
                $extra_class .= " last";
            $out .= '
                <div id="dragonfly-code-field-'.$i.'" class="dragonfly-code-field-div'.$extra_class.'">
                    <div class="dragonfly-label">'.__('Code #').$i.'</div><input class="dragonfly-input-text" type="text" name="dragonfly-input-text-'.$i.'">
                </div>';
        }
        return $out;
    }
    function get_page_id() {
        global $post;
        return $post->ID;
    }
    static function get_winning_codes($post_id) {
        $codes = get_post_meta($post_id, 'dragonfly_codes', true);
        if (!$codes)
            return false;
        $codeList = self::codes_as_sorted_list($codes);
        $codes = implode(' ', $codeList);
        return $codes;
    }
    function dragonfly_codes_ui_html($options) {
        $page_id = $this->get_page_id();
        $codes = self::get_winning_codes($page_id);
        error_log('dragonfly_codes_ui_html codes = '.$codes);
        if (empty($codes)) {
            return '<div class="dragonfly-warning">'.__('Sorry, we are not accepting any codes at the moment!').'</div>';
        }
        if (isset($options['mail_to'])) {
            update_post_meta($page_id,'dragonfly_mail_to',$options['mail_to']);
        }
        $nonce = wp_create_nonce('dragonfly_evaluate_nonce');
        $out = '
            <p>
            <div id="dragonfly-codes-form-div">
                <div class="dragonfly-evaluate-button-div">
                </div>
                <div id="dragonfly-email-div" class="dragonfly-email-div dragonfly-info-div">
                    <div class="dragonfly-label">'.__('Your Email Address: ').'</div>
                    <input id="dragonfly-input-participant-email" class="dragonfly-input dragonfly-input-email" type="email" name="dragonfly-input-participant-email" placeholder="'.__('Your Email Address').'">
                </div>
                <div id="dragonfly-realname-div" class="dragonfly-realname-div dragonfly-info-div">
                    <div class="dragonfly-label">'.__('Your Name: ').'</div>
                    <input id="dragonfly-input-participant-realname" class="dragonfly-input dragonfly-input-realname" type="text" name="dragonfly-input-participant-realname" placeholder="'.__('Your Name').'">
                </div>
                <div id="dragonfly-code-fields-div" class="dragonfly-code-fields-div">'.
                    $this->code_fields_html($options['max_codes']).'
                </div>
                <button id="dragonfly-evaluate-button" class="dragonfly-button" type="button" data-postid="'.$page_id.
                    '" data-nonce="'.$nonce.
                    '" data-nocodes="'.__('You have to enter some codes').
                    '" data-nomail="'.__('Invalid email address').
                    '" data-norealname="'.__('Don\'t forget to enter your name').
                '">'.
                    __('Send codes now').
                '</button>
            </div>

            <!-- <div id="dragonfly-spinner"><img src="'.get_stylesheet_directory_uri().'/images/ajax-loader.gif"/></div> -->
            <div id="dragonfly-evaluate-result"></div>
            </p>';
        return $out;
    }
}
