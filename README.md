CiviCRM_MembershipID
====================

Grants CiviCRM Membership ID Generator for the AA.

We're using CiviCRM for contact and membership management. The problem is that the
CiviCRM "Contact ID" doesn't really suit us as a member number, as not all contacts
are members, and members need to have monotonically increasing for the issueance of
membership books and packs.

Therefore we've created this CiviCRM extension.

The approach is to create a custom "Contact" entity field called "Membership_ID".

This number will be initalised to the next available number when a membership is created
for a contact.

We want this to happen as a hook so that we don't have to keep some separate record
for what the current maximum membership ID is.

Appreciate any and all advice and help on this.
