# Managed File Purge
This module provides integration to invalidate managed file URLs from external
caches, reverse proxies and CDN platforms. Invalidating the file URLs might be
necessary in cases when file entities are deleted, and new file entities are 
then created with filenames matching the deleted file entities, or if contrib
or other custom code is used to be able to update or change the files on disk
or at source that the file entities refer to. This is an integration with the
[Purge module](https://www.drupal.org/project/purge) and provides a purge
queue plugin.

This module also provides a form to specify the base URLs for when the site is
accessed via CDN and reverse proxy. This form is available at 
`/admin/config/development/performance/purge/managed_file`.
