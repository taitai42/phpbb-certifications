<?php
/**
 *
 * @package       phpBB Extension - Acme Demo
 * @copyright (c) 2013 phpBB Group
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace taitai42\certifications\controller;

use DateTime;
use phpbb\routing\router;

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
        global $table_prefix;

        $timestamp_start = strtotime("monday this week");
        $timestamp_end = strtotime("monday next week");

        $this->prepareInterviews($table_prefix, $timestamp_start, $timestamp_end);

        $this->prepareSlots($table_prefix, $timestamp_start, $timestamp_end);

        return $this->helper->render('certifications_body.html');
    }


    public function submit()
    {
        return $this->helper->render('certifications_body.html');
    }


    public function manage()
    {
        global $table_prefix;

        $timestamp_start = strtotime("monday this week");
        $timestamp_end = strtotime("monday next week");

        $this->prepareManageInterviews($table_prefix, $timestamp_start, $timestamp_end);

        $this->prepareManageSlots($table_prefix, $timestamp_start, $timestamp_end);

        $this->template->assign_vars([
            'U_MANAGEMENT_PAGE' => true,
        ]);

        return $this->helper->render('management_body.html');
    }

    public function saveCreneaux()
    {
        global $table_prefix;
        global $symfony_request;

        $timestamp_start = strtotime("monday this week");
        $timestamp_end = strtotime("monday next week");
        $sql = "delete from {$table_prefix}certifications_creneaux where " . $this->db->sql_build_array("SELECT", [
                'user_id' => $this->user->data['user_id'],
            ]) . " and date_start > $timestamp_start 
                   and date_end < $timestamp_end";

        $this->db->sql_query($sql);
        foreach (array_values($symfony_request->request->get('slot', [])) as $slot) {
            if ($slot['date_start'] == 0 || $slot['date_end'] == 0 || $slot['time_start'] == 0 || $slot['time_end'] == 0)
                continue;
            $timestamp_start = DateTime::createFromFormat('d/m/Y H:i', $slot['date_start'] . " " . $slot['time_start'], new \DateTimeZone($this->user->data['user_timezone']));
            $timestamp_end = DateTime::createFromFormat('d/m/Y H:i', $slot['date_end'] . " " . $slot['time_end'], new \DateTimeZone($this->user->data['user_timezone']));
            $sql = "insert into {$table_prefix}certifications_creneaux values (null, {$timestamp_start->getTimestamp()}, {$timestamp_end->getTimestamp()}, {$this->user->data['user_id']})";
            $this->db->sql_query($sql);
        }

        return redirect('/certification/manage/');
    }

    /**
     * @param $table_prefix
     * @param $timestamp_start
     * @param $timestamp_end
     */
    public function prepareSlots($table_prefix, $timestamp_start, $timestamp_end)
    {
        $sql = "select * from {$table_prefix}certifications_creneaux where 
             date_start > $timestamp_start 
            and date_end < $timestamp_end";

        $result = $this->db->sql_query($sql);
        $i = 0;

        while ($row = $this->db->sql_fetchrow($result)) {
            $date_start = (new DateTime())->setTimestamp($row['date_start']);
            $date_end = (new DateTime())->setTimestamp($row['date_end']);
            $this->template->assign_block_vars('creneaux', [
                'creneaux_id'         => $row['creneaux_id'],
                'date_start' => $date_start->format('d/m/Y'),
                'date_end'   => $date_end->format('d/m/Y'),
                'time_start' => $date_start->format('H:i'),
                'time_end'   => $date_end->format('H:i'),
            ]);
            $i++;
        }
    }

    /**
     * @param $table_prefix
     * @param $timestamp_start
     * @param $timestamp_end
     */
    public function prepareInterviews($table_prefix, $timestamp_start, $timestamp_end)
    {
        $sql = "select i.*, u.username as user from {$table_prefix}certifications_interviews  i 
        left JOIN " . USERS_TABLE . " u on u.user_id = i.interviewer_id
          where " . $this->db->sql_build_array("SELECT", ['i.user_id' => $this->user->data['user_id']]) .
            " and date_start > $timestamp_start 
              and date_end < $timestamp_end";

        $result = $this->db->sql_query($sql);

        while ($row = $this->db->sql_fetchrow($result)) {
            $this->template->assign_block_vars('interviews', [
            ]);
        }
    }

    /**
     * @param $table_prefix
     * @param $timestamp_start
     * @param $timestamp_end
     *
     * @return array
     */
    public function prepareManageInterviews($table_prefix, $timestamp_start, $timestamp_end)
    {
        $sql = "select i.*, u.username as user from {$table_prefix}certifications_interviews  i 
        left JOIN " . USERS_TABLE . " u on u.user_id = i.user_id
          where " . $this->db->sql_build_array("SELECT", ['i.interviewer_id' => $this->user->data['user_id']]) .
            " and date_start > $timestamp_start 
              and date_end < $timestamp_end";
        $result = $this->db->sql_query($sql);
        while ($row = $this->db->sql_fetchrow($result)) {
            $this->template->assign_block_vars('interviews', [
            ]);
        }

    }

    /**
     * @param $table_prefix
     * @param $timestamp_start
     * @param $timestamp_end
     */
    public function prepareManageSlots($table_prefix, $timestamp_start, $timestamp_end)
    {
        $sql = "select * from {$table_prefix}certifications_creneaux where " .
            $this->db->sql_build_array("SELECT", ['user_id' => $this->user->data['user_id']]) .
            " and date_start > $timestamp_start 
              and date_end < $timestamp_end";
        $result = $this->db->sql_query($sql);
        $i = 0;

        while ($row = $this->db->sql_fetchrow($result)) {
            $date_start = (new DateTime())->setTimestamp($row['date_start']);
            $date_end = (new DateTime())->setTimestamp($row['date_end']);
            $this->template->assign_block_vars('creneaux', [
                'date_start' => $date_start->format('d/m/Y'),
                'date_end'   => $date_end->format('d/m/Y'),
                'time_start' => $date_start->format('H:i'),
                'time_end'   => $date_end->format('H:i'),
            ]);
            $i++;
        }
    }
}
