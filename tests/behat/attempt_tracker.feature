@report @report_embedquestion
Feature: Teachers/students can see their students/their own progress on embedded questions.
  In order to make the report more useful
  As a teacher/student
  I should be able to see the Embedded questions progress link only if there are some attempts

  Background:
    Given the following "users" exist:
      | username |
      | student1 |
      | student2 |
      | teacher  |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | teacher  | C1     | editingteacher |
    And the following "activities" exist:
      | activity | name      | idnumber | course |
      | page     | Test page | page1    | C1     |
    And the following "question categories" exist:
      | contextlevel | reference | name          | idnumber |
      | Course       | C1        | Test questions| embed    |
    And the following "questions" exist:
      | questioncategory | qtype     | name           | idnumber |
      | Test questions   | truefalse | First question | test1    |
    And the "embedquestion" filter is "on"

  Scenario: The Embedded questions progress link in the Course Administration only show if there are some attempts
    Given I log in as "teacher"
    And I am on "Course 1" course homepage
    When I navigate to "Reports" in current page administration
    Then "Embedded questions progress" "link" should not exist
    And "student1" has attempted embedded questions in "course" context "Course 1":
      | pagename        | question    | response |
      | Course:Course 1 | embed/test1 | True     |
    And I reload the page
    And "Embedded questions progress" "link" should exist
    And I follow "Embedded questions progress"
    And I should see "student1"
    And I should not see "student2"

  Scenario: The Embedded questions progress link in the Module Administration only show if there are some attempts
    Given I log in as "teacher"
    And I am on "Course 1" course homepage
    When I follow "Test page"
    Then "Embedded questions progress" "link" should not exist in current page administration
    And "student2" has attempted embedded questions in "activity" context "page1":
      | pagename | question    | response |
      | C1:page1 | embed/test1 | False    |
    And I reload the page
    And "Embedded questions progress" "link" should exist in current page administration
    And I follow "Embedded questions progress"
    And I should see "student2"
    And I should not see "student1"
