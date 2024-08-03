@tool @tool_certify @openlms
Feature: Certification periods settings management tests

  Background:
    Given unnecessary Admin bookmarks block gets deleted
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
      | Program 003 | PR3      | Cat 3    | 0      | certify  |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | manager1 | Manager   | 1        | manager1@example.com |
      | manager2 | Manager   | 2        | manager2@example.com |
      | viewer1  | Viewer    | 1        | viewer1@example.com  |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
    And the following "roles" exist:
      | name                  | shortname |
      | Certification viewer  | pviewer   |
      | Certification manager | pmanager  |
    And the following "permission overrides" exist:
      | capability                         | permission | role     | contextlevel | reference |
      | tool/certify:view                  | Allow      | pviewer  | System       |           |
      | tool/certify:view                  | Allow      | pmanager | System       |           |
      | tool/certify:edit                  | Allow      | pmanager | System       |           |
      | tool/certify:delete                | Allow      | pmanager | System       |           |
      | tool/certify:assign                | Allow      | pmanager | System       |           |
      | enrol/programs:addtocertifications | Allow      | pmanager | System       |           |
    And the following "role assigns" exist:
      | user      | role          | contextlevel | reference |
      | manager1  | pmanager      | System       |           |
      | manager2  | pmanager      | Category     | CAT2      |
      | manager2  | pmanager      | Category     | CAT3      |
      | viewer1   | pviewer       | System       |           |

  @javascript
  Scenario: Manager may set defaults for certification periods
    Given I log in as "manager1"
    And I am on all certifications management page

    And I press "Add certification"
    And I set the following fields to these values:
      | Certification name | Certification 001 |
      | ID number          | CT01              |
    And I press dialog form button "Add certification"
    And I click on "Period settings" "link" in the "#region-main" "css_element"
    And I should see "Not set" in the "Program:" definition list item
    And I should see "Not set" in the "Certification due:" definition list item
    And I should see "Certification completion date" in the "Valid from:" definition list item
    And I should see "Never" in the "Window closing:" definition list item
    And I should see "Never" in the "Expiration:" definition list item
    And I should see "Standard course purge" in the "Certification program reset:" definition list item
    And I should see "No" in the "Re-certify automatically:" definition list item

    When I click on "Update certification" "link"
    And the following fields match these values:
      | due1[enabled]         | 0                                     |
      | resettype1            | Standard course purge                 |
      | valid1                | Certification completion date         |
      | windowend1[since]     | Never                                 |
      | expiration1[since]    | Never                                 |
      | recertify[enabled]    | 0                                     |
    And I set the following fields to these values:
      | Program               | Program 001                           |
      | resettype1            | Full course purge                     |
      | due1[enabled]         | 1                                     |
      | due1[number]          | 3                                     |
      | due1[timeunit]        | weeks                                 |
      | valid1                | Window opening                        |
      | windowend1[since]     | Window opening                        |
      | windowend1[number]    | 2                                     |
      | windowend1[timeunit]  | Months                                |
      | expiration1[since]    | Certification completion date         |
      | expiration1[number]   | 12                                    |
      | expiration1[timeunit] | Months                                |
      | recertify[enabled]    | 0                                     |
    And I press dialog form button "Update certification"
    Then I should see "Program 001" in the "Program:" definition list item
    And I should see "21 days" in the "Certification due:" definition list item
    And I should see "Window opening" in the "Valid from:" definition list item
    And I should see "2 months after Window opening" in the "Window closing:" definition list item
    And I should see "12 months after Certification completion date" in the "Expiration:" definition list item
    And I should see "Full course purge" in the "Certification program reset:" definition list item
    And I should see "No" in the "Re-certify automatically:" definition list item

    When I click on "Update certification" "link"
    And I press dialog form button "Update certification"
    Then I should see "Program 001" in the "Program:" definition list item
    And I should see "21 days" in the "Certification due:" definition list item
    And I should see "Window opening" in the "Valid from:" definition list item
    And I should see "2 months after Window opening" in the "Window closing:" definition list item
    And I should see "12 months after Certification completion date" in the "Expiration:" definition list item
    And I should see "Full course purge" in the "Certification program reset:" definition list item
    And I should see "No" in the "Re-certify automatically:" definition list item

    When I click on "Update certification" "link"
    And the following fields match these values:
      | resettype1            | Full course purge                     |
      | due1[enabled]         | 1                                     |
      | due1[number]          | 3                                     |
      | due1[timeunit]        | weeks                                 |
      | valid1                | Window opening                        |
      | windowend1[since]     | Window opening                        |
      | windowend1[number]    | 2                                     |
      | windowend1[timeunit]  | Months                                |
      | expiration1[since]    | Certification completion date         |
      | expiration1[number]   | 12                                    |
      | expiration1[timeunit] | Months                                |
      | recertify[enabled]    | 0                                     |
    And I set the following fields to these values:
      | Program               | Program 002                           |
      | resettype1            | Standard course purge                 |
      | due1[enabled]         | 0                                     |
      | valid1                | Certification completion date         |
      | windowend1[since]     | Never                                 |
      | expiration1[since]    | Never                                 |
      | recertify[enabled]    | 0                                     |
    And I press dialog form button "Update certification"
    Then I should see "Program 002" in the "Program:" definition list item
    And I should see "Not set" in the "Certification due:" definition list item
    And I should see "Certification completion date" in the "Valid from:" definition list item
    And I should see "Never" in the "Window closing:" definition list item
    And I should see "Never" in the "Expiration:" definition list item
    And I should see "Standard course purge" in the "Certification program reset:" definition list item
    And I should see "No" in the "Re-certify automatically:" definition list item

  @javascript
  Scenario: Manager may set defaults for re-certification periods
    Given I log in as "manager1"
    And I am on all certifications management page

    And I press "Add certification"
    And I set the following fields to these values:
      | Certification name | Certification 001 |
      | ID number          | CT01              |
    And I press dialog form button "Add certification"
    And I click on "Period settings" "link" in the "#region-main" "css_element"
    And I should see "Not set" in the "Program:" definition list item
    And I should see "Not set" in the "Certification due:" definition list item
    And I should see "Certification completion date" in the "Valid from:" definition list item
    And I should see "Never" in the "Window closing:" definition list item
    And I should see "Never" in the "Expiration:" definition list item
    And I should see "Standard course purge" in the "Certification program reset:" definition list item
    And I should see "No" in the "Re-certify automatically:" definition list item

    When I click on "Update certification" "link"
    And I set the following fields to these values:
      | Program               | Program 001                           |
      | resettype1            | Standard course purge                 |
      | due1[enabled]         | 0                                     |
      | valid1                | Certification completion date         |
      | windowend1[since]     | Never                                 |
      | expiration1[since]    | Certification completion date         |
      | expiration1[number]   | 12                                    |
      | expiration1[timeunit] | Months                                |
      | recertify[enabled]    | 1                                     |
      | recertify[number]     | 30                                    |
      | recertify[timeunit]   | days                                  |
    And I press dialog form button "Update certification"
    And I click on "Update re-certification" "link"
    And the following fields match these values:
      | resettype2            | Standard course purge                 |
      | grace2[enabled]       | 0                                     |
      | valid2                | Certification due                     |
      | windowend2[since]     | Never                                 |
      | expiration2[since]    | Certification completion date         |
      | expiration2[number]   | 12                                    |
      | expiration2[timeunit] | Months                                |
    And I set the following fields to these values:
      | Program               | Program 002                           |
      | resettype2            | Full course purge                     |
      | grace2[enabled]       | 1                                     |
      | grace2[number]        | 6                                     |
      | grace2[timeunit]      | days                                  |
      | valid2                | Certification due                     |
      | windowend2[since]     | Window opening                        |
      | windowend2[number]    | 2                                     |
      | windowend2[timeunit]  | Months                                |
      | expiration2[since]    | Certification due                     |
      | expiration2[number]   | 12                                    |
      | expiration2[timeunit] | Months                                |
    And I press dialog form button "Update re-certification"
    Then I should see "30 days before Expiration" in the "Re-certify automatically:" definition list item
    And I click on "Update re-certification" "link"
    And the following fields match these values:
      | resettype2            | Full course purge                     |
      | grace2[enabled]       | 1                                     |
      | grace2[number]        | 6                                     |
      | grace2[timeunit]      | days                                  |
      | valid2                | Certification due                     |
      | windowend2[since]     | Window opening                        |
      | windowend2[number]    | 2                                     |
      | windowend2[timeunit]  | Months                                |
      | expiration2[since]    | Certification due                     |
      | expiration2[number]   | 12                                    |
      | expiration2[timeunit] | Months                                |
    And I press dialog form button "Cancel"

    When I click on "Update certification" "link"
    And I set the following fields to these values:
      | recertify[enabled]    | 0                                     |
    And I press dialog form button "Update certification"
    Then I should see "No" in the "Re-certify automatically:" definition list item

  @javascript
  Scenario: Manager may manage periods manually
    Given I log in as "manager1"
    And the following "permission overrides" exist:
      | capability                         | permission | role     | contextlevel | reference |
      | tool/certify:admin                 | Allow      | pmanager | System       |           |
    And I am on all certifications management page

    And I press "Add certification"
    And I set the following fields to these values:
      | Certification name | Certification 001 |
      | ID number          | CT01              |
    And I press dialog form button "Add certification"
    And I click on "Period settings" "link" in the "#region-main" "css_element"
    And I click on "Update certification" "link"
    And I set the following fields to these values:
      | Program               | Program 001                           |
      | resettype1            | Standard course purge                 |
      | due1[enabled]         | 0                                     |
      | valid1                | Certification completion date         |
      | windowend1[since]     | Never                                 |
      | expiration1[since]    | Certification completion date         |
      | expiration1[number]   | 12                                    |
      | expiration1[timeunit] | Months                                |
      | recertify[enabled]    | 1                                     |
      | recertify[number]     | 30                                    |
      | recertify[timeunit]   | days                                  |
    And I press dialog form button "Update certification"
    And I click on "Assignment settings" "link" in the "#region-main" "css_element"
    And I click on "Update Manual assignment" "link"
    And I set the following fields to these values:
      | Active | Yes |
    And I press dialog form button "Update"
    And I click on "Users" "link" in the "#region-main" "css_element"
    And I press "Assign users"
    And I set the following fields to these values:
      | Users                    | Student 1 |
      | timewindowstart[day]     | 5         |
      | timewindowstart[month]   | 10        |
      | timewindowstart[year]    | 2022      |
      | timewindowstart[hour]    | 09        |
      | timewindowstart[minute]  | 00        |
    And I press dialog form button "Assign users"
    And I follow "Student 1"

    When I press "Add period"
    And I set the following fields to these values:
      | Program                  | Program 002 |
      | timewindowstart[day]     | 5           |
      | timewindowstart[month]   | 10          |
      | timewindowstart[year]    | 2023        |
      | timewindowstart[hour]    | 09          |
      | timewindowstart[minute]  | 00          |
      | timefrom[enabled]        | 1           |
      | timefrom[day]            | 5           |
      | timefrom[month]          | 10          |
      | timefrom[year]           | 2023        |
      | timefrom[hour]           | 09          |
      | timefrom[minute]         | 00          |
      | timeuntil[enabled]       | 1           |
      | timeuntil[day]           | 5           |
      | timeuntil[month]         | 10          |
      | timeuntil[year]          | 2024        |
      | timeuntil[hour]          | 09          |
      | timeuntil[minute]        | 00          |
    And I press dialog form button "Add period"
    Then the following should exist in the "tool_certify_assignment_periods_table" table:
      | Program     | Window opening | Window closing | Expiration                                    | Re-certify automatically |
      | Program 002 | 5/10/23        | Not set        |                                               | 5/09/24                  |
      | Program 001 | 5/10/22        | Not set        | 12 months after Certification completion date | No                       |

    When I follow "5/10/22"
    And I press "Override period dates"
    And I set the following fields to these values:
      | timewindowstart[day]     | 1           |
      | timewindowstart[month]   | 10          |
      | timewindowstart[year]    | 2022        |
      | timewindowstart[hour]    | 09          |
      | timewindowstart[minute]  | 00          |
      | timewindowdue[enabled]   | 1           |
      | timewindowdue[day]       | 10          |
      | timewindowdue[month]     | 10          |
      | timewindowdue[year]      | 2022        |
      | timewindowdue[hour]      | 09          |
      | timewindowdue[minute]    | 00          |
      | timewindowend[enabled]   | 1           |
      | timewindowend[day]       | 20          |
      | timewindowend[month]     | 10          |
      | timewindowend[year]      | 2022        |
      | timewindowend[hour]      | 09          |
      | timewindowend[minute]    | 00          |
      | timefrom[enabled]        | 1           |
      | timefrom[day]            | 5           |
      | timefrom[month]          | 10          |
      | timefrom[year]           | 2022        |
      | timefrom[hour]           | 09          |
      | timefrom[minute]         | 00          |
      | timeuntil[enabled]       | 1           |
      | timeuntil[day]           | 5           |
      | timeuntil[month]         | 10          |
      | timeuntil[year]          | 2023        |
      | timeuntil[hour]          | 09          |
      | timeuntil[minute]        | 00          |
    And I press dialog form button "Override period dates"
    Then I should see "Program 001" in the "Program:" definition list item
    And I should see "1 October 2022" in the "Window opening:" definition list item
    And I should see "10 October 2022" in the "Certification due:" definition list item
    And I should see "20 October 2022" in the "Window closing:" definition list item
    And I should see "5 October 2022" in the "Valid from:" definition list item
    And I should see "5 October 2023" in the "Expiration:" definition list item
    And I should see "No" in the "Re-certify automatically:" definition list item
    And I should see "Not set" in the "Certification completion date:" definition list item
    And I should see "Not set" in the "Revocation date:" definition list item

    When I press "Override period dates"
    And I set the following fields to these values:
      | timerevoked[enabled]       | 1           |
    And I press dialog form button "Override period dates"
    And I press "Delete period"
    And I press dialog form button "Delete period"
    Then the following should exist in the "tool_certify_assignment_periods_table" table:
      | Program     | Window opening | Window closing | Expiration                                    | Re-certify automatically |
      | Program 002 | 5/10/23        | Not set        |                                               | 5/09/24                  |
    And I should not see "Program 001"
