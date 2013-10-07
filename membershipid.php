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

      /* get a reference to the contact referred to by this membership entry */
      $contactRef = new CRM_Contact_DAO_Contact();
      $contactRef->id = $objectRef->contact_id;

      /* Check if this contact already has a membership id */
      /* if not, then set it to the current maximum membership id + 1 */

      /* Some debugging logic */
      $file = '/tmp/gktTestMembershipPlugin.log';

      $strOut = "Performed {$op} on {$objectName} for contact ID {$objectRef->contact_id}\n";

      file_put_contents( $file, $strOut, FILE_APPEND );
   }
}
