HiveLMS

About:
This started off as a side project for multi devices, but the goal is to eventually get full "typical" support for ingesting proprietary and SCORM 2004 content.
Targets - iOS devices, Android, Safari, FireFox, Chrome and IE8+ 

Technologies:
Front End
 - HTML 4/5
 - CSS
 - Webkit Engine based on much of JQtouch's implementation
 - JavaScript (JQuery Framework)
 
Backend
 - PHP 5+
 - MySQL 5+

As of 12-5-2010, the base frame work of the UI and the backend is laid out.  Some of the top items on my list  - 

- Finish SCORM 2004 API
- Graphic updates for mobile (smaller graphics)
- Rethink the Assignments/Lessons menus a bit.  This blurred a bit on me as I was designing it.  I'd like to do something new and take a very minimalist approach to it.  Very tired of being over saturated with information among other LMS's.

I spent some time looking around for a complete solution for Minifying, Packing the content to reduce HTTP drag and bandwidth.
The consensus was nothing really existed from a "complete" standpoint, so I was left bolting on pieces of other projects and built out a rapid build script that essentially takes a look at a existing "index_dev.html" and creates a index.html, portal.pack.js, and portal.min.css.
Combined with gzipping and this reduces everything to just under 50KB and 3 HTTP hits not counting background graphics.
I have hopes to also begin to take small CSS images detected in the build process and base64 those under a certain byte range (TBD) to reduce further HTTP hits.

I'm open to further assistance on this project, and have leaned heavily on other projects to get this down the road the last 5 months.

My main personal goals:
 - A way to have total transparency testing SCORM Content
 - A leaner more simplified approach to a LMS, masking complexity, useless services and navigation etc ...
 - Bolt-on as many other great open source products to reduce teachers having to reach out to these projects seperately
 - Wrap in a Authoring engine with some of the latest Web Technologies, rich layouts and templates.
 - Attempt to remain compatible with modern browsers and the multitude of growing mobile devices.