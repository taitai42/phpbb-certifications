<?php
/**
 *
 * @package       phpBB Extension - Acme Demo
 * @copyright (c) 2013 phpBB Group
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace taitai42\certifications\controller;

class main
{
    /* @var \phpbb\config\config */
    protected $config;

    /* @var \phpbb\controller\helper */
    protected $helper;

    /* @var \phpbb\template\template */
    protected $template;

    /* @var \phpbb\user */
    protected $user;
    /**
     * @var \phpbb\db\driver\driver_interface
     */
    private $db;

    /**
     * Constructor
     *
     * @param \phpbb\config\config     $config
     * @param \phpbb\controller\helper $helper
     * @param \phpbb\template\template $template
     * @param \phpbb\user              $user
     */
    public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user)
    {
        $this->config = $config;
        $this->helper = $helper;
        $this->template = $template;
        $this->user = $user;
        $this->db = $db;
    }


    public function handle()
    {
        return $this->helper->render('certifications_body.html');
    }


    public function submit()
    {
        return $this->helper->render('certifications_body.html');
    }


    public function manage()
    {
        global $table_prefix;

        $sql = "select i.*, u.username as user from {$table_prefix}certifications_interviews  i 
        left JOIN ".USERS_TABLE." u on u.user_id = i.user_id
          where " . $this->db->sql_build_array("SELECT", ['i.interviewer_id' => $this->user->data['user_id']]);;
        $result = $this->db->sql_query($sql);
        $results = [];
        while ($row = $this->db->sql_fetchrow($result)) {
            $results[] = $row;
        }

        $sql = "select * from {$table_prefix}certifications_creneaux where " . $this->db->sql_build_array("SELECT", ['user_id' => $this->user->data['user_id']]);;
        $result = $this->db->sql_query($sql);
        $creneaux = [];
        while ($row = $this->db->sql_fetchrow($result)) {
            $creneaux[] = $row;
        }

        echo '<pre>';var_dump($creneaux, $results);echo '</pre>';
        $this->template->assign_vars([
            'U_MANAGEMENT_PAGE' => true,
            'interview_list'    => $results,
            'creneaux_list'     => $creneaux,
        ]);

        return $this->helper->render('management_body.html');
    }

    public function saveCreneaux() {
        global $symfony_request;

        echo '<pre>';var_dump($symfony_request->request->get('slot'));die;
    }
}
