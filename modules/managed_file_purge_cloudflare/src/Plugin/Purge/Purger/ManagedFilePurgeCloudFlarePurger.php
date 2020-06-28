<?php

namespace Drupal\managed_file_purge_cloudflare\Plugin\Purge\Purger;

use Drupal\cloudflarepurger\Plugin\Purge\Purger\CloudFlarePurger;

/**
 * Managed file purage CloudFlare purger.
 *
 * This provides a purger for Cloudflare that invalidates files only, in case
 * it tag invalidation is not desired on Cloudflare.
 *
 * @PurgePurger(
 *   id = "managed_file_purge_cloudflare",
 *   label = @Translation("Managed file purge CloudFlare"),
 *   description = @Translation("URL only purger for CloudFlare."),
 *   types = {"url"},
 *   multi_instance = FALSE,
 * )
 */
class ManagedFilePurgeCloudFlarePurger extends CloudFlarePurger {}
