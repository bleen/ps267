<?php

namespace Drupal\ps267\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides a Calendar block displayed in a traditional grid.
 *
 * @Block(
 *   id = "ps267_grid_calendar_block",
 *   admin_label = @Translation("Calendar Grid")
 * )
 */
class PS267GridCalendarBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The renderer.
   *
   * @var RendererInterface
   */
  protected $renderer;

  /**
   * The number of months to display.
   */
  public $numberOfMonths = 10;

  /**
   * Constructs a new MyBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['label_display' => FALSE];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $calendar = [];
    $current_month = date('n') < 7 || date('n') > 8 ? date('n') : 9;
    $current_year = date('Y');

    for ($i = 0; $i < $this->numberOfMonths; $i++) {
      $month = ($current_month + $i) % 12 ?: 12;
      $year = ($current_month + $i) <= 12 ? $current_year : $current_year + 1;

      $day_one = mktime(0, 0, 0, $month, 1, $year);
      $day_one_components = getdate($day_one);
      $days_count = date('t', $day_one);

      $calendar[$year . $month] = [
        '#theme' => 'table',
        '#caption' => $day_one_components['month'] . ' ' . $year,
        '#header' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        '#attributes' => [
          'class' => ['grid-calendar'],
        ],
        '#rows' => [],
      ];

      $date = 1;
      while ($date <= $days_count) {
        $week = [];
        $day_of_week_index = 0;

        if ($date === 1) {
          //Only during the first week, the first day might not be a Sunday.
          $day_of_week_index = $day_one_components['wday'];

          // Fill in days from the previous month.
          for ($j = 0; $j < $day_one_components['wday']; $j++) {
            $day = $this->build_day(NULL, $current_month, $current_year);
            $week['data'][] = [
              'class' => ['day', 'prior_month', 'not_this_month'],
              'data' => $this->renderer->render($day),
            ];
          }
        }

        // Fill in the week.
        for ($j = $day_of_week_index; $j < 7; $j++) {
          $classes = ['day'];
          if ($date <= $days_count) {
            $day = $this->build_day($date, $this->getEvents($year, $month, $date));
          }
          else {
            // This happens when we surpass the days in the month (e.g. 7/32).
            $day = $this->build_day(NULL, []);
            $classes += ['next_month', 'not_this_month'];
          }

          $week['data'][] = [
            'class' => $classes,
            'data' => $this->renderer->render($day),
          ];
          $date++;
        }

        $calendar[$year . $month]['#rows'][] = $week;
      }
    }

    return $calendar;
  }

  /**
   * Build a render array for a specific day.
   *
   * @param int|NULL $current_date
   *   The number of the date being built. E.g. 28 for July, 28th. If this date
   *   does not fall within the given month (because the month starts or ends in
   *   the middle of a week) then this should be NULL.
   *
   * @return array
   */
  private function build_day($current_date, $events) {
    $day = [];

    if (!is_null($current_date)) {
      $items = [];

      /** @var /Drupal/entity/node $event */
      foreach ($events as $event) {
        $e = $event->get('title')->value;
        if (!empty($event->get('body')->value)) {
          $e .= ' (' . trim(strip_tags($event->get('body')->value)) . ')';
        }

        $items[] = $e;
      }

      $day = [
        'date_number' => [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $current_date,
          '#attibutes' => [
            'class' => ['date_number'],
          ],
        ],
        'events' => [
          '#theme' => 'item_list',
          '#list_type' => 'ul',
          '#items' => $items,
        ],
      ];
    }

    return $day;
  }

  /**
   * Get all the event nodes for a given date.
   *
   * @param int $year
   *   The year.
   * @param int $month
   *   The month.
   * @param int $day
   *   The date.
   *
   * @return array
   */
  private function getEvents($year, $month, $day) {
    $bundle = 'calendar_event';
    $start_date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
    $end_date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);

    $query = \Drupal::entityQuery('node');
    $query->condition('status', 1);
    $query->condition('type', $bundle);
    $and = $query->andConditionGroup();
    $and->condition('field_calendar_event_date.value', $start_date, '>=');
    $and->condition('field_calendar_event_date.value', $end_date, '<=');
    $query->condition($and);
    $query->sort('field_calendar_event_date.value', 'ASC');

    $query->addTag('handy_cache_tags:node:' . $bundle);

    $entity_ids = $query->execute();

    $events = $this->entityTypeManager->getStorage('node')->loadMultiple($entity_ids);

    return $events;
  }

}