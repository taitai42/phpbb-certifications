<?php
/**
 *
 * @package       Genders
 * @copyright (c) 2015 Rich McGirr (RMcGirr83)
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace taitai42\certifications\migrations;

class m1_initial_schema extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return $this->db_tools->sql_table_exists($this->table_prefix . 'certifications_interviews')
            && $this->db_tools->sql_table_exists($this->table_prefix . 'certifications_creneaux');
    }

    static public function depends_on()
    {
        return ['\phpbb\db\migration\data\v31x\v314rc1'];
    }

    public function update_schema()
    {
        return [
            'add_tables' => [
                $this->table_prefix . 'certifications_creneaux'   => [
                    'COLUMNS'     => [
                        'creneaux_id' => ['UINT', null, 'auto_increment'],
                        'timeslots'   => ['TEXT', ''],
                        'user_id'     => ['UINT', null],
                    ],
                    'PRIMARY_KEY' => 'creneaux_id',
                ],
                $this->table_prefix . 'certifications_interviews' => [
                    'COLUMNS'     => [
                        'interview_id'   => ['UINT', null, 'auto_increment'],
                        'timeslot'       => ['TEXT', ''],
                        'user_id'        => ['UINT', null],
                        'interviewer_id' => ['UINT', null],
                    ],
                    'PRIMARY_KEY' => 'interview_id',
                ],
            ],
        ];
    }

    public function revert_schema()
    {
        return [
            'drop_tables' => [
                $this->table_prefix . 'certifications_creneaux',
                $this->table_prefix . 'certifications_interviews',
            ],
        ];
    }
}
