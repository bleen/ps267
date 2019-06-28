<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2019 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.27.17
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
use Kigkonsult\Icalcreator\Util\DateTimeFactory;

/**
 * class VcalendarTest, testing Vcalendar properties AND (the default) components UID/DTSTAMP properties
 *    CALSCALE
 *    METHOD
 *    VERSION
 *    PRODID (implicit)
 *    Not X-property, tested in MiscTest
 *
 * @author      Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  2.27.14 - 2019-01-24
 */
class VcalendarTest extends TestCase
{
    private static $ERRFMT = "Error %sin case #%s, %s <%s>->%s";


    /**
     * Testing Vcalendar config
     *
     * @test
     */
    public function vcalendarTest0() {


        $config = [
            Vcalendar::ALLOWEMPTY => false,
            Vcalendar::UNIQUE_ID  => 'kigkonsult.se',
            Vcalendar::DELIMITER  => '-',
            Vcalendar::DIRECTORY  => sys_get_temp_dir(),
            Vcalendar::FILENAME   => 'abc.ics',
        ];
        $vcalendar    = new Vcalendar( $config );
        $fakeFileName = $vcalendar->getConfig( Vcalendar::FILENAME );
        $vcalendar->setConfig( Vcalendar::FILENAME, 'abc.ics' );

        $this->assertEquals( $config[Vcalendar::ALLOWEMPTY], $vcalendar->getConfig( Vcalendar::ALLOWEMPTY ));
        $this->assertEquals( $config[Vcalendar::UNIQUE_ID],  $vcalendar->getConfig( Vcalendar::UNIQUE_ID ));
        $this->assertEquals( $config[Vcalendar::DELIMITER],  $vcalendar->getConfig( Vcalendar::DELIMITER ));
        $this->assertEquals( $config[Vcalendar::DIRECTORY],  $vcalendar->getConfig( Vcalendar::DIRECTORY ));
        $this->assertEquals( $config[Vcalendar::FILENAME],   $vcalendar->getConfig( Vcalendar::FILENAME ));

        $fileInfo = $vcalendar->getConfig( Vcalendar::FILEINFO );
        $this->assertEquals( $fileInfo[0],  $vcalendar->getConfig( Vcalendar::DIRECTORY ));
        $this->assertEquals( $fileInfo[1],  $vcalendar->getConfig( Vcalendar::FILENAME ));
        $this->assertEquals( $fileInfo[2],  $vcalendar->getConfig( Vcalendar::FILESIZE ));

        $vcalendar->setConfig( Vcalendar::DELIMITER, DIRECTORY_SEPARATOR );
        $dirFile = $config[Vcalendar::DIRECTORY] . DIRECTORY_SEPARATOR . $config[Vcalendar::FILENAME];
        $this->assertEquals( $dirFile,   $vcalendar->getConfig( Vcalendar::DIRFILE ));

        $url      = 'url@exampel.com/' . $fakeFileName;
        $vcalendar->setConfig( Vcalendar::URL, $url );
        $this->assertEquals( $url,          $vcalendar->getConfig( Vcalendar::URL ));
        $this->assertEquals( '.',   $vcalendar->getConfig( Vcalendar::DIRECTORY ));
        $this->assertEquals( $fakeFileName, $vcalendar->getConfig( Vcalendar::FILENAME ));
    }

    /**
     * vcalendarTest1 provider
     */
    public function vcalendarTest1Provider() {

        $dataArr = [];

        $value     = 'GREGORIAN';
        $dataArr[] = [
            1,
            Vcalendar::CALSCALE,
            $value,
            $value,
            ':' . $value
        ];

        $value = Vcalendar::P_BLIC;
        $dataArr[] = [
            5,
            Vcalendar::METHOD,
            $value,
            $value,
            ':' . $value
        ];
/*
        $value = 'Hejsan-Hopp';
        $dataArr[] = [
            9,
            Vcalendar::PRODID,
            $value,
            $value,
            ':' . $value
        ];
*/
        $value = '2.1';
        $dataArr[] = [
            19,
            Vcalendar::VERSION,
            $value,
            $value,
            ':' . $value
        ];

        return $dataArr;
    }

    /**
     * Testing Vcalendar
     *
     * @test
     * @dataProvider vcalendarTest1Provider
     * @param int    $case
     * @param string $propName
     * @param mixed  $value
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function vcalendarTest1(
        $case,
        $propName,
        $value,
        $expectedGet,
        $expectedString
    ) {
        $vcalendar = Vcalendar::factory();
        
        $getMethod    = Vcalendar::getGetMethodName( $propName );
        $createMethod = Vcalendar::getCreateMethodName( $propName );
        $deleteMethod = Vcalendar::getDeleteMethodName( $propName );
        $setMethod    = Vcalendar::getSetMethodName( $propName );
        $vcalendar->{$setMethod}( $value );
        $getValue = $vcalendar->{$getMethod}();
        $this->assertEquals(
            $expectedGet,
            $getValue,
            sprintf( self::$ERRFMT, null, $case, __FUNCTION__, 'Vcalendar', $getMethod )
        );
        $this->assertEquals(
            strtoupper( $propName ) . $expectedString,
            trim( $vcalendar->{$createMethod}() ),
            sprintf( self::$ERRFMT, null, $case, __FUNCTION__, 'Vcalendar', $createMethod )
        );

        switch( $propName ) {
            case Vcalendar::CALSCALE :
                $vcalendar->{$deleteMethod}();
                $this->assertNotFalse(
                    $vcalendar->{$getMethod}(),
                    sprintf( self::$ERRFMT, '(after delete) ', $case, __FUNCTION__, 'Vcalendar', $getMethod )
                );
                break;
            case Vcalendar::METHOD :
                $vcalendar->{$deleteMethod}();
                $this->assertFalse(
                    $vcalendar->{$getMethod}(),
                    sprintf( self::$ERRFMT, '(after delete) ', $case, __FUNCTION__, 'Vcalendar', $getMethod )
                );
                $vcalendar->{$setMethod}( $value );
                break;
            case Vcalendar::VERSION :
                break;
        }

        $v = $vcalendar->newVevent();
        $v->deleteUID();
        $this->assertNotFalse(
            $v->getUID(),
            sprintf( self::$ERRFMT, null, $case, __FUNCTION__, 'VEVENT', 'getUid' )
        );
        $v->deleteDtstamp();
        $this->assertNotFalse(
            $v->getDtstamp(),
            sprintf( self::$ERRFMT, null, $case, __FUNCTION__, 'VEVENT', 'getDtstamp' )
        );

        $calendar1String = $vcalendar->createCalendar();

        $vcalendar2 = new Vcalendar();
        $vcalendar2->parse( $calendar1String );
        if( Vcalendar::VERSION == $propName ) {
            $vcalendar2->{$setMethod}( $value );
        }
        $this->assertEquals(
            $calendar1String,
            $vcalendar2->createCalendar(),
            sprintf( "Error in compare (1) " . __FUNCTION__ )
        );

        $vcalendar2->setConfig( Vcalendar::DIRECTORY, sys_get_temp_dir());
        $dirFile = $vcalendar2->getConfig( Vcalendar::DIRFILE );
        $vcalendar2->saveCalendar();
        $this->assertFileExists(
            $dirFile,
            sprintf( "File not found Error " . __FUNCTION__ )
        );

        $vcalendar3 = Vcalendar::factory( [
            Vcalendar::DIRECTORY => $vcalendar2->getConfig( Vcalendar::DIRECTORY ),
            Vcalendar::FILENAME  => $vcalendar2->getConfig( Vcalendar::FILENAME )
        ]);
        $vcalendar3->parse();
        if( Vcalendar::VERSION == $propName ) {
            $vcalendar3->{$setMethod}( $value );
        }

        $this->assertEquals(
            $calendar1String,
            $vcalendar3->createCalendar(),
            sprintf( "Error in compare (2) " . __FUNCTION__ )
        );

        unlink( $dirFile );

        unset( $vcalendar, $vcalendar2, $vcalendar3 );
        $this->assertFalse( isset( $vcalendar ));
    }

    /**
     * Testing Vcalendar component management
     *
     * @test
     */
    public function vcalendarTest2() {
        $vcalendar = new Vcalendar();

        $v = new Vevent();
        $uid = $v->getUid();
        $vcalendar->setComponent( $v, 6 );

        $v2 = $vcalendar->getComponent( 6 );
        $this->assertEquals( $uid,  $v2->getUid());

        $date = DateTimeFactory::getDateArrayFromDateTime(
            DateTimeFactory::factory( 'now', Vcalendar::UTC )
        );
        $v2->setDtstart( $date );
        $vcalendar->setComponent( $v2, 6 );
        $v2 = $vcalendar->getComponent( 6 );
        $this->assertEquals( $date, $v2->getDtstart());

        $vcalendar->deleteComponent( 6 );
        $this->assertFalse( $vcalendar->getComponent( 6 ));
        $this->assertFalse( $vcalendar->getComponent());

        $this->assertTrue(
            ( 0 == $vcalendar->countComponents()),
            'deleteComponent-error 1, has ' . $vcalendar->countComponents()
        );


        for( $x = 1; $x <= 12; $x++ ) {
            $vx1   = $vcalendar->newVevent();
            $vx1->setXprop( 'X-SET_NO', $x );
        }

        for( $x = 13; $x <= 14; $x++ ) {
            $vx1   = $vcalendar->newVtodo();
            $vx1->setXprop( 'X-SET_NO', $x );
        }
        for( $x = 15; $x <= 30; $x++ ) {
            $vx1   = $vcalendar->newVevent();
            $vx1->setXprop( 'X-SET_NO', $x );
        }
        $this->assertTrue(
            ( 30 == $vcalendar->countComponents()),
            'deleteComponent-error 2, has ' . $vcalendar->countComponents()
        );

        $testStr = 'Testing this #';

        $testArr = [];

        $value = $testStr . 1;
        $testArr[Vcalendar::CATEGORIES] = [ 1, $value ];
        $v     = $vcalendar->getComponent( 1 );
        $v->setCategories( $value );
        $v->setXprop( 'X-VALUE', $value );
        $v->setComment( 1 ); // remember $x
        $v->setXprop( 'X-UPD_NO', 1 );
        $vcalendar->replaceComponent( $v );

        $value = $testStr . 2;
        $testArr[Vcalendar::LOCATION] = [ 2, $value ];
        $v     = $vcalendar->getComponent( 2 );
        $v->setLocation( $value );
        $v->setComment( 2 ); // remember $x
        $v->setXprop( 'X-VALUE', $value );
        $v->setXprop( 'X-UPD_NO', 2 );
        $vcalendar->replaceComponent( $v );

        $value = $testStr . 3;
        $testArr[Vcalendar::SUMMARY] = [ 3, $value ];
        $v     = $vcalendar->getComponent( 3 );
        $v->setSummary( $value );
        $v->setComment( 3 ); // remember $x
        $v->setXprop( 'X-VALUE', $value );
        $v->setXprop( 'X-UPD_NO', 3 );
        $vcalendar->replaceComponent( $v );

        $value = $testStr . 4;
        $testArr[Vcalendar::RESOURCES] = [ 4, $value ];
        $v     = $vcalendar->getComponent( 4 );
        $v->setResources( $value );
        $v->setComment( 4 ); // remember $x
        $v->setXprop( 'X-VALUE', $value );
        $v->setXprop( 'X-UPD_NO', 4 );
        $vcalendar->replaceComponent( $v );


        $testArr[Vcalendar::PRIORITY] = [ 5, 5 ];
        $v = $vcalendar->getComponent( 5 );
        $v->setPriority( 5 );
        $v->setComment( 5 ); // remember $x
        $v->setXprop( 'X-VALUE', 5 );
        $v->setXprop( 'X-UPD_NO', 5 );
        $vcalendar->replaceComponent( $v );

        $testArr[Vcalendar::STATUS] = [ 6, Vcalendar::TENTATIVE ];
        $v = $vcalendar->getComponent( 6 );
        $v->setStatus( Vcalendar::TENTATIVE );
        $v->setComment( 6 ); // remember $x
        $v->setXprop( 'X-VALUE', Vcalendar::TENTATIVE );
        $v->setXprop( 'X-UPD_NO', 6 );
        $vcalendar->replaceComponent( $v );

        $date = DateTimeFactory::getDateArrayFromDateTime(
            DateTimeFactory::factory( '+' . 7 . ' days', Vcalendar::UTC )
        );
        $dateStr = DateTimeFactory::getYMDString( $date ) . DateTimeFactory::getHisString( $date );
        $testArr[Vcalendar::DTSTART] = [ 7, $dateStr ];
        $v = $vcalendar->getComponent( 7 );
        $v->setDtstart( $date );
        $v->setComment( 7 ); // remember $x
        $v->setXprop( 'X-VALUE', $date );
        $v->setXprop( 'X-UPD_NO', 7 );
        $vcalendar->replaceComponent( $v );

        $date = DateTimeFactory::getDateArrayFromDateTime(
            DateTimeFactory::factory( '+' . 8 . ' days', Vcalendar::UTC )
        );
        $dateStr = DateTimeFactory::getYMDString( $date ) . DateTimeFactory::getHisString( $date );
        $testArr[Vcalendar::DTSTAMP] = [ 8, $dateStr ];
        $v = $vcalendar->getComponent( 8 );
        $v->setDtstamp( $date );
        $v->setComment( 8 ); // remember $x
        $v->setXprop( 'X-VALUE', $date );
        $v->setXprop( 'X-UPD_NO', 8 );
        $vcalendar->replaceComponent( $v );

        $date = DateTimeFactory::getDateArrayFromDateTime(
            DateTimeFactory::factory( '+' . 9 . ' days', Vcalendar::UTC )
        );
        $dateStr = DateTimeFactory::getYMDString( $date ) . DateTimeFactory::getHisString( $date );
        $testArr[Vcalendar::DTEND] = [ 9, $dateStr ];
        $v = $vcalendar->getComponent( 9 );
        $v->setDtend( $date );
        $v->setComment( 9 ); // remember $x
        $v->setXprop( 'X-VALUE', $date );
        $vcalendar->replaceComponent( $v );

        $date = DateTimeFactory::getDateArrayFromDateTime(
            DateTimeFactory::factory( '+' . 10 . ' days', Vcalendar::UTC )
        );
        $dateStr = DateTimeFactory::getYMDString( $date ) . DateTimeFactory::getHisString( $date );
        $testArr[Vcalendar::CREATED] = [ 10, $dateStr ];
        $v = $vcalendar->getComponent( 10 );
        $v->setCreated( $date );
        $v->setComment( 10 ); // remember $x
        $v->setXprop( 'X-VALUE', $date );
        $vcalendar->replaceComponent( $v );

        $date = DateTimeFactory::getDateArrayFromDateTime(
            DateTimeFactory::factory( '+' . 11 . ' days', Vcalendar::UTC )
        );
        $dateStr = DateTimeFactory::getYMDString( $date ) . DateTimeFactory::getHisString( $date );
        $testArr[Vcalendar::LAST_MODIFIED] = [ 11, $dateStr ];
        $v = $vcalendar->getComponent( 11 );
        $v->setLastmodified( $date );
        $v->setComment( 11 ); // remember $x
        $v->setXprop( 'X-VALUE', $date );
        $vcalendar->replaceComponent( $v );

        $date = DateTimeFactory::getDateArrayFromDateTime(
            DateTimeFactory::factory( '+' . 7 . ' days', Vcalendar::UTC )
        );
        $dateStr = DateTimeFactory::getYMDString( $date ) . DateTimeFactory::getHisString( $date );
        $testArr[Vcalendar::RECURRENCE_ID] = [ 12, $dateStr ];
        $v = $vcalendar->getComponent( 12 );
        $v->setRecurrenceid( $date );
        $v->setComment( 12 ); // remember $x
        $v->setXprop( 'X-VALUE', $date );
        $vcalendar->replaceComponent( $v );


        $date = DateTimeFactory::getDateArrayFromDateTime(
            DateTimeFactory::factory( '+' . 13 . ' days', Vcalendar::UTC )
        );
        $dateStr = DateTimeFactory::getYMDString( $date ) . DateTimeFactory::getHisString( $date );
        $testArr[Vcalendar::COMPLETED] = [ 13, $dateStr ]; // Vtodo
        $v = $vcalendar->getComponent( 13 );
        $v->setCompleted( $date );
        $v->setComment( 13 ); // remember $x
        $v->setXprop( 'X-VALUE', $date );
        $vcalendar->replaceComponent( $v );

        $date = DateTimeFactory::getDateArrayFromDateTime(
            DateTimeFactory::factory( '+' . 14 . ' days', Vcalendar::UTC )
        );
        $dateStr = DateTimeFactory::getYMDString( $date ) . DateTimeFactory::getHisString( $date );
        $testArr[Vcalendar::DUE] = [ 14, $dateStr ]; // Vtodo
        $v = $vcalendar->getComponent( 14 );
        $v->setDue( $date );
        $v->setComment( 14 ); // remember $x
        $v->setXprop( 'X-VALUE', $date );
        $vcalendar->replaceComponent( $v );


        $contact  = 'test.this.contact@exsample.com';
        $testArr[Vcalendar::CONTACT] = [ 15, $contact ];
        $v  = $vcalendar->getComponent( 15 );
        $v->setContact( $contact );
        $v->setComment( 15 ); // remember $x
        $v->setXprop( 'X-VALUE', $contact );
        $vcalendar->replaceComponent( $v );

        $attendee = 'MAILTO:test.this.attendee@exsample.com';
        $testArr[Vcalendar::ATTENDEE] = [ 16, $attendee ];
        $v = $vcalendar->getComponent( 16 );
        $v->setAttendee( $attendee );
        $v->setComment( 16 ); // remember $x
        $v->setXprop( 'X-VALUE', $attendee );
        $vcalendar->replaceComponent( $v );

        $organizer = 'MAILTO:test.this.organizer@exsample.com';
        $testArr[Vcalendar::ORGANIZER] = [ 17, $organizer ];
        $v         = $vcalendar->getComponent( 17 );
        $v->setOrganizer( $organizer );
        $v->setComment( 17 ); // remember $x
        $v->setXprop( 'X-VALUE', $organizer );
        $vcalendar->replaceComponent( $v );

        $relatedTo = 'test this related-to';
        $testArr[Vcalendar::RELATED_TO] = [ 18, $relatedTo ];
        $v         = $vcalendar->getComponent( 18 );
        $v->setRelatedto( $relatedTo );
        $v->setComment( 18 ); // remember $x
        $v->setXprop( 'X-VALUE', $relatedTo );
        $vcalendar->replaceComponent( $v );

        $url = 'http://test.this.url@exsample.com';
        $testArr[Vcalendar::URL] = [ 19, $url ];
        $v   = $vcalendar->getComponent( 19 );
        $v->setUrl( $url );
        $v->setComment( 19 ); // remember $x
        $v->setXprop( 'X-VALUE', $url );
        $vcalendar->replaceComponent( $v );

        $uid = 'test this uid';
        $testArr[Vcalendar::UID] = [ 20, $uid ];
        $v   = $vcalendar->getComponent( 20 );
        $v->setUid( $uid );
        $v->setComment( 20 ); // remember $x
        $v->setXprop( 'X-VALUE', $uid );
        $vcalendar->setComponent( $v, 20 );

//        error_log( __FUNCTION__ . ' calendar : ' . var_export( $vcalendar, true )); // test ###

        foreach( $testArr as $propName => $testValues ) {
            // fetch on uid
            $v = $vcalendar->getComponent( [ $propName => $testValues[1] ] );
            $this->assertNotFalse(
                $v,
                'getComponent not-found-error 1 for #' . $testValues[0] . ' : ' . $propName
            );
            // check test case number
            $ordNo = $v->getComment();
            $this->assertEquals(
                $testValues[0],
                $ordNo,
                'getComponent-error 2 for #' . $testValues[0] . ' : ' . $propName
            );
            // check set values
            $getMethod = Vcalendar::getGetMethodName( $propName );
            $this->assertEquals(
                $v->{$getMethod}(),
                $v->getXprop( 'X-VALUE' )[1],
                'getComponent-error 3 for #' . $testValues[0] . ' : ' . $propName
            );
        }

        // check fetch on config compsinfo
        foreach( $vcalendar->getConfig( Vcalendar::COMPSINFO ) as $cix => $compInfo ) {

            $v = $vcalendar->getComponent( $compInfo['uid'] );

            $this->assertEquals(
                $compInfo['type'],
                $v->getCompType(),
                'getComponent-error 5 for #' . $testValues[0] . ' : ' . $propName
            );

        }

        // fetch all components
        $compArr = [];
        while( $comp = $vcalendar->getComponent()) {
            $compArr[] = $comp;
        }

        // check fetch on type and order number
        $v = $vcalendar->getComponent( Vcalendar::VTODO, 1 );
        $v = $vcalendar->getComponent( Vcalendar::VTODO, 2 );
        $this->assertFalse( $vcalendar->getComponent( Vcalendar::VTODO, 3 ) );

        // check number of components
        $this->assertTrue(
            ( 30 == $vcalendar->countComponents() ),
            'deleteComponent-error 6, has ' . $vcalendar->countComponents()
        );

        for( $x = 18; $x <= 1; $x-- ) {
            $this->assertTrue(
                $vcalendar->deleteComponent( Vcalendar::VEVENT, $x ),
                'deleteComponent-error 7 on #' . $x
            );
        }
        while( $vcalendar->deleteComponent( Vcalendar::VEVENT ) ) {
            continue;
        }
        $this->assertFalse(
            $vcalendar->deleteComponent( Vcalendar::VEVENT ),
            'deleteComponent-error 8'
        );
        $this->assertTrue(
            ( 2 == $vcalendar->countComponents() ),
            'deleteComponent-error 9, has ' . $vcalendar->countComponents()
        );

        while( $vcalendar->deleteComponent( Vcalendar::VTODO ) ) {
            continue;
        }
        $this->assertFalse(
            $vcalendar->deleteComponent( Vcalendar::VTODO ),
            'deleteComponent-error 10'
        );
        $this->assertTrue(
            ( 0 == $vcalendar->countComponents() ),
            'deleteComponent-error 11, has ' . $vcalendar->countComponents()
        );

        // check components are set in order
        foreach( $compArr as $comp ) {
            $vcalendar->setComponent( $comp );
        }
        $x = 0;
        while( $comp = $vcalendar->getComponent()) {
            $x += 1;
            $this->assertEquals(
                $x,
                $comp->getXprop( 'X-SET_NO' )[1],
                'setComponent-error 12, comp . ' . $x . ' is not in order'
            );
        }
        // check number of components
        $this->assertTrue(
            ( 30 == $vcalendar->countComponents() ),
            'deleteComponent-error 13, has ' . $vcalendar->countComponents()
        );
    }

}