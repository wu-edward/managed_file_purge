<?php

namespace Drupal\managed_file_purge;

use Drupal\file\FileInterface;

/**
 * Interface for service that will purge a managed file's URL.
 */
interface FileUrlInvalidatorInterface {

  /**
   * Invalidate a managed file URL via purge.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file to be purged.
   */
  public function invalidateFileUrl(FileInterface $file);

}
