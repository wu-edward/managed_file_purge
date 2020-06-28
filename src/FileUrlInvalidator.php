<?php

namespace Drupal\managed_file_purge;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\file\FileInterface;
use Drupal\purge\Plugin\Purge\Invalidation\Exception\InvalidExpressionException;
use Drupal\purge\Plugin\Purge\Invalidation\Exception\MissingExpressionException;
use Drupal\purge\Plugin\Purge\Invalidation\Exception\TypeUnsupportedException;
use Drupal\purge\Plugin\Purge\Invalidation\InvalidationsServiceInterface;
use Drupal\purge\Plugin\Purge\Queue\QueueServiceInterface;
use Drupal\purge\Plugin\Purge\Queuer\QueuersServiceInterface;

/**
 * Service that invalidate URL via purge for managed file.
 */
class FileUrlInvalidator implements FileUrlInvalidatorInterface {

  /**
   * CDN base URLs to be prepended to file path.
   *
   * @var array
   */
  protected $cdnBaseUrls;

  /**
   * The purge invalidation factory service.
   *
   * @var \Drupal\purge\Plugin\Purge\Invalidation\InvalidationsServiceInterface
   */
  protected $purgeInvalidationFactory;

  /**
   * Purge queuer for URLs.
   *
   * @var \Drupal\purge\Plugin\Purge\Queuer\QueuerInterface
   */
  protected $purgeQueuer;

  /**
   * The purge queue service.
   *
   * @var \Drupal\purge\Plugin\Purge\Queue\QueueServiceInterface
   */
  protected $purgeQueue;

  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * FileUrlInvalidator constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\purge\Plugin\Purge\Invalidation\InvalidationsServiceInterface $purge_invalidation_factory
   *   The purge invalidation factory service.
   * @param \Drupal\purge\Plugin\Purge\Queuer\QueuersServiceInterface $purge_queuers
   *   The purge queuers service.
   * @param \Drupal\purge\Plugin\Purge\Queue\QueueServiceInterface $purge_queue
   *   The purge queue service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              InvalidationsServiceInterface $purge_invalidation_factory,
                              QueuersServiceInterface $purge_queuers,
                              QueueServiceInterface $purge_queue,
                              LoggerChannelFactoryInterface $logger_factory) {

    // Get the list of CDN base URLs, if any. Make sure the list is an array and
    // each value in list is a valid absolute URL.
    $this->cdnBaseUrls = $config_factory->get('managed_file_purge.settings')
      ->get('cdn_base_urls');
    if (!is_array($this->cdnBaseUrls)) {
      $this->cdnBaseUrls = [];
    }
    $this->cdnBaseUrls = array_filter($this->cdnBaseUrls, function ($url) {
      return UrlHelper::isValid($url, TRUE);
    });

    $this->purgeInvalidationFactory = $purge_invalidation_factory;
    $this->purgeQueuer = $purge_queuers->get('managed_file_purge_url_queuer');
    $this->purgeQueue = $purge_queue;
    $this->logger = $logger_factory->get('managed_file_purge');
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateFileUrl(FileInterface $file) {
    try {
      // Invalidate the absolute file URL.
      $invalidations = [$this->purgeInvalidationFactory->get('url', $file->createFileUrl(FALSE))];

      if ($this->cdnBaseUrls) {
        foreach ($this->cdnBaseUrls as $cdn_base_url) {
          // Prepend the CDN base URLs to the relative file path and add to the
          // invalidations.
          $invalidate_url = rtrim($cdn_base_url, '/') . $file->createFileUrl();
          $invalidations[] = $this->purgeInvalidationFactory->get('url', $invalidate_url);
        }
      }

      $this->purgeQueue->add($this->purgeQueuer, $invalidations);
    }
    catch (MissingExpressionException | InvalidExpressionException | TypeUnsupportedException $e) {
      $this->logger->error($e->getMessage());
    }
  }

}
