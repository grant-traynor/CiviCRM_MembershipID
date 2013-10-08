<?php

require_once 'membershipid.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function membershipid_civicrm_config(&$config) {
  _membershipid_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function membershipid_civicrm_xmlMenu(&$files) {
  _membershipid_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function membershipid_civicrm_install() {
  return _membershipid_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function membershipid_civicrm_uninstall() {
  return _membershipid_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function membershipid_civicrm_enable() {
  return _membershipid_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function membershipid_civicrm_disable() {
  return _membershipid_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function membershipid_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _membershipid_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function membershipid_civicrm_managed(&$entities) {
  return _membershipid_civix_civicrm_managed($entities);
}

/**
 * GKT - Implement hook to catch membership additions / changes
 * and update the memerbship id accordingly.
 */
function membershipid_civicrm_post( $op, $objectName, $objectId, &$objectRef )
{
   if ($op == 'create' && $objectName == 'Membership') 
   {

      /* Debugging log */
      $DEBUG=TRUE;

      /* Log File */
      $file = '/tmp/gktTestMembershipPlugin.log';

      /* Get the contact record from the database */
      $params = array( 'id' => $objectRef->contact_id, 'version' => 3,);

      $result = civicrm_api( 'contact', 'get', $params);
      if( $result['is-error'] = 0 )
      {
         file_put_contents( $file, "GKT: Error retrieving contact {$objectRef->contact_id} from database\n", FILE_APPEND );
         return;
      }
      if( $result['count'] != 1 )
      {
         file_put_contents( $file, "GKT: Got {$result['count']} contacts with ID {$objectRef->contact_id}\n", FILE_APPEND );
         return;
      }

      if ($DEBUG )
      {
         $firstname = $result['values'][$objectRef->contact_id]['first_name'];
         $lastname  = $result['values'][$objectRef->contact_id]['last_name'];
         $strOut  = "Got membership change for contact ";
         $strOut .= "{$firstname} {$lastname} ";
         $strOut .= "with ID {$objectRef->contact_id}\n";
         file_put_contents( $file, $strOut, FILE_APPEND );
      }
   
      /* First, get the table name for the custom data group "Membership_Specifics" */
      $params = array(
        'version' => 3,
        'page' => 'CiviCRM',
        'q' => 'civicrm/ajax/rest',
        'sequential' => 1,
        'name' => 'Membership_Specifics',
      );
      $membershipSpecificsGroup = civicrm_api('CustomGroup', 'get', $params);
      $membershipSpecGroupId = $membershipSpecificsGroup['values'][0]['id'];
      $memhershipSpecificsTable = $membershipSpecificsGroup['values'][0]['table_name']; 

      /* Now get the Column Name and ID for "Membership_ID" in that table */
      $params = array(
        'version' => 3,
        'page' => 'CiviCRM',
        'q' => 'civicrm/ajax/rest',
        'sequential' => 1,
        'custom_group_id' => $membershipSpecGroupId,
        'name' => 'Membership_ID',
      );
      $columnNames = civicrm_api('CustomField', 'get', $params);
      $columnName = $columnNames['values'][0]['column_name'];
      $columnId = $columnNames['values'][0]['id'];

      if( $DEBUG )
      {
         $strOut  = "Got membershipSpecificData with  ";
         $strOut .= "groupID {$membershipSpecGroupId} and columnName {$columnName}\n";
         file_put_contents( $file, $strOut, FILE_APPEND );
      }

      /* Check if this contact already has a membership id */
      $params = array(
        'version' => 3,
        'page' => 'CiviCRM',
        'q' => 'civicrm/ajax/rest',
        'sequential' => 1,
        'entity_id' => $objectRef->contact_id,
      );
      $existingMembershipId = civicrm_api('CustomValue', 'get', $params);

      $i = 0;
      $membershipIdExists = FALSE;
      while( $i <= $existingMembershipId['count'] )
      {
         $membershipEntry = $existingMembershipId['values'][$i];
         $tmp = print_r( $membershipEntry, TRUE );
         file_put_contents( $file, $tmp, FILE_APPEND );
         if( ($membershipEntry['id'] == $columnId) && ($membershipEntry['0'] > 0) )
         {
            /* A membership ID already exists for this contact */
            if( $DEBUG )
            {
               $strOut  = "Got existing membership ID ";
               $strOut .= "{$membershipEntry['0']} ... moving on quietly\n";
               file_put_contents( $file, $strOut, FILE_APPEND );
            }
            $membershipIdExists = TRUE;
            return;
         }
         $i++;
      }

      /* Find the biggest member ID currently allocated */
      /* NOTE: There appears to be no easy way to do this via the api. 
               so I'm dropping back to the DAO interface until a better method
               can be found that preserves our design goal to use the API */

      $query = "SELECT max( {$columnName} ) as biggest_member_id FROM {$memhershipSpecificsTable}"; 
      $dao = CRM_Core_DAO::executeQuery( $query ); 
      while ( $dao->fetch( ) ) { $max_member_id = $dao->biggest_member_id; } 
      if( $DEBUG )
      {
         $strOut .= "Got max membership number as {$max_member_id}\n";
         file_put_contents( $file, $strOut, FILE_APPEND );
      }

      /* 2. Assign this number +1 to the current contact */
      /* Note: You use the "create" function, passing in the contact ID, to set the
               custom fields. Also, you use the syntax "custom_n" where n is the ID
               of the custom field. This is a little strange, but hey.
               Refer to this page : 

               http://wiki.civicrm.org/confluence/display/CRMDOC/Using+Custom+Data+with+the+API */

      $colname = "custom_{$columnId}";
      $newMemberId = $max_member_id + 1;
      $params = array('contact_id'   => $objectRef->contact_id,
                      'version' => 3,
                      $colname       => $newMemberId, );
      civicrm_api('contact', 'create', $params);

      if( $DEBUG )
      {
         $strOut .= "Assigned as {$newMemberId}\n";
         file_put_contents( $file, $strOut, FILE_APPEND );
      }
   }
}
