<?php
/**
*
* @package phpBB Extension - Acme Demo
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'CERTIFICATIONS_PAGE'			=> 'certifications',
    'SLOT_NOT_FILLED' => 'slot is not filled',
    'PIC_MISSING' => 'picture is missing',
    'NO_REQUIRE_DATA' => 'vous ne remplissez pas les conditions requises pour etre certifie',
    'NO_USER_FILE' => 'impossible d\'inclure les fichiers necessaires, contactez un administrateur',
    'NO_CERTIF_GROUP' => 'Vous ne faites pas partie du comite de certification',
));
