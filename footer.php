<?php
/**
 * Plugin Name: Footer
 * Plugin URI: none
 * Description: Footer links
 * Version: 1.0
 * Author: Ruslan Beytiyev
 * Author URI: https://github.com/sp33dst3r
 */
function pre($data){
    echo "<pre>", print_r($data), "</pre>";
}

add_action('wp_ajax_add', 'add_action_function');
add_action('wp_ajax_delete', 'delete_action_function');
function delete_action_function(){
    global $wpdb;
    $response=[];
    $id = (int)$_POST["id"];
    if($id){
        $wpdb->query( $wpdb->prepare( 
            "
                DELETE FROM `wp_pf_parts`  WHERE id = %d
               
            ", 
            $id) );
             $response['status'] = 'ok';
    }else{
        $response['status'] = 'error';
    }
   
    echo json_encode($response);

    exit();
}

function add_action_function(){


    global $wpdb;
    $response=[];
    $post_id = (int)trim(strip_tags($_POST["post_id"])); 
    $href = trim(strip_tags($_POST["href"])); 
    if(!$post_id || !$href)  {
        $response["status"] = "error";
        $response["message"] = "wrong_data";
        echo json_encode($response);
        exit();
        
    }
    if(isset($_POST["record-id"]) && (int)$_POST["record-id"] > 0){
        $res = $wpdb->query( $wpdb->prepare( 
            "
                UPDATE `wp_pf_parts` set
                 post_id = %d, link =  %s
                WHERE id = %d
            ", 
            $post_id, 
            $href, (int)$_POST["record-id"]) );
    }else{
        $wpdb->query( $wpdb->prepare( 
            "
                INSERT INTO `wp_pf_parts`
                ( post_id, link )
                VALUES ( %d, %s )
            ", 
            $post_id, 
            $href) );
            $response['record_id'] = $wpdb->insert_id;
    }
   
    $response['status'] = 'ok';
    $response['message'] = 'data changed successfully';
    echo  json_encode($response);
  
    //Don't forget to always exit in the ajax function.
    exit();

}

function getAvailableAnchors()
{
    global $wpdb;
    $anchors = $wpdb->get_results(  
        "
            SELECT id, post_id, link FROM wp_pf_parts
        ") ;

        return $anchors;
    
}


add_action('admin_menu', 'add_menu_item');
 
function add_menu_item(){
        add_menu_page( 'Test Plugin Page', 'Footer Links', 'manage_options', 'test-plugin', 'test_init' );
}

function getAvailablePosts()
{
       $articles = get_posts(
        array(
         'numberposts' => -1,
         'post_status' => 'any',
         'post_type' => 'post',
        )
       );
       return $articles; 

    

}



register_activation_hook( __FILE__, 'pf_rb_install' );
function pf_rb_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pf_parts';
    $pf_parts_db_version = '1.0.0';
    $charset_collate = $wpdb->get_charset_collate();

    if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) != $table_name ) {

        $sql = "CREATE TABLE $table_name (
                        id mediumint(9) NOT NULL AUTO_INCREMENT,
                        post_id mediumint(9) NOT NULL UNIQUE,
                        link varchar(255) NOT NULL,
                        PRIMARY KEY  (id)
                        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        add_option( 'pf_parts_db_version', $pf_parts_db_version );
    }
}


wp_enqueue_script( 'js', plugins_url( '/js/main.js', __FILE__ ));
wp_enqueue_style( 'css', plugins_url( '/css/main.css', __FILE__ ));


function test_init(){
    $posts = getAvailablePosts();
    $anchors = getAvailableAnchors();
    //pre($anchors);
    $options = "";
    foreach($posts as $post){
        $options .= "<option value='".$post->ID."'>".$post->post_title."</option>";
    }
    //pre($options);
    
    
    

    echo "<h1>Footer Links</h1>";
    echo 
    "<div id='template'><div class='link-block'>
        <form>
            <div>
                <label style='display: block;'>Link href</label>
                <input name='link-href'  />
            </div>
            <div>
                <label style='display: block;'>Post</label>
                <select name='post_id'>$options</select>
            </div>
            <div>
                <button type='submit'>Save</button>
                <button class='delete-item' type='button'>Delete</button>
            </div>
        </form>
    
    </div></div>";
    
    echo '<div id="link-container">';
    if(count($anchors)){
        foreach($anchors as $anchor){
            echo 
            "<div ><div class='link-block'>
                <form data-record-id=".$anchor->id.">
                    <div>
                        <label style='display: block;'>Link href</label>
                        <input name='link-href' value='".$anchor->link."' />
                    </div>
                    <div>
                        <label style='display: block;'>Post</label>
                        <select data-post='".$anchor->post_id."' name='post_id'>$options</select>
                    </div>
                    <div>
                        <button type='submit'>Save</button>
                        <button class='delete-item' type='button'>Delete</button>
                    </div>
                </form>
            
            </div></div>";
        }
    }
    echo "</div>";



    echo "<div class='add-more'>Add more</div>";
}

add_action('wp_footer', 'footer');
function getLink($post_id){
    global $wpdb;
   $post = get_post($post_id);
   return $post->guid;
}
function footer() {
   

    $posts = getAvailablePosts();
    $anchors = getAvailableAnchors();
    echo "<ul>";
        foreach($anchors as $anchor){
            echo "<li><a href='".getLink($anchor->post_id)."'>$anchor->link</a></li>";

        }
    echo "</ul>";
    
}
