@tool @tool_certify @openlms
Feature: Certifications behat generator tests

  Background:
    Given unnecessary Admin bookmarks block gets deleted
    And the following "cohorts" exist:
      | name     | idnumber |
      | Cohort 1 | CH1      |
      | Cohort 2 | CH2      |
      | Cohort 3 | CH3      |
    And the following "categories" exist:
      | name  | category | idnumber |
      | Cat 1 | 0        | CAT1     |
      | Cat 2 | 0        | CAT2     |
      | Cat 3 | 0        | CAT3     |
      | Cat 4 | CAT3     | CAT4     |
    And the following "enrol_programs > programs" exist:
      | fullname    | idnumber | category | public | sources  |
      | Program 000 | PR0      |          | 0      | certify  |
      | Program 001 | PR1      | Cat 1    | 0      | certify  |
      | Program 002 | PR2      | Cat 2    | 0      | certify  |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | viewer1  | Viewer    | 1        | viewer1@example.com  |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
    And the following "roles" exist:
      | name           | shortname |
      | Certification viewer | cviewer   |
    And the following "permission overrides" exist:
      | capability                     | permission | role    | contextlevel | reference |
      | tool/certify:view              | Allow      | cviewer | System       |           |
    And the following "role assigns" exist:
      | user     | role          | contextlevel | reference |
      | viewer1  | cviewer       | System       |           |

  Scenario: Certifications Behat generator creates certifications
    When the following "tool_certify > certifications" exist:
      | fullname          | idnumber | category | public | cohorts            | program1 | sources          |
      | Certification 000 | CT0      |          | 0      | Cohort 1, Cohort 2 | PR0      | manual, approval |
      | Certification 001 | CT1      | Cat 1    | 1      |                    | PR1      |                  |
      | Certification 002 | CT2      | Cat 2    | 0      |                    |          | manual           |

    And I log in as "viewer1"
    And I am on all certifications management page
    Then "Certification 000" row "Category" column of "management_certifications" table should contain "System"
    And "Certification 000" row "ID number" column of "management_certifications" table should contain "CT0"
    And "Certification 000" row "Public" column of "management_certifications" table should contain "No"
    And "Certification 001" row "Category" column of "management_certifications" table should contain "Cat 1"
    And "Certification 001" row "ID number" column of "management_certifications" table should contain "CT1"
    And "Certification 001" row "Public" column of "management_certifications" table should contain "Yes"
    And "Certification 002" row "Category" column of "management_certifications" table should contain "Cat 2"
    And "Certification 002" row "ID number" column of "management_certifications" table should contain "CT2"
    And "Certification 002" row "Public" column of "management_certifications" table should contain "No"

    And I follow "Certification 000"
    And I should see "Certification 000" in the "Full name:" definition list item
    And I should see "CT0" in the "ID number:" definition list item
    And I should see "System" in the "Category:" definition list item
    And I should see "No" in the "Archived:" definition list item
    And I click on "Period settings" "link" in the "#region-main" "css_element"
    And I should see "Program 000" in the "Program:" definition list item
    And I should see "Not set" in the "Certification due:" definition list item
    And I should see "Certification completion date" in the "Valid from:" definition list item
    And I should see "Never" in the "Window closing:" definition list item
    And I should see "Never" in the "Expiration:" definition list item
    And I should see "Standard course purge" in the "Certification program reset:" definition list item
    And I should see "No" in the "Re-certify automatically:" definition list item
    And I click on "Visibility settings" "link" in the "#region-main" "css_element"
    And I should see "No" in the "Public:" definition list item
    And I should see "Cohort 1, Cohort 2" in the "Visible to cohorts:" definition list item
    And I should not see "Cohort 3"
    And I click on "Assignment settings" "link" in the "#region-main" "css_element"
    And I should see "Active" in the "Manual assignment:" definition list item
    And I should see "Inactive" in the "Automatic cohort assignment:" definition list item
    And I should see "Inactive" in the "Self assignment:" definition list item
    And I should see "Active" in the "Requests with approval:" definition list item

    And I am on all certifications management page
    And I follow "Certification 001"
    And I should see "Certification 001" in the "Full name:" definition list item
    And I should see "CT1" in the "ID number:" definition list item
    And I should see "Cat 1" in the "Category:" definition list item
    And I should see "No" in the "Archived:" definition list item
    And I click on "Period settings" "link" in the "#region-main" "css_element"
    And I should see "Program 001" in the "Program:" definition list item
    And I should see "Not set" in the "Certification due:" definition list item
    And I should see "Certification completion date" in the "Valid from:" definition list item
    And I should see "Never" in the "Window closing:" definition list item
    And I should see "Never" in the "Expiration:" definition list item
    And I should see "Standard course purge" in the "Certification program reset:" definition list item
    And I should see "No" in the "Re-certify automatically:" definition list item
    And I click on "Visibility settings" "link" in the "#region-main" "css_element"
    And I should see "Yes" in the "Public:" definition list item
    And I should not see "Cohort 1"
    And I should not see "Cohort 2"
    And I should not see "Cohort 3"
    And I click on "Assignment settings" "link" in the "#region-main" "css_element"
    And I should see "Inactive" in the "Manual assignment:" definition list item
    And I should see "Inactive" in the "Automatic cohort assignment:" definition list item
    And I should see "Inactive" in the "Self assignment:" definition list item
    And I should see "Inactive" in the "Requests with approval:" definition list item
