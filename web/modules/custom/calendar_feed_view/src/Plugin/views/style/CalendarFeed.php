<?php

namespace Drupal\calendar_feed_view\Plugin\views\style;

use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\views\Annotation\ViewsStyle;
use Drupal\Core\Annotation\Translation;
use Drupal\filter\Entity\FilterFormat;
use Drupal\filter\Plugin\Filter\FilterHtml;
use Drupal\Core\Url;
use kigkonsult\iCalcreator\vcalendar;
use kigkonsult\iCalcreator\vevent;

/**
 * Style plugin to render a list of calendar events as a feed.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "calendar_feed_view",
 *   title = @Translation("Calendar Feed"),
 *   help = @Translation("Render a list of calendar events as a feed that users can subscribe to."),
 *   display_types = { "feed" }
 * )
 *
 */
class CalendarFeed extends StylePluginBase {

  /**
   * {@inheritdoc}
   */
  protected $usesRowPlugin = TRUE;

  /**
   * {@inheritdoc}
   */
  public function render() {
    if (empty($this->view->rowPlugin)) {
      debug('Drupal\calendar_feed_view\Plugin\views\style\CalendarFeed: Missing row plugin');
      return [];
    }

    $config = [
      'unique_id' => 'ps267',
      'allowempty' => FALSE
    ];
    $calendar = new vcalendar($config);
    $calendar->setMethod('PUBLISH');
    $calendar->setProperty('X-WR-CALNAME', 'PS267', array('VALUE' => 'TEXT'));

    foreach ($this->view->result as $row_index => $row) {
      if (empty($row)) {
        // The row plugin returned NULL for this row, which can happen due to
        // either various error conditions.
        continue;
      }

      /** @var vevent $event */
      $event = $calendar->newComponent('vevent');
      $event->setUid($row->_entity->uuid());
      $event->setSummary($row->_entity->getTitle());

      $body = $row->_entity->get('body')->getValue()[0];
      if (!is_null($body)) {
        $format = FilterFormat::load($body['format']);
        /** @var FilterHtml $filter */
        $filter = $format->filters()->get('filter_html');
        $body = $filter->process($body['value'], 'en')->getProcessedText();
        $body = \Drupal\Core\Mail\MailFormatHelper::htmlToText($body);
        $body = str_replace(["\n\n", "\r\n", "\n", "\r"], ' ', $body);
        $event->setDescription($body);
      }

      list($year, $month, $day) = explode("-", $row->_entity->get('field_calendar_event_date')->value);
      $event->setDtstart($year, $month, $day, FALSE, FALSE, FALSE, FALSE, ['VALUE' => 'DATE']);
    }

    $feed = $calendar->createCalendar();
    // iCalcreator escapes all commas and semicolons in string values, as the
    // spec demands. However, some calendar clients are buggy and fail to
    // unescape these characters. Users may choose to unescape them here to
    // sidestep those clients' bugs.
    // NOTE: This results in a non-compliant iCal feed, but it seems like a
    // LOT of major clients are bugged this way.
    $feed = str_replace('\,', ',', $feed);
    $feed = str_replace('\;', ';', $feed);

    // These steps shouldn't be run during Preview on the View page.
    if (empty($this->view->live_preview)) {
      // Prevent devel module from appending queries to ical export.
      $GLOBALS['devel_shutdown'] = FALSE;
    }

    $build = [
      '#type' => 'markup',
      '#view' => $this->view,
      '#options' => $this->options,
      '#markup' => $feed,
    ];

    $this->view->getResponse()->headers->set('Content-Type', 'text/calendar; charset=UTF-8');

    return $build;
  }
}
