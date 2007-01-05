$ Id: $

------------
Description
------------

This module sends html or plain text newsletters to the subscription list. At the end
of the newsletter, an unsubscribe link is provided. Subscription (and also
unsubscription) is managed through a block, or by an admin on the module's admin pages.
Sent newsletters are collected by taxonomy module, and a link to the term's page or the 
associated rss-feed can be displayed in the block. Not-sent newsletters are kept in the
Drafts folder. New newsletter types can be created to organize your newsletters. The
subscription list can be managed. Sending of large mailings can be managed by cron.

------------
Requirements
------------

- Drupal 4.7

- Taxonomy module should be enabled

- For large mailing lists, cron is required

------------
Installation
------------

- Create a new directory "simplenews" in your "modules" directory and place the
  entire contents of this simplenews directory in it.

- Enable the module by navigating to administer -> modules.

- Grant the proper access to user accounts under administer -> access control.
  The most important setting is "access newsletters" for all roles, including
  "anonymous user" if you want links to be displayed in the Simplenews block to
  everyone.

- Enable the Simplenews block by navigating to administer -> blocks.

- Configure Simplenews by navigating to administer -> newsletters -> settings.

------------
Credits
------------
Written by
  - Dries Knapen <drieske AT hotmail DOT com>
