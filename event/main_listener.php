<?php
/**
 *
 * @package       phpBB Extension - Acme Demo
 * @copyright (c) 2013 phpBB Group
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace taitai42\certifications\event;

/**
 * @ignore
 */
use phpbb\user;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use taitai42\certifications\config\config;

/**
 * Event listener
 */
class main_listener implements EventSubscriberInterface
{
    static public function getSubscribedEvents()
    {
        return [
            'core.user_setup'  => 'load_language_on_setup',
            'core.page_header' => 'add_page_header_link',
        ];
    }

    /* @var \phpbb\controller\helper */
    protected $helper;

    /* @var \phpbb\template\template */
    protected $template;

    /**
     * Constructor
     *
     * @param \phpbb\controller\helper $helper   Controller helper object
     * @param \phpbb\template\template $template Template object
     */
    public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, user $user)
    {
        $this->helper = $helper;
        $this->template = $template;
        $this->user = $user;
    }

    public function load_language_on_setup($event)
    {
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = [
            'ext_name' => 'taitai42/certifications',
            'lang_set' => 'common',
        ];
        $event['lang_set_ext'] = $lang_set_ext;
    }

    public function add_page_header_link($event)
    {
        if (file_exists('includes/functions_user.php')) {
            require_once 'includes/functions_user.php';

            $canSee = in_array($this->user->data['group_id'], [2, 3, 7]) && $this->user->data['user_gender'] == 2 && $this->user->data['user_posts'] >= config::MIN_MESSAGES;
            if ($this->user->data['user_id'] != ANONYMOUS) {
                $this->template->assign_vars([
                    'U_CERTIFICATIONS_PAGE'      => $this->helper->route('certifications_user'),
                    'U_CAN_SEE'                  => $canSee,
                    'U_MANAGER'                  => group_memberships(config::CERTIFICATION_GROUP, $this->user->data['user_id'], true),
                    'U_CERTIFICATION_MANAGEMENT' => $this->helper->route('certifications_management'),
                ]);
            }
        }

    }
}
