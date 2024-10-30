<?php

class CodeqrcodeWidget
{

    public $codeqrcode_url;
    public $api_data;
    public $api_data_table;
    public $ad_data = array();
    protected $application_id;
    public $curlfailovao;

    public function __construct()
    {

        $this->codeqrcode_url = "https://www.codeqrcode.com/analytics/";
        $this->application_id = get_option('codeQRCodeApplicationID');


        if (is_admin()) {
            add_action("admin_menu", array(
                &$this,
                "adminMenu"
            ));

            add_action('admin_init', array(
                &$this,
                "setOptions"
            ));
        }


        if (isset($_GET['page']) && $_GET['page'] == 'codeqrcode-adsense' ) {
 //       if (is_admin()) {




            if (get_option('codeQRCodeApplicationID') !== '') {
                $this->api_data = $this->addNewWebsiteApi();
                if (!isset($this->api_data->data)) {
                    $this->api_data->data = array();
                }
                $this->api_data_table = clone $this->api_data;
            }

            if (isset($this->api_data->flag) && $this->api_data->flag) {
                update_option('codeQRCodeWidgets', $this->api_data);
            }

            

            /* Add new items to the end of array data*/
            $item_add = new stdClass();

            /*
             * Add items to array for qr selection
             */

            $item_add->img_idkey = 'static';
            $item_add->name = 'Static QR code';
            if (!isset($this->api_data)) {
                $this->api_data = new stdClass();
            }
            if (!isset($this->api_data->data)) {
                $this->api_data->data = array();
            }
            array_push($this->api_data->data, unserialize(serialize($item_add)));


            /*
             * Add items to array for ads selection
             */

            if (get_option('codeQRCodeAds') !== '') {
                $item_add->uniq_name = stripslashes(htmlspecialchars_decode(get_option('codeQRCodeAds')));
                if (get_option('codeQRCodeAds1Name') != "") {
                    $item_add->title = get_option('codeQRCodeAds1Name');
                } else {
                    $item_add->title = 'Ad 1 code';
                }

                array_push($this->ad_data, unserialize(serialize($item_add)));
            }

            if (get_option('codeQRCodeAds2') !== '') {
                $item_add->uniq_name = stripslashes(htmlspecialchars_decode(get_option('codeQRCodeAds2')));
                if (get_option('codeQRCodeAds2Name') != "") {
                    $item_add->title = get_option('codeQRCodeAds2Name');
                } else {
                    $item_add->title = 'Ad 2 code';
                }
                array_push($this->ad_data, unserialize(serialize($item_add)));
            }
            if (get_option('codeQRCodeAds3') !== '') {
                $item_add->uniq_name = stripslashes(htmlspecialchars_decode(get_option('codeQRCodeAds3')));
                if (get_option('codeQRCodeAds3Name') != "") {
                    $item_add->title = get_option('codeQRCodeAds3Name');
                } else {
                    $item_add->title = 'Ad 3 code';
                }
                array_push($this->ad_data, unserialize(serialize($item_add)));
            }

            $item_add->uniq_name = 'none';
            $item_add->title = 'Do not show';
            array_push($this->ad_data, unserialize(serialize($item_add)));
        }
        //var_dump($this->api_data);

        if (get_option('codeQRCodeSingleWidgetID') !== 'none') {

            if (get_option('codeQRCodeSingleWidgetID') == '') {
                if (isset($this->api_data->data[0]->uniq_name) && $this->api_data->data[0]->uniq_name != 'none') {
                    update_option('codeQRCodeSingleWidgetID', $this->api_data->data[0]->uniq_name);
                }
                if($this->application_id != "")
                    add_filter('the_content', 'codeQRCode_bottom_post');
            }
        }

        if (get_option('codeQRCodePageWidgetID') !== 'none') {

            if (get_option('codeQRCodePageWidgetID') == '') {
                if (isset($this->api_data->data[0]->uniq_name) && $this->api_data->data[0]->uniq_name != 'none') {
                    update_option('codeQRCodePageWidgetID', $this->api_data->data[0]->uniq_name);
                }

            }
            if($this->application_id != "")
                add_filter('the_content', 'codeQRCode_bottom_post');
        }


    }

    function setOptions()
    {
        register_setting('codeQRCode-options', 'codeQRCodeApplicationID');
        register_setting('codeQRCode-options', 'codeQRCodePoweredBy');
        register_setting('codeQRCode-options', 'codeQRCodeSingleWidgetID');
        register_setting('codeQRCode-options', 'codeQRCodePageWidgetID');
        register_setting('codeQRCode-options', 'codeQRCodeSingleWidgetTitle');
        register_setting('codeQRCode-options', 'codeQRCodeShadow');
        // Ads codes
        register_setting('codeQRCode-options', 'codeQRCodeAds');
        register_setting('codeQRCode-options', 'codeQRCodeAds2');
        register_setting('codeQRCode-options', 'codeQRCodeAds3');
        // Custom ads name
        register_setting('codeQRCode-options', 'codeQRCodeAds1Name');
        register_setting('codeQRCode-options', 'codeQRCodeAds2Name');
        register_setting('codeQRCode-options', 'codeQRCodeAds3Name');

        //Static QR codes
        //for signle page
        register_setting('codeQRCode-options', 'codeQRCodeSingle');
        register_setting('codeQRCode-options', 'enableQROnSingle');
        register_setting('codeQRCode-options', 'qrCodeAlignSingle'); //Settings for align qr code on single post
        register_setting('codeQRCode-options', 'qrMarginSingle'); //Settings for margin on single post
        register_setting('codeQRCode-options', 'qrImgSizeSingle'); //Settings for Image size on single post
        //for single page
        register_setting('codeQRCode-options', 'codeQRCodePage'); // Used to store qr data
        register_setting('codeQRCode-options', 'enableQROnPage'); // Settings to enable/disable qr code
        register_setting('codeQRCode-options', 'qrCodeAlignPage'); //Settings for align qr code on single page
        register_setting('codeQRCode-options', 'qrMarginPage'); //Settings for margin on single page
        register_setting('codeQRCode-options', 'qrImgSizePage'); //Settings for Image size on single page


    }

    public function adminMenu()
    {
        add_menu_page('CodeQRCode Generator - Premium QR codes and Analytics', 'Code QRCode', 'manage_options', 'codeqrcode-adsense', array(
            $this,
            'createAdminPage'
        ), content_url() . '/plugins/codeqrcode-adsense/images/codeqrcode-icon.png');

    }

    public function getSignupUrl()
    {
        $user_info =  wp_get_current_user();

        return $this->codeqrcode_url . 'login/application_id?utm_source=wordpress&utm_medium=wpcqrc&e=' . urlencode(get_option('admin_email')) .
        '&pub=' .  preg_replace('/^www\./','',$_SERVER['SERVER_NAME']).
        '&un=' . urlencode($user_info->user_login). '&fn=' . urlencode($user_info->user_firstname) . '&ln=' . urlencode($user_info->user_lastname) .
        '&pl=infeed&return_uri=' . admin_url("admin.php?page=codeqrcode-adsense");

    }


    private function addNewWebsiteApi()
    {

        $service     = $this->codeqrcode_url . "wp-authenticate/user";
        $p['ip']     = $_SERVER['REMOTE_ADDR'];
        $p['domain'] = site_url();
        $p['source'] = "wordpress";
        $p['QRCodeApplicationID'] = get_option('codeQRCodeApplicationID');

        /*
 * We're going to use the output buffer to store the debug info.
 */
//        ob_start();
//        $out = fopen('php://output', 'w');
//

        $data = wp_remote_post( $service, array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'body' => $p,
                'cookies' => array()
            )
        );



//        /*
//         * Joining debug info and response body.
//         */
//        $debugdata = ob_get_clean();
//        $debugdata .= PHP_EOL . PHP_EOL;
//        var_dump($debugdata);
//        var_dump($data);

        $ret_info = new stdClass();
        if(is_wp_error($data))
        {
            $this->curlfailovao = 1;
        }
        else
        {
            $this->curlfailovao = 0;
            $ret_info = json_decode($data['body']);
        }

        return $ret_info;

    }

    public function createAdminPage()
    {

        $code = get_option('codeQRCodeApplicationID');
        $qr_home_url = 'https://www.codeqrcode.com';
        $qr_dashboard_url = 'https://www.codeqrcode.com/analytics/qranalytics';

        ?>
        <style>

            a.qrcode-signup-button:hover {
                cursor: pointer;
                color: #f8f8f8;
            }

            .qrcode-signup-button {
                vertical-align: top;
                width: auto;
                height: 30px;
                line-height: 30px;
                padding: 10px;
                font-size: 20px;
                color: white;
                text-align: center;
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
                background: #c0392b;
                border-radius: 5px;
                border-bottom: 2px solid #b53224;
                cursor: pointer;
                -webkit-box-shadow: inset 0 -2px #b53224;
                box-shadow: inset 0 -2px #b53224;
                text-decoration: none;
                margin-top: 3px;
                margin-bottom: 10px;
                float: left;

            }

            a.qrcode-signup-button:hover {
                cursor: pointer;
                color: lightskyblue;
            }

            textarea {
                overflow: auto;
                padding: 4px 6px;
                line-height: 1.4;
            }

            .alert_red{
                margin-bottom: 18px;
                margin-top: 10px;
                color: #c09853;
                text-shadow: 0 1px 0 rgba(255,255,255,0.5);
                background-color: #fcf8e3;
                border: 1px solid #fbeed5;
                -webkit-border-radius: 4px;
                -moz-border-radius: 4px;
                border-radius: 4px;
                padding: 8px 35px 8px 14px;
            }
            .alert-msg_red {
                color: #8f0100;
                background-color: #f6cbd2;
                border-color: #f68d89;
            }

            .aklamator_INlogin {
                padding: 10px;
                background-color: #000058;
                color: white;
                text-decoration: none;
                font-size: 15px;
                text-align: center;
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
                border-radius: 5px;
                cursor: pointer;
                -webkit-box-shadow:0 0 4px #909090;
                box-shadow:0 0 4px #909090;
            }

            .aklamator_INlogin:hover {
                color: lightskyblue;
            }

            h3 {
                margin-bottom: 3px;
            }
            p {
                margin-top: 3px;
            }

            .preview
            {
                text-transform: uppercase;
                padding: 2px 4px;
                border: 2px solid black;
                border-radius: 2px;
                color: black;
                font-weight: bold;
            }

            .preview:hover
            {
                background-color: black;
                color: white;
                cursor: pointer;
            }

            .btn { border: 1px solid #fff; font-size: 13px; border-radius: 3px; background: transparent; text-transform: uppercase; font-weight: 700; padding: 3px 5px; min-width: 162px; max-width: 100%; text-decoration: none;}
            .btn:Hover, .btn.hovered { border: 1px solid #fff; }
            .btn:Active, .btn.pressed { opacity: 1; border: 1px solid #fff; border-top: 3px solid #17ade0; -webkit-box-shadow: 0 0 0 transparent; box-shadow: 0 0 0 transparent; }

            .btn-primary { background: #1ac6ff; border:1px solid #1ac6ff; color: #fff; text-decoration: none;}
            .btn-primary:hover, .btn-primary.hovered { background: #1ac6ff;  border:1px solid #1ac6ff; opacity:0.9; color: #030e3f
            }
            .btn-primary:Active, .btn-primary.pressed { background: #1ac6ff; border:1px solid #1ac6ff; }

            .box{float: left; margin-left: 10px; width: 700px; background-color:#f8f8f8; padding: 10px; border-radius: 5px;}
            .box img{width: 910px};
            .right_sidebar{float: right; margin-left: 10px; width: 300px; background-color:#f8f8f8; padding: 10px; border-radius: 5px;}

        </style>
        <!-- Load css libraries -->

        <link href="//cdn.datatables.net/1.10.5/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">

        <div id="codeQRCode-options" style="width:1160px;margin-top:10px;">

            <div style="float: left; width: 300px;">

                <a target="_blank" href="<?php echo $qr_home_url; ?>?utm_source=wordpress_cqc">
                    <img style="border-radius:5px;border:0px;" src=" <?php echo plugins_url('images/cqc-logo-300x225-1.png', __FILE__);?>" /></a>
                <a target="_blank" href="<?php echo $qr_home_url; ?>?utm_source=wordpress_cqc">
                    <img style="margin-top: 5px; border-radius:5px;border:0px;" src=" <?php echo plugins_url('images/cqc-logo-300x225-2.png', __FILE__);?>" /></a>
                <?php
                if ($code != '') : ?>
                    <a target="_blank" href="<?php echo $qr_dashboard_url; ?>/?utm_source=wordpress_cqc">
                        <img style="border:0px;margin-top:5px;border-radius:5px;" src="<?php echo plugins_url('images/dashboard.jpg', __FILE__); ?>" /></a>

                <?php endif; ?>

                <a target="_blank" href="<?php echo $qr_home_url;?>/contact/?utm_source=wp-plugin-contact-cqc">
                    <img style="border:0px;margin-top:5px; margin-bottom:5px;border-radius:5px;" src="<?php echo plugins_url('images/support.jpg', __FILE__); ?>" /></a>

                <a target="_blank" href="https://qr.rs/q/56a5d">
                    <img style="border:0px;margin-top:5px; margin-bottom:5px;border-radius:5px;" src="<?php echo plugins_url('images/cqc-promo-300x200.png', __FILE__); ?>" /></a>

                <div style="margin-left: 0; width: 300px; background-color:#f8f8f8; padding: 0px; border-radius: 5px;">
                    <h3 style="text-align: center">How to create Email signature</h3>
                    <iframe width="300" height="225" src="https://www.youtube.com/embed/EqfTHkPVPoY?rel=0" frameborder="0" allowfullscreen></iframe>

                </div>

            </div>

            <div class="box">

                <h1>Code QR codes generator</h1>

                <?php

                if ($code == '' || isset($this->api_data->error)) : ?>
                    <h3><?php if ($code == '') echo 'Step 1: ';?>Get your QRCode Application ID</h3>
                    <a class='qrcode-signup-button' id="qrcode-signup-button" >Click here for FREE registration/login</a>
                    <div style="clear: both"></div>
                    <p>Or you can manually <a href="<?php echo $this->codeqrcode_url . 'registration/publisher'; ?>" target="_blank">register</a> or <a href="<?php echo $this->codeqrcode_url . 'login'; ?>" target="_blank">login</a> and copy paste your Application ID</p>

                <?php endif; ?>

                <div style="clear: both"></div>
                <?php if ($code == '') { ?>
                    <h3>Step 2: &nbsp;&nbsp;&nbsp;&nbsp; Paste your CodeQRCode Application ID</h3>
                <?php }else{ ?>
                    <h3>Your CodeQRcode Application ID</h3>
                <?php } ?>

                <form method="post" action="options.php">
                    <?php
                    settings_fields('codeQRCode-options');
                    ?>

                    <p>
                        <input type="text" style="width: 400px" name="codeQRCodeApplicationID" id="codeQRCodeApplicationID" value="<?php
                        echo (get_option("codeQRCodeApplicationID"));
                        ?>" maxlength="999" onchange="appIDChange(this.value)"/>
                    </p>
                    <p>
                        <input type="checkbox" id="codeQRCodePoweredBy" name="codeQRCodePoweredBy" <?php echo (get_option("codeQRCodePoweredBy") == true ? 'checked="checked"' : ''); ?> Required="Required">
                        <strong>Required</strong> I acknowledge there is a <a style="text-decoration: none" href="https://codeqrcode.com" target="_blank">'powered by QR CODE with love'</a> link on the widget. <br />
                    </p>

                    <?php if(isset($this->api_data->flag) && $this->api_data->flag === false): ?>
                        <p id="aklamator_infeed_inactive" class="alert_red alert-msg_red"><span style="color:red"><?php echo $this->api_data->error; ?></span></p>
                    <?php endif; ?>

                    <h1>Options</h1>

                    <h3><?php _e('QR codes Settings :'); ?></h3>

                    <p>
                        <input type="checkbox" id="codeQRCodeShadow" name="codeQRCodeShadow" <?php echo (get_option("codeQRCodeShadow") == true ? 'checked="checked"' : ''); ?>>
                        <label for="codeQRCodeSingleWidgetTitle"><strong>Enable Shadow</strong> on QR codes</label><br/>
                    </p>

                    <label for="codeQRCodeSingleWidgetTitle">Title above QR code (Optional): </label><br/>
                    <input type="text" style="width: 300px; margin:10px 0px" name="codeQRCodeSingleWidgetTitle" id="codeQRCodeSingleWidgetTitle" value="<?php echo (get_option("codeQRCodeSingleWidgetTitle")); ?>" maxlength="999" />


                    <table border="0" cellspacing="5" cellpadding="0">

                        <tr valign="top">
                            <td align="left" style="padding:0px 10px"><input type="checkbox" id="enableQROnSingle" name="enableQROnSingle" <?php echo (get_option("enableQROnSingle") == true ? 'checked="checked"' : ''); ?>></td>
                            <td align="left"><strong>End of each Post</strong> </td>
                            <td align="left" style="padding:0px 10px;">
                                <select style="width: 100px" id="codeQRCodeSingle" name="codeQRCodeSingle">
                                    <?php
                                    foreach ($this->api_data->data as $item): ?>
                                        <option <?php echo (stripslashes(htmlspecialchars_decode(get_option('codeQRCodeSingle'))) == $item->img_idkey)? 'selected="selected"' : '' ;?> value="<?php echo addslashes(htmlspecialchars($item->img_idkey)); ?>"><?php echo $item->name; ?></option>
                                    <?php endforeach; ?>

                                </select></td>
                            <td align="left" style="padding-left: 10px">
                                <?php if (get_option('qrCodeAlignSingle') != "") { $single_align = get_option('qrCodeAlignSingle'); } else { $single_align = 'Center'; } ?>
                                <select name="qrCodeAlignSingle">
                                    <option value="Left" <?php echo $single_align == 'Left'? 'selected="selected"': ''; ?>><?php _e('Left') ; ?></option>
                                    <option value="Center" <?php echo $single_align == 'Center'? 'selected="selected"': ''; ?>><?php _e('Center') ; ?></option>
                                    <option value="Right" <?php echo $single_align == 'Right'? 'selected="selected"': ''; ?>><?php _e('Right') ; ?></option>
                                    <option value="None" <?php echo $single_align == 'None'? 'selected="selected"': ''; ?>><?php _e('None') ; ?></option></select> <?php _e('alignment'); ?><br/>
<!--                                <input style="width:73px;text-align:center;" id="qrMarginSingle" name="qrMarginSingle" value="--><?php //echo get_option('qrMarginSingle') != '' ? get_option('qrMarginSingle') : '10'; ?><!--" /> px &nbsp;- --><?php //_e('margin'); ?><!--<br/>-->
                                <input style="width:73px;text-align:center;" id="qrImgSizeSingle" name="qrImgSizeSingle" value="<?php echo get_option('qrImgSizeSingle') != '' ? get_option('qrImgSizeSingle') : '120'; ?>" /> px &nbsp;- <?php _e('Image size'); ?><br/>
                            </td>
                        </tr>
                        <tr valign="top">
                            <td align="left" style="padding:0px 10px"><input type="checkbox" id="enableQROnPage" name="enableQROnPage" <?php echo (get_option("enableQROnPage") == true ? 'checked="checked"' : ''); ?>></td>
                            <td align="left"><strong>End of each Page</strong> </td>
                            <td align="left" style="padding:0px 10px">
                                <select style="width: 100px" id="codeQRCodePage" name="codeQRCodePage">
                                    <?php
                                    foreach ( $this->api_data->data as $item ): ?>
                                        <option <?php echo (stripslashes(htmlspecialchars_decode(get_option('codeQRCodePage'))) == $item->img_idkey)? 'selected="selected"' : '' ;?> value="<?php echo addslashes(htmlspecialchars($item->img_idkey)); ?>"><?php echo $item->name; ?></option>
                                    <?php endforeach; ?>

                                </select></td>
                            <td align="left" style="padding-left: 10px">
                                <?php if (get_option('qrCodeAlignPage') != "") { $singlePage_align = get_option('qrCodeAlignPage'); } else { $singlePage_align = 'Center'; } ?>
                                <select name="qrCodeAlignPage">
                                    <option value="Left" <?php echo $singlePage_align == 'Left'? 'selected="selected"': ''; ?> ><?php _e('Left') ; ?></option>
                                    <option value="Center" <?php echo $singlePage_align == 'Center'? 'selected="selected"': ''; ?>><?php _e('Center') ; ?></option>
                                    <option value="Right" <?php echo $singlePage_align == 'Right'? 'selected="selected"': ''; ?>><?php _e('Right') ; ?></option>
                                    <option value="None" <?php echo $singlePage_align == 'None'? 'selected="selected"': ''; ?>><?php _e('None') ; ?></option></select> <?php _e('alignment'); ?><br/>
<!--                                <input style="width:73px;text-align:center;" id="qrMarginPage" name="qrMarginPage" value="--><?php //echo get_option('qrMarginPage') != '' ? get_option('qrMarginPage') : '10'; ?><!--" /> px &nbsp;- --><?php //_e('margin'); ?><!--<br/>-->
                                <input style="width:73px;text-align:center;" id="qrImgSizePage" name="qrImgSizePage" value="<?php echo get_option('qrImgSizePage') != '' ? get_option('qrImgSizePage') : '120'; ?>" /> px &nbsp;- <?php _e('Image size'); ?><br/>
                            </td>
                        </tr>

                    </table>

                    <p>
                        <input type="checkbox" id="ad_setting_box" <?php if (get_option('codeQRCodeAds1Name') != "" || get_option('codeQRCodeAds2Name') != "" || get_option('codeQRCodeAds3Name') != "") {echo 'checked';} ?>>
                        <strong>Bonus</strong>: Use this plugin to serve <strong>Ad Codes</strong> using separate widget in Appearance->widgets. <br />
                    </p>


                    <div id="adsetings">
                        <h3 style="font-size:120%;margin-bottom:5px"><?php _e('Add your Adsense Code or any other script codes'); ?></h3>
                        <p style="margin-top:0px"><span class="description"><?php _e('Paste your <strong>Ad</strong> codes here and you will be able to show that <strong>Ad</strong>  by drag and drop CodeQRCode-AdSense widget in Appearance ->Widgets to desired position in your sidebar.') ?></span></p>

                        <h4><?php _e('Paste your Ad codes :'); ?></h4>
                        <table border="0" cellspacing="0" cellpadding="5">

                            <tr valign="top">
                                <td align="left" style="width:140px; padding-right: 5px"><strong>Ad1:</strong> <br/>Custom Ad name
                                    <input id="codeQRCodeAds1Name" name="codeQRCodeAds1Name" value="<?php echo stripslashes(htmlspecialchars(get_option('codeQRCodeAds1Name'))); ?>" placeholder="Optional Ad1 name"/>
                                </td>
                                <td align="left"><textarea style="margin:0 5px 3px 0; resize: none; overflow-y: scroll;text-align: left; height: 75px" id="codeQRCodeAds" name="codeQRCodeAds" rows="3" cols="45"><?php echo stripslashes(htmlspecialchars(get_option('codeQRCodeAds'))); ?></textarea></td>

                            </tr>
                            <tr valign="top">
                                <td align="left" style="width:140px; padding-right: 5px"><strong>Ad2:</strong> <br/>Custom Ad name
                                    <input id="codeQRCodeAds2Name" name="codeQRCodeAds2Name" value="<?php echo stripslashes(htmlspecialchars(get_option('codeQRCodeAds2Name'))); ?>" placeholder="Optional Ad2 name"/>
                                </td>
                                <td align="left"><textarea style="margin:0 5px 3px 0; resize: none; overflow-y: scroll;text-align: left; height: 75px" id="codeQRCodeAds2" name="codeQRCodeAds2" rows="3" cols="45"><?php echo stripslashes(htmlspecialchars(get_option('codeQRCodeAds2'))); ?></textarea></td>

                            </tr>
                            <tr valign="top">
                                <td align="left" style="width:140px; padding-right: 5px"><strong>Ad3:</strong> <br/>Custom Ad name
                                    <input id="codeQRCodeAds3Name" name="codeQRCodeAds3Name" value="<?php echo stripslashes(htmlspecialchars(get_option('codeQRCodeAds3Name'))); ?>" placeholder="Optional Ad3 name"/>
                                </td>
                                <td align="left"><textarea style="margin:0 5px 3px 0; resize: none; overflow-y: scroll;text-align: left; height: 75px" id="codeQRCodeAds3" name="codeQRCodeAds3" rows="4" cols="45"><?php echo stripslashes(htmlspecialchars(get_option('codeQRCodeAds3'))); ?></textarea></td>

                            </tr>

                        </table>
                    </div>

                    <input id="aklamator_infeed_save" class="aklamator_INlogin" style ="margin: 0; border: 0; float: left;" type="submit" value="<?php echo (_e("Save Changes")); ?>" />
                    <?php if(!isset($this->api_data->flag) || !$this->api_data->flag): ?>
                        <div style="float: left; padding: 7px 0 0 10px; color: red; font-weight: bold; font-size: 16px"> <-- In order to proceed save changes</div>
                    <?php endif ?>

                </form>
            </div>

            <!-- right sidebar -->

            <!-- End Right sidebar -->

        </div>

        <div style="clear:both"></div>
        <div style="margin-top: 20px; margin-left: 0px; width: 1010px;" class="box">

        <?php if ($this->curlfailovao && get_option('codeQRCodeApplicationID') != ''): ?>
                <h2 style="color:red">Error communicating with CodeQRCode server, please refresh plugin page or try again later. </h2>
            <?php endif;?>
        <?php if(!isset($this->api_data_table->flag) || !$this->api_data_table->flag): ?>
            <a href="<?php echo $this->getSignupUrl(); ?>" target="_blank"><img style="border-radius:5px;border:0px;" src=" <?php echo plugins_url('images/teaser-810x262.png', __FILE__);?>" /></a>
        <?php else : ?>
            <!-- Start of dataTables -->
            <div id="codeQRCode-options">
                <h1>Your Dynamic QR Codes</h1>
                <div>In order to add new QR codes please <a href="https://www.codeqrcode.com/analytics" target="_blank">login to CodeQRCode</a></div>
            </div>
            <br>
            <table cellpadding="0" cellspacing="0" border="0"
                   class="responsive dynamicTable display table table-bordered" width="100%">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Data(URL/Contact name...)</th>
                    <th>QR type</th>
                    <th>Date Created</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($this->api_data_table->data as $item): ?>

                    <tr class="odd">
                        <td style="vertical-align: middle;" ><?php echo str_replace(' - Dynamic','',$item->name); ?></td>
                        <td style="vertical-align: middle;" ><a href="<?php echo $item->url; ?>" target="_blank"><?php echo $item->url; ?></a></td>
                        <td style="vertical-align: middle;text-align:center;" ><?php echo $item->qr_type; ?><div id="<?php echo $item->img_idkey; ?>" class="preview qr_qtip_icon">preview</div><div id="<?php echo 'qrcode' . $item->img_idkey; ?>"></div></td>
                        <td style="vertical-align: middle;" ><?php echo $item->qr_date; ?></td>

                    </tr>
                <?php endforeach; ?>

                </tbody>
                <tfoot>
                <tr>
                    <th>Name</th>
                    <th>Domain</th>
                    <th>QR type</th>
                    <th>Date Created</th>

                </tr>
                </tfoot>
            </table>
            </div>

        <?php endif; ?>

        <!-- load js scripts -->

        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="<?php echo content_url(); ?>/plugins/codeqrcode-adsense/assets/dataTables/jquery.dataTables.min.js"></script>


        <script type="text/javascript">
            function appIDChange(val) {

                $('#codeQRCodeSingleWidgetID option:first-child').val('');
                $('#codeQRCodePageWidgetID option:first-child').val('');

            }

            $(document).ready(function(){

                $('#qrcode-signup-button').click(function () {
                    var akla_login_window = window.open('<?php echo $this->getSignupUrl(); ?>','_blank');
                    var aklamator_interval = setInterval(function() {
                        var aklamator_infeed_hash = akla_login_window.location.hash;
                        var aklamator_infeed_api_id = "";
                        if (akla_login_window.location.href.indexOf('aklamator_wordpress_api_id') !== -1) {

                            aklamator_infeed_api_id = aklamator_infeed_hash.substring(28);
                            $("#codeQRCodeApplicationID").val(aklamator_infeed_api_id);
                            akla_login_window.close();
                            clearInterval(aklamator_interval);
                            $('#aklamator_infeed_inactive').css('display', 'none');
                        }
                    }, 1000);

                });

                if (!$("#ad_setting_box").is(":checked")) {
                    $('#adsetings').hide();
                }
                $("#ad_setting_box").click(function () {
                    if($(this).is(":checked")){
                        $('#adsetings').show();
                    }else{
                        $('#adsetings').hide();
                    }

                });

                if ($('table').hasClass('dynamicTable')) {
                    $('.dynamicTable').dataTable({
                        "iDisplayLength": 10,
                        "sPaginationType": "full_numbers",
                        "bJQueryUI": false,
                        "bAutoWidth": false

                    });
                }
            });

            $('.qr_qtip_icon').on('mouseover',function() {
                var id = $(this).attr('id');
                if ($('#qrcode' + id).html().length != 0)
                {
                    $('#qrcode' + id).css('display', 'block');
                }
                else {
                    var preview = '<div style="position: absolute; margin-top: -130px; margin-left: -50px; width: 130px; border: 2px solid #676767; border-radius: 4px; background-color: white;" class="qr_preview"><a href="https://qr.rs/download/' + $(this).attr('id') + '"><img style="width: 130px; margin: 0;" src="https://www.codeqrcode.com/img_qr_urls/' + $(this).attr('id') + '.png"/></a></div>';
                    $('#qrcode' + id).html(preview);
                }
            });
            $('.qr_qtip_icon').on('mouseleave',function() {
                var id = $(this).attr('id');
                $('#qrcode' + id).css('display', 'none');
            });
        </script>

    <?php
    }


}


new CodeqrcodeWidget();
