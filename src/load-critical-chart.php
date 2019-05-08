<?php
/*
 * Copyright 2005-2019 CENTREON
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once '../../require.php';
require_once $centreon_path . 'bootstrap.php';
require_once $centreon_path . 'www/class/centreon.class.php';
require_once $centreon_path . 'www/class/centreonSession.class.php';
require_once $centreon_path . 'www/class/centreonWidget.class.php';
require_once $centreon_path . 'www/class/centreonUtils.class.php';
require_once $centreon_path . 'www/class/centreonACL.class.php';
require_once $centreon_path . 'www/class/centreonHost.class.php';
require_once $centreon_path . 'www/class/centreonService.class.php';

CentreonSession::start(1);
if (!isset($_SESSION['centreon']) || !isset($_REQUEST['widgetId'])) {
    exit;
}

$centreon = $_SESSION['centreon'];
$widgetId = $_REQUEST['widgetId'];

// Init DB
$centreonDB = $dependencyInjector['configuration_db'];
$monitoringDB = $dependencyInjector['realtime_db'];
if (CentreonSession::checkSession(session_id(), $centreonDB) == 0) {
    exit();
}


// Init Smarty
$path = $centreon_path . 'www/widgets/centreon-administrator-dashboard/src/';
$template = new Smarty();
$template = initSmartyTplForPopup($path, $template, "/", $centreon_path);

// Build query
$query = "SELECT SQL_CALC_FOUND_ROWS count(distinct(service_id)) criticalServices FROM services s, hosts h WHERE s.state='2' AND s.state_type = 1";

// Handle data for non admin users
if (!$centreon->user->admin) {
    $pearDB = $centreonDB;
    $aclObj = new CentreonACL($centreon->user->user_id, $centreon->user->admin);
    $groupList = $aclObj->getAccessGroupsString();
    $query .= ' AND EXISTS (
                SELECT a.service_id
                FROM centreon_acl a
                WHERE a.host_id = h.host_id
                AND a.service_id = s.service_id
                AND a.group_id
                IN ('. $groupList . ')
            )';
}

$res = $monitoringDB->prepare($query);
$res->execute();
$row = $res->fetch();

// Send data to smarty tpl
$template->assign('criticalServices', $row);
$template->display('load-critical-chart.ihtml');

?>
