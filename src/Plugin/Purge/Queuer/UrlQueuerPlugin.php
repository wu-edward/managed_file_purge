<?php

namespace Drupal\managed_file_purge\Plugin\Purge\Queuer;

use Drupal\purge\Plugin\Purge\Queuer\QueuerInterface;
use Drupal\purge\Plugin\Purge\Queuer\QueuerBase;

/**
 * Queues URLs to be purged.
 *
 * @PurgeQueuer(
 *   id = "managed_file_purge_url_queuer",
 *   label = @Translation("Managed file purge URL queuer"),
 *   description = @Translation("Queues URLs to be purged."),
 *   enable_by_default = true,
 * )
 */
class UrlQueuerPlugin extends QueuerBase implements QueuerInterface {}
