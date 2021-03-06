SLOODLE object assignment type

This is an assignment type for assignments involving virtual objects created in Second Life or OpenSim.
It is used in conjunction with the PrimDrop SLOODLE object, which can be rezzed in Second Life or OpenSim to allow students to submit assignments of this type.

It requires the main SLOODLE module:
https://github.com/sloodle/moodle-mod_sloodle

It should be installed under the mod/assignment/type/ directory of your Moodle site.

Note on Moodle 2.3 (2012-05:22):
From looking at the dev code, it appears that Moodle 2.3 release introduces a new assignment type structure, and deprecates the old one.
The new type lives under mod/assign, rather than mod/assignment.
We have not yet ported the SLOODLE type to the new structure, but you will probably be able to continue to use the old one, although the site will display warnings.
In new installs this may require turning on compatibility with new assignment types.

See http://www.sloodle.org for more information, and post any questions or comments in the forums there.

Edmund Edgar, 2012-05-19
