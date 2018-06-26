<?php

namespace Drupal\contentpool_remote_register;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of remote registration entities.
 *
 * @ingroup remote_registration
 */
class RemoteRegistrationListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['name'] = $this->t('Name');
    $header['url'] = $this->t('Url');
    $header['remote'] = $this->t('Remote');
    $header['operations'] = $this->t('Operations');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.remote_registration.canonical',
      ['remote_registration' => $entity->id()]
    );

    $url = Url::fromUri($entity->getUrl());
    $row['url'] = Link::fromTextAndUrl($entity->getUrl(), $url);
    $row['remote'] = $entity->remote_id->entity->label();

    $list_builder = \Drupal::service('entity_type.manager')->getListBuilder('remote_registration');
    $operations = $list_builder->getOperations($entity);
    $row['operations'] = [
      'data' => [
        '#type' => 'operations',
        '#links' => $operations
      ]
    ];

    return $row + parent::buildRow($entity);
  }

}
