<?php
/**
 *
 * @package       phpBB Extension - Acme Demo
 * @copyright (c) 2013 phpBB Group
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace taitai42\certifications\controller;

use phpbb\datetime;
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
        global $table_prefix;
        $this->config = $config;
        $this->helper = $helper;
        $this->template = $template;
        $this->user = $user;
        $this->db = $db;
        $this->table_prefix = $table_prefix;

    }


    public function handle()
    {
        $timestamp_start = strtotime("monday this week");
        $timestamp_end = strtotime("monday next week");

        $this->prepareInterviews($timestamp_start, $timestamp_end);

        $this->prepareSlots($timestamp_start, $timestamp_end);

        return $this->helper->render('certifications_body.html');
    }


    public function submit()
    {
        global $symfony_request;


        $slot = $symfony_request->request->get('slot', "");
        $file = $symfony_request->files->get('id', null);

        $this->checkParameters($slot, $file);
        $filename = md5($this->user->data['user_id']).'_certif.'.$file->getClientOriginalExtension();
        $file->move('./images/avatars/upload', $filename);

        // look for wanted slot
        $sql = "select * from {$this->table_prefix}certifications_creneaux where creneaux_id = " . (int) $slot;
        $result = $this->db->sql_query($sql);
        $slotresult = $this->db->sql_fetchrow($result);

        // delete current interview
        $sql = "delete from {$this->table_prefix}certifications_interviews where user_id = " . (int) $this->user->data['user_id'];
        $result = $this->db->sql_query($sql);

        // insert new interview
        $sql = "insert into {$this->table_prefix}certifications_interviews values(null, 
        ". (int) $slot .", "
            . (int) $this->user->data['user_id'] . ","
            . (int) $slotresult['user_id'] .",
            'images/avatars/upload/". $filename ."')";
        $this->db->sql_query($sql);

        return redirect("/certifications");
    }


    public function manage()
    {
        $timestamp_start = strtotime("monday this week");
        $timestamp_end = strtotime("monday next week");

        $this->prepareManageInterviews($timestamp_start, $timestamp_end);

        $this->prepareManageSlots($timestamp_start, $timestamp_end);

        $this->template->assign_vars([
            'U_MANAGEMENT_PAGE' => true,
        ]);

        return $this->helper->render('management_body.html');
    }

    public function saveCreneaux()
    {
        global $symfony_request;

        $timestamp_start = strtotime("monday this week");
        $timestamp_end = strtotime("monday next week");

        $this->removeOldSlots($timestamp_start, $timestamp_end);

        $dates = $this->getDatesFromRequest($symfony_request);

        foreach ($dates as $date) {
            $timestamp_start = $date['start'];
            $timestamp_end = $date['end'];

            while ($timestamp_start != $timestamp_end) {
                $sql = "insert into {$this->table_prefix}certifications_creneaux values (null, {$timestamp_start->getTimestamp()}, {$timestamp_start->add(new \DateInterval('PT30M'))->getTimestamp()}, {$this->user->data['user_id']})";
                $this->db->sql_query($sql);
            }
        }

        return redirect('/certification/manage/');
    }

    /**
     * @param $table_prefix
     * @param $timestamp_start
     * @param $timestamp_end
     */
    public function prepareSlots($timestamp_start, $timestamp_end)
    {
        $sql = "select c.* from {$this->table_prefix}certifications_creneaux c
              left join {$this->table_prefix}certifications_interviews i on c.creneaux_id = i.creneaux_id where 
             c.date_start > $timestamp_start 
            and c.date_end < $timestamp_end and i.creneaux_id IS NULL ORDER by c.date_start";

        $result = $this->db->sql_query($sql);
        $i = 0;

        while ($row = $this->db->sql_fetchrow($result)) {
            $date_start = (new datetime($this->user))->setTimestamp($row['date_start']);
            $date_end = (new datetime($this->user))->setTimestamp($row['date_end']);

            $this->template->assign_block_vars('creneaux', [
                'creneaux_id' => $row['creneaux_id'],
                'date_start'  => $date_start->format('l j F'),
                'time_start'  => $date_start->format('H:i'),
                'time_end'    => $date_end->format('H:i'),
                'date_end'    => $date_end->format('d/m/Y'),
            ]);
            $i++;
        }
    }

    /**
     * @param $table_prefix
     * @param $timestamp_start
     * @param $timestamp_end
     */
    public function prepareInterviews($timestamp_start, $timestamp_end)
    {
        $sql = "select i.*, u.* , c.* from {$this->table_prefix}certifications_interviews  i 
        left JOIN " . USERS_TABLE . " u on u.user_id = i.user_id
        left join {$this->table_prefix}certifications_creneaux c on i.creneaux_id = c.creneaux_id
          where " . $this->db->sql_build_array("SELECT", ['i.user_id' => $this->user->data['user_id']]) .
            " and c.date_start > $timestamp_start 
              and c.date_end < $timestamp_end";

        $result = $this->db->sql_query($sql);

        while ($row = $this->db->sql_fetchrow($result)) {
            $date = (new datetime($this->user))->setTimestamp($row['date_start']);
            $this->template->assign_var('interview', array_merge($row, [
                'date' => $date->format('l j F \a H:i'),
            ]));
        }
    }

    /**
     * @param $table_prefix
     * @param $timestamp_start
     * @param $timestamp_end
     *
     * @return array
     */
    public function prepareManageInterviews($timestamp_start, $timestamp_end)
    {
        $sql = "select i.*, u.*, c.* from {$this->table_prefix}certifications_interviews  i 
        left JOIN " . USERS_TABLE . " u on u.user_id = i.user_id
        left join {$this->table_prefix}certifications_creneaux c on i.creneaux_id = c.creneaux_id
          where " . $this->db->sql_build_array("SELECT", ['i.interviewer_id' => $this->user->data['user_id']]) .
            " and c.date_start > $timestamp_start 
              and c.date_end < $timestamp_end";
        $result = $this->db->sql_query($sql);
        while ($row = $this->db->sql_fetchrow($result)) {
            $date = (new datetime($this->user))->setTimestamp($row['date_start']);


            $this->template->assign_block_vars('interviews',  array_merge($row, [
                'date' => $date->format('l j F \a H:i'),
            ]));
        }

    }

    /**
     * @param $table_prefix
     * @param $timestamp_start
     * @param $timestamp_end
     */
    public function prepareManageSlots($timestamp_start, $timestamp_end)
    {
        $sql = "select * from {$this->table_prefix}certifications_creneaux where " .
            $this->db->sql_build_array("SELECT", ['user_id' => $this->user->data['user_id']]) .
            " and date_start > $timestamp_start 
              and date_end < $timestamp_end";
        $result = $this->db->sql_query($sql);
        $i = 0;

        while ($row = $this->db->sql_fetchrow($result)) {
            $date_start = (new datetime($this->user))->setTimestamp($row['date_start']);
            $date_end = (new datetime($this->user))->setTimestamp($row['date_end']);
            $this->template->assign_block_vars('creneaux', [
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
     *
     * @return string
     */
    public function removeOldSlots($timestamp_start, $timestamp_end)
    {
        $sql = "delete from {$this->table_prefix}certifications_creneaux where " . $this->db->sql_build_array("SELECT", [
                'user_id' => $this->user->data['user_id'],
            ]) . " and date_start > $timestamp_start 
                   and date_end < $timestamp_end";

        $this->db->sql_query($sql);

        return $sql;
    }

    /**
     * @param $symfony_request
     *
     * @return array
     */
    public function getDatesFromRequest($symfony_request)
    {
        $dates = [];

        $params = array_values($symfony_request->request->get('slot', []));
        foreach ($params as $slot) {
            // echo '<pre>';var_dump(array_values($symfony_request->request->get('slot', [])));die;
            if ($slot['date_start'] === "" || $slot['date_end'] === "" || $slot['time_start'] === "" || $slot['time_end'] === "")
                continue;
            $timestamp_start = DateTime::createFromFormat('d/m/Y H:i', $slot['date_start'] . " " . $slot['time_start'], new \DateTimeZone($this->user->data['user_timezone']));
            $timestamp_end = DateTime::createFromFormat('d/m/Y H:i', $slot['date_end'] . " " . $slot['time_end'], new \DateTimeZone($this->user->data['user_timezone']));
            $dates[] = [
                'start' => $timestamp_start,
                'end'   => $timestamp_end,
            ];
        }

        return $this->removeOverlappedDates($dates);
    }

    private function removeOverlappedDates($dates)
    {
        for ($i = 0; $i < count($dates); $i++) {
            $j = $i + 1;
            if (!isset($dates[$j])) {
                break;
            }
            if ($dates[$j]['start']->getTimestamp() >= $dates[$i]['start']->getTimestamp() &&
                $dates[$j]['start']->getTimestamp() < $dates[$i]['end']->getTimestamp()
            ) {
                if ($dates[$j]['end']->getTimestamp() > $dates[$i]['end']->getTimestamp()) {
                    $dates[$i]['end'] = $dates[$j]['end'];
                }
                unset($dates[$j]);

                return $this->removeOverlappedDates(array_values($dates));
            }
        }

        return $dates;
    }

    private function checkCreneaux($slot)
    {
        $sql = "select * from {$this->table_prefix}certifications_interviews where creneaux_id = " . (int) $slot;
        $result = $this->db->sql_query($sql);
        if ($result->num_rows !== 0) {
            trigger_error('SLOT_TAKEN');
        }
    }

    /**
     * @param $slot
     * @param $file
     */
    public function checkParameters($slot, $file)
    {
        if (!$slot) {
            trigger_error('SLOT_NOT_FILLED');
        }
        if (!$file) {
            trigger_error('PIC_MISSING');
        }

        $authorizedMimes = ["image/jpeg", "image/png"];

        if (!in_array($file->getMimeType(), $authorizedMimes, true)) {
            trigger_error('INVALID_FILE');
        }


        $this->checkCreneaux($slot);
    }
}