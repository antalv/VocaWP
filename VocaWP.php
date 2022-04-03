<?php
/**
 * Plugin Name: VocaWP
 * Description: VocaWP plugin
 * Author:      antalv
 * Version:     1.0
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if(!defined('ABSPATH')){
    die;
}

class VocaWP{

    public function __construct() {
        $this->actions();
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
        add_shortcode('voca_cats', array($this, 'voca_cats_shortcode'));
        add_shortcode('voca_cat', array($this, 'voca_cat_shortcode'));
    }

    public function actions(){
        add_action('init', array($this, 'VocaWP_post_types'));
        add_action('wp_enqueue_scripts', array($this,'enqueue_VocaWP_scripts'));
        add_action('wp_ajax_voca_cats', array($this, 'voca_cats_inner'));
        add_action('wp_ajax_voca__show_items', array($this, 'voca__show_items_function'));
        add_action('wp_ajax_voca__add_cat', array($this, 'voca__add_cat'));
        add_action('wp_ajax_voca__add_item', array($this, 'voca__add_item'));
        add_action('wp_ajax_voca__add_cat_function', array($this, 'voca__add_cat_function'));
        add_action('wp_ajax_voca__remove_cat_function', array($this, 'voca__remove_cat_function'));
        add_action('wp_ajax_voca__add_item_function', array($this, 'voca__add_item_function'));
        add_action('wp_ajax_voca__remove_item_function', array($this, 'voca__remove_item_function'));
        add_action('wp_ajax_voca__update_item_function', array($this, 'voca__update_function'));
        add_action('wp_ajax_voca__update_item', array($this, 'voca__update'));
    }

    static function activate(){
        flush_rewrite_rules();
    }

    static function deactivate(){
        flush_rewrite_rules();
    }

    public function VocaWP_post_types(){
        register_post_type('voca', [
            'label' => null,
            'labels' => [
                'name' => 'Vocas',
                'singular_name' => 'Vocas',
                'add_new' => 'Добавить voca',
                'add_new_item' => 'Добавление voca',
                'edit_item' => 'Редактирование voca',
                'new_item' => 'Новое voca',
                'view_item' => 'Смотреть voca',
                'search_items' => 'Искать voca',
                'not_found' => 'Не найдено',
                'not_found_in_trash' => 'Не найдено в корзине',
                'parent_item_colon' => '',
                'menu_name' => 'Voca',
            ],
            'description' => '',
            'public' => true,
            'show_in_menu' => null,
            'show_in_rest' => null,
            'rest_base' => null,
            'menu_position' => null,
            'menu_icon' => null,
            'hierarchical' => false,
            'supports' => ['title'],
            'taxonomies' => ['voca_tax'],
            'has_archive' => true,
            'rewrite' => true,
            'query_var' => true,
        ]);
        register_taxonomy( 'voca_tax', [ 'voca' ], [
            'label'                 => '',
            'labels'                => [
                'name'              => 'Cats',
                'singular_name'     => 'Cats',
                'search_items'      => 'Search Cats',
                'all_items'         => 'All Cat',
                'view_item '        => 'View Cat',
                'parent_item'       => 'Parent Cat',
                'parent_item_colon' => 'Parent Cat:',
                'edit_item'         => 'Edit Cat',
                'update_item'       => 'Update Cat',
                'add_new_item'      => 'Add New Cat',
                'new_item_name'     => 'New Cat Name',
                'menu_name'         => 'Cats',
                'back_to_items'     => '← Back to Cat',
            ],
            'description'           => '',
            'public'                => true,
            'hierarchical'          => true,
            'rewrite'               => true,
            'capabilities'          => array(),
            'meta_box_cb'           => null,
            'show_admin_column'     => false,
            'show_in_rest'          => null,
            'rest_base'             => null,
        ] );
    }

    public function enqueue_VocaWP_scripts(){
        wp_enqueue_style('styles_css', plugins_url( '/css/style.css', __FILE__));
        wp_enqueue_script( 'my-ajax-handle', plugins_url('/js/script.js', __FILE__ ), array( 'jquery' ));
        wp_localize_script( 'my-ajax-handle', 'ajax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
        wp_enqueue_script( 'voice', plugins_url('/js/voice.js', __FILE__ ), false,false,true);
    }

    public function voca_cats_shortcode(){
        echo
        '<div class="voca-cats__block">
            <h2 class="voca-cats__title">Voca Cats</h2>
            <div class="voca-cats"></div>
            <div class="voca-cats__add">Add Cat</div>
        </div>';
    }

    public function voca_cat_shortcode(){
        echo '<div class="voca-cat"></div>';
    }

    public function voca__show_items_function(){
        $args = array(
            'post_type' => 'voca',
            'taxonomy' => 'voca_tax',
            'voca_tax' => $_POST['slug'],
            'orderby' => 'date'
        );
        $vocaCat = get_term_by('slug', $_POST['slug'], 'voca_tax');
        $vocaCatID = $vocaCat->term_id;
        $vocaCatVoice = get_term_meta($vocaCatID, 'voice'); ?>
        <h2 class="voca-items__title"><?php echo $vocaCat->name ?></h2>
        <p class="voca-items__desc"><?php if ($vocaCat->description) { echo 'Description: <br>' . $vocaCat->description; } ?></p>
        <span class="voca-voice"><?php if($vocaCatVoice[0]){ echo 'Voice: '. $vocaCatVoice[0]; } ?></span>
        <?php $query = new WP_Query($args);
        if ($query->have_posts()) { ?>
            <div class="voca-items" data-voice="<?php echo $vocaCatVoice[0] ?>"> <?php
                while ($query->have_posts()) {
                    $query->the_post();
                    $vocaID = get_the_ID();
                    $vocaCats = get_the_terms($vocaID, 'voca_tax');
                    $vocaText = get_post_meta( $vocaID, 'text' );
                    $vocaTranslate = get_post_meta( $vocaID, 'translate' );
                    ?>
                    <div class="voca-item" data-id="<?php echo $vocaID ?>" data-slug="<?php echo $vocaCats[0]->slug ?>">
                        <div class="voca-item__content">
                            <div class="voca-item__play"></div>
                            <div class="voca-item__text"><?php echo $vocaText[0]; ?></div>
                            <div class="voca-item__translate"><?php echo $vocaTranslate[0] ?></div>
                        </div>
                        <div class="voca-item__buttons">
                            <div class="voca-item__edit"></div>
                            <div class="voca-item__remove"></div>
                        </div>
                    </div>

                <?php } ?>
            </div>

            <?php
        } else {
            echo 'No items';
        }?>
        <div class="voca-items__add" data-cat="<?php echo $vocaCatID ?>">Add Item</div>
        <?php
        wp_reset_postdata();
        wp_die();
    }

    public function voca_cats_inner(){
        $current_user = wp_get_current_user();
        $user_nickname = $current_user->nickname;
        $parentTerm = term_exists( $user_nickname, 'voca_tax' );
        if($parentTerm) { ?> <ul> <?php
            $parentTermID = $parentTerm['term_id'];
        $args = array(
            'hide_empty' => 0,
            'child_of' => $parentTermID,
            'orderby' => 'id'
        );
        $terms = get_terms('voca_tax', $args);
              foreach ($terms as $term){ ?>
                  <li class="voca-cats__item" data-id="<?php echo $term->term_id ?>" data-slug="<?php echo $term->slug ?>">
                      <span><?php echo $term->name ?></span>
                      <div class="voca-item__buttons">
                          <div class="voca-item__edit"></div>
                          <div class="voca-item__remove"></div>
                      </div>
                  </li>
              <?php } ?> </ul> <?php
        } else {
            echo 'no cats';
        }
        wp_die();
    }

    public function voca__add_cat() { ?>
        <div class="voca-cats__add-form">
            <h2>Add cat</h2>
                <input type="text" class="voca__cat-title" placeholder="Title">
                <select name="voca-voice" id="voca-voice"></select>
                <textarea name="" class="voca__cat-desc" id="" cols="30" rows="10" placeholder="Description"></textarea>
                <button type="submit">Add cat</button>
        </div>
        <?php wp_die();
    }

    public function voca__add_cat_function() {
        $term_title = $_POST['title'];
        $term_desc = $_POST['desc'];
        $term_voice = $_POST['voice'];
        $current_user = wp_get_current_user();
        $user_nickname = $current_user->nickname;
        $parentTerm = term_exists( $user_nickname, 'voca_tax' );
        if($parentTerm == 0 && $term == null){
            wp_insert_term($user_nickname, 'voca_tax');
            $parentTerm = term_exists( $user_nickname, 'voca_tax' );
        }
        $parentTermID = $parentTerm['term_id'];
        $args =  array(
            'slug'        => $user_nickname . '-' . $term_title,
            'parent'      => $parentTermID,
            'description' => $term_desc,
        );
        $term_insert = wp_insert_term($term_title, 'voca_tax', $args);
        $term_id = $term_insert['term_id'];
        add_term_meta($term_id, 'voice', $term_voice);
        wp_die();
    }

    public function voca__remove_cat_function(){
        $termID = $_POST['id'];
        wp_delete_term($termID, 'voca_tax');
    }

    public function voca__add_item() {
        $dataCat = $_POST['cat'];
        $dataCatTerm = get_term_by('id', $dataCat, 'voca_tax');
        $dataCatSlug = $dataCatTerm->slug;
        ?>
        <div class="voca-items__add-form">
            <h2>Add item</h2>
            <input type="text" class="voca__item-text" placeholder="Text">
            <input type="text" class="voca__item-translate" placeholder="Translate">
            <button data-cat="<?php echo $dataCat ?>" data-slug="<?php echo $dataCatSlug ?>" >Add item</button>
        </div>
        <?php wp_die();
    }

    public function voca__add_item_function() {
        $post_text = $_POST['text'];
        $post_translate = $_POST['translate'];
        $post_cat = $_POST['cat'];
        $args = array(
            'post_title' => $post_text,
            'post_status' => 'publish',
            'post_type' => 'voca',
            'tax_input' => array( 'voca_tax' => $post_cat),
            'meta_input'    => array(
                'text' => $post_text,
                'translate' => $post_translate
            ),
        );
        wp_insert_post($args);
        wp_die();
    }

    public function voca__remove_item_function(){
        $postID = $_POST['id'];
        wp_delete_post($postID);
    }

}


if(class_exists('VocaWP')){
    $VocaWP = new VocaWP();
}