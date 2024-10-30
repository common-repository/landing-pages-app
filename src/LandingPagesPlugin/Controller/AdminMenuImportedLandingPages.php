<?php
namespace LandingPages\Wordpress\Plugin\LandingPagesPlugin\Controller;

use LandingPages\Wordpress\Plugin\Framework\Kernel\PluginPartInterface;
use LandingPages\Wordpress\Plugin\Framework\Wrapper\AdminMenuTrait;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\Model\LandingPostType;

class AdminMenuImportedLandingPages implements PluginPartInterface
{
    use AdminMenuTrait;

    const ACTION_TAG = 'admin_menu';
    const PAGE_TITLE = 'Imported Landing Pages';
    const SUBMENU_TITLE = 'Imported Landing Pages';
    const CAPABILITY = 'manage_options';

    public function noticeHomepageSuccess() {
        ?>
        <div class="notice notice-success is-dismissible">
            <p>Homepage successfully changed!</p>
        </div>
        <?php
    }

    public function setAsHomepage()
    {
        add_action('load-edit.php', function() {
            if (!isset($_REQUEST['post_type']) || $_REQUEST['post_type'] != 'landing') {
                return;
            }

            if (!isset($_REQUEST['land']) || empty($_REQUEST['land'])) {
                return;
            }

            $action = strip_tags((string)wp_unslash($_REQUEST['land']));

            if ($action == 'setashomepage') {
                $post_name = strip_tags((string)wp_unslash($_REQUEST['post_name']));

                if (empty($post_name)) {
                    return;
                }

                $landing_title = 'Landing Pages App';
                $landing_check = get_page_by_title($landing_title);
                $landing_check_id = isset($landing_check->ID) ? $landing_check->ID : 0;
                $landing_homepage = [
                    'post_type'     => 'page',
                    'post_status'   => 'publish',
                    'post_title'    => $landing_title,
                    'post_content'  => $post_name,
                    'post_author'   => 1,
                    'post_slug'     => 'home',
                    'page_template' => 'fp-landing-template.php',
                    'ID'            => $landing_check_id
                ];

                $landing_id = wp_insert_post($landing_homepage);
                update_option('page_on_front', $landing_id);
                update_option('show_on_front', 'page');

                add_action('admin_notices', [$this, 'noticeHomepageSuccess']);
            }
        });
    }

    public function initialize()
    {
        $this->addPostTypeInSubMenu(AdminMenuAvailableLandingPages::MENU_SLUG, LandingPostType::POST_TYPE);
        $this->setAsHomepage();
    }
}
