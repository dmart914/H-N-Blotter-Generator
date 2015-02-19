The H&N Blotter Generator
=========================

Overview
--------

This is a PDF parsing program created for the Herald and News
newspaper in Klamath Falls, Oregon. The master branch was
created by Dave Martinez in 2014.

The program converts PDFs sent by the Klamath County Jail and
Klamath County 911 and first converts them to text. It then
parses through the PDFs and pulls out relevent data.

The data is then passed back to the user in editable fields.

On submission, the field data is gathered and transformed 
for publication on heraldandnews.com.

Obviously, this is a pretty specialized use but the parsing
principals may help someone. 


WARNING: Under active development
---------------------------------

This program is currently under active development and should
be used with caution.


To do list:
-----------
+ Refactor PHP to use OOP
+ Make database connection optional
+ Create install script
+ Add bug tracker