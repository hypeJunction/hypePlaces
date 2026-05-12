<?php

namespace hypeJunction\Places\Tests\Unit;

use PHPUnit\Framework\TestCase;

class PlaceConstantsTest extends TestCase {

    public function testSubtypeConstantIsDefined(): void {
        $this->assertEquals('hjplace', \hypeJunction\Places\Place::SUBTYPE);
    }

    public function testCheckinDurationConstantIsDefined(): void {
        $this->assertEquals(60, \hypeJunction\Places\Place::CHECKIN_DURATION);
    }
}
