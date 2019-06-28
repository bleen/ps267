<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2019 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.27.18
 * License   Subject matter of licence is the software iCalcreator.
 *           The above copyright, link, package and version notices,
 *           this licence notice and the invariant [rfc5545] PRODID result use
 *           as implemented and invoked in iCalcreator shall be included in
 *           all copies or substantial portions of the iCalcreator.
 *
 *           iCalcreator is free software: you can redistribute it and/or modify
 *           it under the terms of the GNU Lesser General Public License as published
 *           by the Free Software Foundation, either version 3 of the License,
 *           or (at your option) any later version.
 *
 *           iCalcreator is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *           GNU Lesser General Public License for more details.
 *
 *           You should have received a copy of the GNU Lesser General Public License
 *           along with iCalcreator. If not, see <https://www.gnu.org/licenses/>.
 *
 * This file is a part of iCalcreator.
*/

namespace Kigkonsult\Icalcreator;

use PHPUnit\Framework\TestCase;
use DateTime;
use DateTimezone;
use Exception;

/**
 * class SelectComponentsTest
 *
 * Testing exceptions in DateIntervalFactory
 *
 * @author      Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  2.27.18 - 2019-04-09
 */
class SelectComponentsTest extends TestCase
{
    /**
     * Vevent calendar sub-provider
     *
     * demo calendar
     */
    public static function veventCalendarSubProvider() {

        // create a new calendar
        $vcalendar = Vcalendar::factory( [ Vcalendar::UNIQUE_ID => "kigkonsult.se", ] )
            // with calendaring info
                              ->setMethod( Vcalendar::PUBLISH )
                              ->setXprop(
                                  Vcalendar::X_WR_CALNAME,
                                  "Calendar Sample"
                              )
                              ->setXprop(
                                  Vcalendar::X_WR_CALDESC,
                                  "The calendar Description"
                              )
                              ->setXprop(
                                  Vcalendar::X_WR_RELCALID,
                                  "3E26604A-50F4-4449-8B3E-E4F4932D05B5"
                              )
                              ->setXprop(
                                  Vcalendar::X_WR_TIMEZONE,
                                  "Europe/Stockholm"
                              );

        // create a new event
        $event1 = $vcalendar->newVevent()
                            ->setTransp( Vcalendar::OPAQUE )
                            ->setClass( Vcalendar::P_BLIC )
                            ->setSequence( 1 )
            // describe the event
                            ->setSummary( 'Scheduled meeting with six occurrences' )
                            ->setDescription(
                                'Agenda for the the meeting...',
                                [ Vcalendar::ALTREP => 'CID:<FFFF__=0ABBE548DFE235B58f9e8a93d@coffeebean.com>' ]
                            )
                            ->setComment( 'It\'s going to be fun..' )
                            ->setLocation( 'Kafé Ekorren Stockholm' )
                            ->setGeo( '59.32206', '18.12485' )
            // set the time
                            ->setDtstart(
                                new DateTime(
                                    '20190421T090000',
                                    new DateTimezone( 'Europe/Stockholm' )
                                )
                            )
                            ->setDtend(
                                new DateTime(
                                    '20190421T100000',
                                    new DateTimezone( 'Europe/Stockholm' )
                                )
                            )
            // with recurrence rule
                            ->setRrule(
                [
                    Vcalendar::FREQ  => Vcalendar::WEEKLY,
                    Vcalendar::COUNT => 5,
                ]
            )
            // and a another using a recurrence date
                            ->setRdate(
                                [
                                    new DateTime(
                                        '20190609T090000',
                                        new DateTimezone( 'Europe/Stockholm' )
                                    ),
                                    new DateTime(
                                        '20190609T110000',
                                        new DateTimezone( 'Europe/Stockholm' )
                                    ),
                                ],
                                [ Vcalendar::VALUE => Vcalendar::PERIOD ]
                            )
            // and revoke a recurrence date
                            ->setExdate(
                                new DateTime(
                                    '2019-05-12 09:00:00',
                                    new DateTimezone( 'Europe/Stockholm' )
                                )
                            )
            // organizer, chair  and some participants
                            ->setContact(
                                'Head Office, coffeebean Corp, Acme city',
                                [ Vcalendar::ALTREP => "http://coffeebean.com/contacts.vcf" ]
                            )
                            ->setContact(
                                '+12 34 56 78 90, coffeebean Corp, Acme city',
                                [ Vcalendar::ALTREP => "http://coffeebean.com/contacts.vcf" ]
                            )
                            ->setContact(
                                'Head.Office@coffeebean.com, coffeebean Corp, Acme city',
                                [ Vcalendar::ALTREP => "http://coffeebean.com/contacts.vcf" ]
                            )
                            ->setOrganizer(
                                'secretary@coffeebean.com',
                                [ Vcalendar::SENT_BY => 'sent_by.Secretary.staff@CoffeeBean.com' ]
                            )
                            ->setAttendee(
                                'president@coffeebean.com',
                                [
                                    Vcalendar::ROLE     => Vcalendar::CHAIR,
                                    Vcalendar::PARTSTAT => Vcalendar::ACCEPTED,
                                    Vcalendar::RSVP     => Vcalendar::FALSE,
                                    Vcalendar::CN       => 'President CoffeeBean',
                                    Vcalendar::SENT_BY  => 'president.sent_by.secretary@coffeebean.com'
                                ]
                            )
                            ->setAttendee(
                                'participant1.staff@coffeebean.com',
                                [
                                    Vcalendar::ROLE     => Vcalendar::REQ_PARTICIPANT,
                                    Vcalendar::PARTSTAT => Vcalendar::NEEDS_ACTION,
                                    Vcalendar::RSVP     => Vcalendar::TRUE,
                                    Vcalendar::CN       => 'Participant1 CoffeeBean',
                                    Vcalendar::MEMBER   => [
                                        'member.3@coffeebean.com',
                                        'member.4@coffeebean.com'
                                    ],
                                ]
                            )
                            ->setAttendee(
                                'participant2@coffeebean.com',
                                [
                                    Vcalendar::ROLE     => Vcalendar::REQ_PARTICIPANT,
                                    Vcalendar::PARTSTAT => Vcalendar::NEEDS_ACTION,
                                    Vcalendar::RSVP     => Vcalendar::TRUE,
                                    Vcalendar::CN       => 'Participant2 CoffeeBean',
                                    Vcalendar::DELEGATED_FROM => 'delegated_from.2@coffeebean.com',
                                ]
                            );

        // add an alarm for the event
        $alarm = $event1->newValarm()
                        ->setAction( Vcalendar::DISPLAY )
                        ->setDescription( $event1->getDescription() )
            // fire off the alarm one day before
                        ->setTrigger( '-P1D' );

        // alter day and time for one event in recurrence set
        $event2 = $vcalendar->newVevent()
                            ->setTransp( Vcalendar::OPAQUE )
                            ->setClass( Vcalendar::P_BLIC )
            // reference to one event in recurrence set
                            ->setUid( $event1->getUid() )
                            ->setSequence( 2 )
            // pointer to event in the recurrence set
                            ->setRecurrenceid( '20190505T090000 Europe/Stockholm' )
            // reason text
                            ->setDescription(
                'Altered day and time for event 2019-05-05',
                [ Vcalendar::ALTREP => 'CID:<FFFF__=0ABBE548DFE235B58f9e8a93d@coffeebean.com>' ]
            )
                            ->setComment( 'Now we are working hard for two hours' )
            // the altered day and time with duration
                            ->setDtstart(
                new DateTime(
                    '20190504T100000',
                    new DateTimezone( 'Europe/Stockholm' )
                )
            )
                            ->setDuration( 'PT2H' )
            // add alarm (i.e. same as event1)
                            ->setComponent( $event1->getComponent( Vcalendar::VALARM ) );

        // apply appropriate Vtimezone with Standard/DayLight components
        $vcalendar->vtimezonePopulate();

        return $vcalendar;
    }

    /**
     * Vtodo calendar sub-provider
     */
    public static function vtodoCalendarSubProvider() {

        // create a new calendar
        $vcalendar = Vcalendar::factory( [ Vcalendar::UNIQUE_ID => "kigkonsult.se", ] )
            // with calendaring info
                              ->setMethod( Vcalendar::PUBLISH )
                              ->setXprop(
                                  Vcalendar::X_WR_CALNAME,
                                  "Calendar Sample"
                              )
                              ->setXprop(
                                  Vcalendar::X_WR_CALDESC,
                                  "The calendar Description"
                              )
                              ->setXprop(
                                  Vcalendar::X_WR_RELCALID,
                                  "3E26604A-50F4-4449-8B3E-E4F4932D05B5"
                              )
                              ->setXprop(
                                  Vcalendar::X_WR_TIMEZONE,
                                  "Europe/Stockholm"
                              );

        // create a new todo
        $todo1 = $vcalendar->newVtodo()
                            ->setClass( Vcalendar::P_BLIC )
                            ->setSequence( 1 )
            // describe the event
                            ->setSummary( 'Scheduled meeting with six occurrences' )
                            ->setDescription(
                                'Agenda for the the meeting...',
                                [ Vcalendar::ALTREP => 'CID:<FFFF__=0ABBE548DFE235B58f9e8a93d@coffeebean.com>' ]
                            )
                            ->setComment( 'It\'s going to be fun..' )
                            ->setLocation( 'Kafé Ekorren Stockholm' )
                            ->setGeo( '59.32206', '18.12485' )
            // set the time
                            ->setDtstart(
                                new DateTime(
                                    '20190421T090000',
                                    new DateTimezone( 'Europe/Stockholm' )
                                )
                            )
                            ->setDue(
                                new DateTime(
                                    '20190421T100000',
                                    new DateTimezone( 'Europe/Stockholm' )
                                )
                            )
            // with recurrence rule
                            ->setRrule(
                                [
                                    Vcalendar::FREQ  => Vcalendar::WEEKLY,
                                    Vcalendar::COUNT => 5,
                                    ]
                            )
            // and a another using a recurrence date
                            ->setRdate(
                                [
                                    new DateTime(
                                        '20190609T090000',
                                        new DateTimezone( 'Europe/Stockholm' )
                                    ),
                                    new DateTime(
                                        '20190609T110000',
                                        new DateTimezone( 'Europe/Stockholm' )
                                    ),
                                ],
                                [ Vcalendar::VALUE => Vcalendar::PERIOD ]
                            )
            // and revoke recurrence dates
                            ->setExdate(
                                new DateTime(
                                    '2019-05-12 09:00:00',
                                    new DateTimezone( 'Europe/Stockholm' )
                                )
                            )
            // organizer, chair  and some participants
                            ->setContact(
                                'Head Office, coffeebean Corp, Acme city',
                                [ Vcalendar::ALTREP => "http://coffeebean.com/contacts.vcf" ]
                            )
                            ->setOrganizer(
                                'secretary2@coffeebean.com',
                                [ Vcalendar::SENT_BY => 'sent_by.Secretary.staff@CoffeeBean.com' ]
                            )
                            ->setAttendee(
                                'VicePresident@coffeebean.com',
                                [
                                    Vcalendar::ROLE     => Vcalendar::CHAIR,
                                    Vcalendar::PARTSTAT => Vcalendar::ACCEPTED,
                                    Vcalendar::RSVP     => Vcalendar::FALSE,
                                    Vcalendar::CN       => 'President CoffeeBean',
                                    Vcalendar::SENT_BY  => 'sent_by.VicePresident@coffeebean.com',
                                ]
                            )
                            ->setAttendee(
                                'participant1.team1@coffeebean.com',
                                [
                                    Vcalendar::ROLE     => Vcalendar::REQ_PARTICIPANT,
                                    Vcalendar::PARTSTAT => Vcalendar::NEEDS_ACTION,
                                    Vcalendar::RSVP     => Vcalendar::TRUE,
                                    Vcalendar::CN       => 'Participant1 CoffeeBean',
                                    Vcalendar::MEMBER   => [
                                        'member1@coffeebean.com',
                                        'member2@coffeebean.com'
                                    ],
                                    Vcalendar::DELEGATED_TO => [
                                        'delegated_to.1@coffeebean.com',
                                        'delegated_to.2@coffeebean.com'
                                    ],
                                ]
                            )
                            ->setAttendee(
                                'participant2@coffeebean.com',
                                [
                                    Vcalendar::ROLE     => Vcalendar::REQ_PARTICIPANT,
                                    Vcalendar::PARTSTAT => Vcalendar::NEEDS_ACTION,
                                    Vcalendar::RSVP     => Vcalendar::TRUE,
                                    Vcalendar::CN       => 'Participant2 CoffeeBean',
                                ]
                            );
        // add an alarm for the todo
        $alarm = $todo1->newValarm()
                        ->setAction( Vcalendar::DISPLAY )
                        ->setDescription( $todo1->getDescription())
            // fire off the alarm one day before
                        ->setTrigger( '-P1D' );

        // alter day and time for one todo in recurrence set
        $event2 = $vcalendar->newVtodo()
                            ->setClass( Vcalendar::P_BLIC )
            // reference to one event in recurrence set
                            ->setUid( $todo1->getUid())
                            ->setSequence( 2 )
            // pointer to event in the recurrence set
                            ->setRecurrenceid( '20190505T090000 Europe/Stockholm' )
            // reason text
                            ->setDescription(
                                'Altered day and time for event 2019-05-05',
                                [ Vcalendar::ALTREP => 'CID:<FFFF__=0ABBE548DFE235B58f9e8a93d@coffeebean.com>' ]
                            )
                            ->setComment( 'Now we are working hard for two hours' )
            // the altered day and time with duration
                            ->setDtstart(
                                new DateTime(
                                    '20190504T100000',
                                    new DateTimezone( 'Europe/Stockholm' )
                                )
                            )
                            ->setDuration( 'PT2H' )
            // add alarm (i.e. same as event1)
                            ->setComponent( $todo1->getComponent( Vcalendar::VALARM ));

        // apply appropriate Vtimezone with Standard/DayLight components
        $vcalendar->vtimezonePopulate();

        return $vcalendar;
    }

    /**
     * SelectComponentsTest provider
     */
    public function SelectComponentsTestProvider() {

        $veventCalendar = self::veventCalendarSubProvider();
        $todoCalendar   = self::vtodoCalendarSubProvider();

        $dataArr = [];

        $dataArr[] = [
            11,
            clone $veventCalendar,
            null
        ];

        $dataArr[] = [
            12,
            clone $veventCalendar,
            strtolower( Vcalendar::VEVENT )
        ];

        $dataArr[] = [
            13,
            clone $veventCalendar,
            [ strtolower( Vcalendar::VEVENT ), strtolower( Vcalendar::VTODO ) ]
        ];

        $dataArr[] = [
            21,
            clone $todoCalendar,
            null
        ];

        $dataArr[] = [
            22,
            clone $todoCalendar,
            strtolower( Vcalendar::VTODO )
        ];

        $dataArr[] = [
            23,
            clone $todoCalendar,
            [ strtolower( Vcalendar::VEVENT ), strtolower( Vcalendar::VTODO ) ]
        ];

        return $dataArr;
    }

    /**
     * Testing Vcalendar::selectComponents
     *
     * @test
     * @dataProvider SelectComponentsTestProvider
     * @param int          $case
     * @param Vcalendar    $vcalendar
     * @param string|array $compType
     * @throws Exception
     */
    public function SelectComponentsTest( $case, Vcalendar $vcalendar, $compType = null ) {
        static $FMTerr = 'error in case#%d';
        $errStr = sprintf( $FMTerr, $case );

        $selectComponents = $vcalendar->selectComponents(
            new DateTime( '20190421T000000', new DateTimezone( 'Europe/Stockholm' )),
            new DateTime( '20190630T000000', new DateTimezone( 'Europe/Stockholm' ))
            ,null, null, null, null,
            $compType
        );

// 2019-04-21
        $this->assertTrue( isset( $selectComponents[2019][4][21][0] ), $errStr . 10 );
        /*
        $this->assertEquals(
            '2019-04-21 09:00:00 Europe/Stockholm',
            $selectComponents[2019][4][21][0]->getXrop( Vcalendar::X_RECURRENCE )[1]
        );
        */
        $this->assertEquals(
            '2019-04-21 09:00:00 Europe/Stockholm',
            $selectComponents[2019][4][21][0]->getXprop( Vcalendar::X_CURRENT_DTSTART )[1],
            $errStr . 11
        );
        if( false == ( $value = $selectComponents[2019][4][21][0]->getXprop( Vcalendar::X_CURRENT_DTEND ))) {
            $value = $selectComponents[2019][4][21][0]->getXprop( Vcalendar::X_CURRENT_DUE );
        }
        $this->assertEquals(
            '2019-04-21 10:00:00 Europe/Stockholm',
            $value[1],
            $errStr . 12
        );

// 2019-04-28
        $this->assertTrue( isset( $selectComponents[2019][4][28][0] ));
        $this->assertEquals(
            2,
            $selectComponents[2019][4][28][0]->getXprop( Vcalendar::X_RECURRENCE )[1],
            $errStr . 13
        );
        $this->assertEquals(
            '2019-04-28 09:00:00 Europe/Stockholm',
            $selectComponents[2019][4][28][0]->getXprop( Vcalendar::X_CURRENT_DTSTART )[1],
            $errStr . 14
        );
        if( false == ( $value = $selectComponents[2019][4][28][0]->getXprop( Vcalendar::X_CURRENT_DTEND ))) {
            $value = $selectComponents[2019][4][28][0]->getXprop( Vcalendar::X_CURRENT_DUE );
        }
        $this->assertEquals(
            '2019-04-28 10:00:00 Europe/Stockholm',
            $value[1],
            $errStr . 15
        );

// 2019-05-04
        $this->assertTrue( isset( $selectComponents[2019][5][4][0] ));
        $this->assertEquals(
            3,
            $selectComponents[2019][5][4][0]->getXprop( Vcalendar::X_RECURRENCE )[1],
            $errStr . 16
        );
        $this->assertEquals(
            '2019-05-04 10:00:00 Europe/Stockholm',
            $selectComponents[2019][5][4][0]->getXprop( Vcalendar::X_CURRENT_DTSTART )[1],
            $errStr . 17
        );
        if( false == ( $value = $selectComponents[2019][5][4][0]->getXprop( Vcalendar::X_CURRENT_DTEND ))) {
            $value = $selectComponents[2019][5][4][0]->getXprop( Vcalendar::X_CURRENT_DUE );
        }
        $this->assertEquals(
            '2019-05-04 12:00:00 Europe/Stockholm',
            $value[1],
            $errStr . 18
        );

// 2019-05-19
        $this->assertTrue( isset( $selectComponents[2019][5][19][0] ));
        $this->assertEquals(
            4,
            $selectComponents[2019][5][19][0]->getXprop( Vcalendar::X_RECURRENCE )[1],
            $errStr . 19
        );
        $this->assertEquals(
            '2019-05-19 09:00:00 Europe/Stockholm',
            $selectComponents[2019][5][19][0]->getXprop( Vcalendar::X_CURRENT_DTSTART )[1],
            $errStr . 20
        );
        if( false == ( $value = $selectComponents[2019][5][19][0]->getXprop( Vcalendar::X_CURRENT_DTEND ))) {
            $value = $selectComponents[2019][5][19][0]->getXprop( Vcalendar::X_CURRENT_DUE );
        }
        $this->assertEquals(
            '2019-05-19 10:00:00 Europe/Stockholm',
            $value[1],
            $errStr . 21
        );

// 2019-06-09
        $this->assertTrue( isset( $selectComponents[2019][6][9][0] ));
        $this->assertEquals(
            5,
            $selectComponents[2019][6][9][0]->getXprop( Vcalendar::X_RECURRENCE )[1],
            $errStr . 22
        );
        $this->assertEquals(
            '2019-06-09 09:00:00 Europe/Stockholm',
            $selectComponents[2019][6][9][0]->getXprop( Vcalendar::X_CURRENT_DTSTART )[1],
            $errStr . 23
        );
        if( false == ( $value = $selectComponents[2019][6][9][0]->getXprop( Vcalendar::X_CURRENT_DTEND ))) {
            $value = $selectComponents[2019][6][9][0]->getXprop( Vcalendar::X_CURRENT_DUE );
        }
        $this->assertEquals(
            '2019-06-09 11:00:00 Europe/Stockholm',
            $value[1],
            $errStr . 24
        );

    }

}