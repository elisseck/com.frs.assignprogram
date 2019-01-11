<?php

use CRM_Assignprogram_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Assignprogram_Form_AssignProgram extends CRM_Core_Form {
  public function buildQuickForm() {
    $contactId = (int) $_GET['cid'];
    //we need the contactID later
    $this->assign('_contactId', $contactId);
    //form only makes sense if we get a contact ID from the page
    if ($contactId) {

      global $wpdb;
      //Get programs defined as WP content
      $programs = $wpdb->get_results("SELECT ID,post_title FROM " . $wpdb->prefix . "posts WHERE post_type='drf_program' AND post_status='publish' order by post_title ASC");
      $tmp_prog = array();

      foreach ($programs as $each_prog) {
        $tmp_prog[$each_prog->ID] = $each_prog->post_title;
      }
      //$this->programs_arr holds all program titles as ID => Title
      $this->assign('programs_arr', $tmp_prog);

      //Not sure why this gets assigned or where it is used this is legacy
      $if_counselor_assigned = $wpdb->query("SELECT contact_id_b FROM civicrm_relationship WHERE contact_id_a=" . $contactId . " AND relationship_type_id=12 AND is_active=1");
      $this->assign('have_counselor', $if_counselor_assigned);

      //Get what's currently in the DB to assign defaults
      $get_progs_hrs = $wpdb->get_results("SELECT * FROM civi_drf_prog_hours WHERE contact_id=" . $contactId);
      $tmarr = array();
      foreach ($get_progs_hrs as $eachProghrs) {
        $tmarr[$eachProghrs->prog_id][0] = $eachProghrs->hours;
        $tmarr[$eachProghrs->prog_id][1] = $eachProghrs->weekmon;
      }
      //$this->progs_arr holds what should be set as defaults for hours and weekly/monthly as ID => 0 => Hours and ID => 1 => Weekly/Monthly
      $this->assign('progs_arr', $tmarr);

      //Not sure why we need to go get full name here
      $name = $wpdb->get_results("SELECT first_name,last_name FROM civicrm_contact WHERE id=" . $contactId, ARRAY_N);
      $this->assign('fullname', $name[0][0] . ' ' . $name[0][1]);

      //Not sure why we need to go get race here this is legacy
      $get_race_name = $wpdb->get_results("SELECT race_19 FROM civicrm_value_race_6 WHERE entity_id=" . $contactId, ARRAY_N);
      $this->assign('race', $get_race_name[0][0]);

      //Not sure why we need to go get guardian names here this is legacy
      $g1name = $g2name = $ginfo = '';

      $get_guardian_name = $wpdb->get_results("SELECT firstname_parent_guardian_name__1,lastname_parent_guardian_name__2,firstname_parent_guardian_name_2_3,lastname_parent_guardian_name_2__4,parent_guardian_email_address_6,parent_guardian_phone_5,street_address_8,address_line_2_9,city_10,state_11,zip_12 FROM civicrm_value_parent_info_1 WHERE entity_id=" . $contactId, ARRAY_N);

      $g1name = $get_guardian_name[0][0] . ' ' . $get_guardian_name[0][1];
      if ($get_guardian_name[0][2] != '') {
        $g2name = ', ' . $get_guardian_name[0][2] . ' ' . $get_guardian_name[0][3];
      }
      if ($get_guardian_name[0][6] != '') {
        $ginfo = $get_guardian_name[0][6];
      }
      if ($get_guardian_name[0][7] != '') {
        $ginfo = $ginfo . ', ' . $get_guardian_name[0][7];
      }
      if ($get_guardian_name[0][8] != '') {
        $ginfo = $ginfo . '<br>' . $get_guardian_name[0][8];
      }
      if ($get_guardian_name[0][9] != '') {
        $ginfo = $ginfo . ', ' . $get_guardian_name[0][9];
      }
      if ($get_guardian_name[0][10] != '') {
        $ginfo = $ginfo . ' ' . $get_guardian_name[0][10];
      }
      if ($get_guardian_name[0][4] != '') {
        $ginfo = $ginfo . '<br>' . $get_guardian_name[0][4];
      }
      if ($get_guardian_name[0][5] != '') {
        $ginfo = $ginfo . ', ' . $get_guardian_name[0][5];
      }

      $this->assign('gname', $g1name . $g2name);
      $this->assign('ginfo', $ginfo);

      //Not sure why we need to go get referral info here this is legacy
      $rname = $rinfo = '';

      $get_ref_info = $wpdb->get_results("SELECT organization_name FROM civicrm_contact WHERE id IN (SELECT contact_id_a FROM civicrm_relationship WHERE relationship_type_id=11 AND contact_id_b=" . $contactId . ")", ARRAY_N);

      if ($get_ref_info[0][0] != '') {
        $rname = $get_ref_info[0][0] . '<br>';
      }

      $get_ref_info1 = $wpdb->get_results("SELECT email FROM civicrm_email WHERE contact_id IN (SELECT contact_id_a FROM civicrm_relationship WHERE relationship_type_id=11 AND contact_id_b=" . $contactId . ")", ARRAY_N);

      if ($get_ref_info1[0][0] != '') {
        $rinfo = $get_ref_info1[0][0];
      }

      $get_ref_info2 = $wpdb->get_results("SELECT phone FROM civicrm_phone WHERE contact_id IN (SELECT contact_id_a FROM civicrm_relationship WHERE relationship_type_id=11 AND contact_id_b=" . $contactId . ")", ARRAY_N);

      if ($get_ref_info2[0][0] != '') {
        $rinfo = $rinfo . ', ' . $get_ref_info2[0][0];
      }

      $this->assign('rname', $get_ref_info[0][0]);
      $this->assign('rinfo', $get_ref_info1[0][0] . ', ' . $get_ref_info2[0][0]);

      //Not sure why we need to go get allergies info here this is legacy
      $get_allergies = $wpdb->get_results("SELECT allergies_or_medical_conditions_95 FROM civicrm_value_medical_information_13 WHERE entity_id=" . $contactId, ARRAY_N);

      $this->assign('allergies', $get_allergies[0][0]);

      $get_allergies = $wpdb->get_results("SELECT school_55,current_grade_56 FROM civicrm_value_youths_education_10 WHERE entity_id=" . $contactId, ARRAY_N);

      $this->assign('school', $get_allergies[0][0]);
      $this->assign('grade', $get_allergies[0][1]);

      // add form elements
      foreach ($tmp_prog as $id => $title) {
        $this->addCheckbox(
          $id,
          $title,
          array('' => 1)
        );
        $this->add(
          'text',
          $id . '_hours',
          ts('Hours to Approve')
        );
        $this->add(
          'select',
          $id . '_weekmon',
          'Weekly / Monthly',
          array('Weekly' => 'Weekly', 'Monthly' => 'Monthly')
        );
      }
      $this->add(
        'text',
        'assign_program_contact_id',
        ts('contact id')
      );
      $this->addButtons(array(
        array(
          'type' => 'submit',
          'name' => E::ts('Submit'),
          'isDefault' => TRUE,
        ),
      ));

      // export form elements
      $this->assign('elementNames', $this->getRenderableElementNames());

      // set defaults
      //$tmarr holds what should be set as defaults for hours and weekly/monthly as ID => 0 => Hours and ID => 1 => Weekly/Monthly
      foreach ($tmarr as $id => $values) {
        $defaults[$id . '[1]'] = 1;
        $defaults[$id . '_hours'] = $values[0];
        $defaults[$id . '_weekmon'] = $values[1];
      }
      $defaults['assign_program_contact_id'] = $contactId;
      $this->setDefaults($defaults);
      parent::buildQuickForm();
    }
  }

  public function postProcess() {
    //insert values into our custom table - can override civicrm processing here
    $values = $this->_submitValues;
    $contactId = $values['assign_program_contact_id'];
    if ($contactId) {
      //get current values for contact
      $sql = "SELECT * FROM civi_drf_prog_hours WHERE contact_id = {$contactId}";
      $dao = CRM_Core_DAO::executeQuery($sql);
      $rows = array();
      while ($dao->fetch()) {
        $row = array();
        $row['id'] = $dao->id;
        $row['contact_id'] = $dao->contact_id;
        $row['prog_id'] = $dao->prog_id;
        $row['hours'] = $dao->hours;
        $row['weekmon'] = $dao->weekmon;
        $rows[] = $row;
      }
      foreach ($values as $key => $value) {
        //if the checkbox is checked for a program
        if (is_array($values[$key])) {
          //Input sanity check
          if (!$values[$key . '_hours']) {
            $values[$key . '_hours'] = 0;
          }
          //loop through rows checking for a matching prog ID and contact ID and update it
          foreach ($rows as $row) {
            if ($row['prog_id'] == $key) {
              $sql = "UPDATE civi_drf_prog_hours SET prog_id = {$key}, contact_id = {$contactId}, hours = {$values[$key . '_hours']}, weekmon = '{$values[$key . '_hourlyweekly']}' WHERE id = {$row['id']}";
              CRM_Core_DAO::executeQuery($sql);
            }
            else {
              //if no current value matches, insert value
              $sql = "INSERT INTO civi_drf_prog_hours (prog_id, contact_id, hours, weekmon) VALUES({$key}, {$contactId}, {$values[$key . '_hours']}, '{$values[$key . '_hourlyweekly']}')";
              CRM_Core_DAO::executeQuery($sql);
            }
          }
          //handle case where the user has no values yet
          if (!$rows) {
            //if no current value matches, insert value
            $sql = "INSERT INTO civi_drf_prog_hours (prog_id, contact_id, hours, weekmon) VALUES({$key}, {$contactId}, {$values[$key . '_hours']}, '{$values[$key . '_hourlyweekly']}')";
            CRM_Core_DAO::executeQuery($sql);
          }
        }
        else {
          //If no array value, check if the row used to exist and we need to remove it
          foreach ($rows as $row) {
            $sql = "DELETE FROM civi_drf_prog_hours WHERE id = {$row['id']}";
            CRM_Core_DAO::executeQuery($sql);
          }
        }
      }
      CRM_Utils_System::redirect('/Portal/wp-admin/admin.php?page=CiviCRM&q=civicrm%2Fcontact%2Fview&reset=1&cid=' . $contactId);
      //parent::postProcess();
    }
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
