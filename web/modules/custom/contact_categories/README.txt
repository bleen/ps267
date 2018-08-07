INTRODUCTION
------------
This module enables use single site wide contact form
with multi categories & send email by the selected category's email value.
Similar to what was in Drupal 7.x & 6.x.


Installation
------------

To install this module, place it in your modules folder and enable it on the
Admin "Extend" page (/admin/modules).


Configuration
-------------
Add list(text) field named 'categories' to the contact form.
It is important that the machine name of the field be 'field_categories'.
In the allowed values text area, input categories labels and email addresses. e.g.:

    example@example.com|Customer Service
    example2@example.com|Registration
    example3@example.com|Manager

REQUIREMENTS
------------
Core Contact Module.
