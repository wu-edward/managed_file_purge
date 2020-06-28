# Managed File Purge Cloudflare
If enabled, this provides a purger that will invalidate URLS using the 
Cloudflare API. The purger is a sublcass of the purger provided by the 
Cloudflare module, except it only applies to URL invalidation. This purger is
provided in case tag-based invalidation is not desired at the Cloudflare level,
but invalidating file URLs is. If there is no such concern about tag 
invalidation, then use the Cloudflare purger.

##TODO
Integrate configuration to invalidate URLs with custom Cloudflare cache keys. 
