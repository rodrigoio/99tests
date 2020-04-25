<?php
namespace App\Test\Core\Domain\Virtual\Integer;

use PHPUnit\Framework\TestCase;
use App\Core\Domain\Virtual\Integer\Element;
use App\Core\Domain\Virtual\Integer\IntegerRange;

/**
 * @group integer_range
 */
class IntegerRangeTest extends TestCase
{
    public function testRangesAreEquals()
    {
        $rangeA = new IntegerRange(new Element(9), new Element(15));
        $rangeB = new IntegerRange(new Element(9), new Element(15));
        $this->assertTrue( $rangeA->equals($rangeB) );
    }

    public function testIfEndIsAlwaysMajorThanStart()
    {
        $range = new IntegerRange(new Element(9), new Element(15));
        $this->assertEquals(new Element(9), $range->getStartValue());
        $this->assertEquals(new Element(15), $range->getEndValue());

        $this->expectException(\InvalidArgumentException::class);
        $range = new IntegerRange(new Element(100), new Element(0));
    }

    public function testHasElement()
    {
        //--------------------------------------------------------------------
        // Regular cases
        //--------------------------------------------------------------------
        // <1 2> 3 4  5  6 7 8 9
        // 1  2  3 4 <5> 6 7 8 9
        $rangeA = new IntegerRange(new Element(1), new Element(2));
        $this->assertFalse( $rangeA->has(new Element(5)) );
        $this->assertFalse( $rangeA->has(new Element(null)) );

        //  1  2 <3 4> 5 6 7 8 9
        // <1> 2  3 4  5 6 7 8 9
        $rangeA = new IntegerRange(new Element(3), new Element(4));
        $this->assertFalse( $rangeA->has(new Element(1)) );
        $this->assertFalse( $rangeA->has(new Element(null)) );

        // 1 <2 3 4 5 6 7 8> 9
        // 1 2 3 4 5 <6> 7 8 9
        $rangeA = new IntegerRange(new Element(2), new Element(8));
        $this->assertTrue( $rangeA->has(new Element(6)) );
        $this->assertFalse( $rangeA->has(new Element(null)) );

        // 1 <2 3 4 5 6 7 8> 9
        // 1 <2> 3 4 5 6 7 8 9
        $rangeA = new IntegerRange(new Element(2), new Element(8));
        $this->assertTrue( $rangeA->has(new Element(2)) );
        $this->assertFalse( $rangeA->has(new Element(null)) );

        // 1 <2 3 4 5 6 7 8> 9
        // 1 2 3 4 5 6 7 <8> 9
        $rangeA = new IntegerRange(new Element(2), new Element(8));
        $this->assertTrue( $rangeA->has(new Element(8)) );
        $this->assertFalse( $rangeA->has(new Element(null)) );

        // 1 <2 3 4 5 6 7 8> 9
        // 1  2 3 4 5 6 7 8 <9>
        $rangeA = new IntegerRange(new Element(2), new Element(8));
        $this->assertFalse( $rangeA->has(new Element(9)) );
        $this->assertFalse( $rangeA->has(new Element(null)) );

        //  1 <2 3 4 5 6 7 8> 9
        // <1> 2 3 4 5 6 7 8  9
        $rangeA = new IntegerRange(new Element(2), new Element(8));
        $this->assertFalse( $rangeA->has(new Element(1)) );
        $this->assertFalse( $rangeA->has(new Element(null)) );

        //--------------------------------------------------------------------
        // Infinity cases
        //--------------------------------------------------------------------
        // - - - - >< - - - -
        $rangeA = new IntegerRange(new Element(null), new Element(null));
        $this->assertTrue( $rangeA->has(new Element(null)) );
        $this->assertTrue( $rangeA->has(new Element(0)) );
        $this->assertTrue( $rangeA->has(new Element(10000)) );
        $this->assertTrue( $rangeA->has(new Element(-10000)) );

        // 1 2 3 4> 5 6 7 8  9
        // 1 2 3 4 5 6 7 <8> 9
        $rangeA = new IntegerRange(new Element(null), new Element(4));
        $this->assertFalse( $rangeA->has(new Element(8)) );
        $this->assertFalse( $rangeA->has(new Element(null)) );

        // 1  2  3 4> 5 6 7 8 9
        // 1 <2> 3 4  5 6 7 8 9
        $rangeA = new IntegerRange(new Element(null), new Element(4));
        $this->assertTrue( $rangeA->has(new Element(2)) );
        $this->assertFalse( $rangeA->has(new Element(null)) );

        // 1  2  3 4 <5 6 7 8 9
        // 1 <2> 3 4  5 6 7 8 9
        $rangeA = new IntegerRange(new Element(5), new Element(null));
        $this->assertFalse( $rangeA->has(new Element(2)) );
        $this->assertFalse( $rangeA->has(new Element(null)) );

        // 1 2 3 4 <5 6  7  8 9
        // 1 2 3 4  5 6 <7> 8 9
        $rangeA = new IntegerRange(new Element(5), new Element(null));
        $this->assertTrue( $rangeA->has(new Element(7)) );
        $this->assertFalse( $rangeA->has(new Element(null)) );

        // 1 2 3  4 <5 6 7 8 9
        // 1 2 3 <4> 5 6 7 8 9
        $rangeA = new IntegerRange(new Element(5), new Element(null));
        $this->assertFalse( $rangeA->has(new Element(4)) );
        $this->assertFalse( $rangeA->has(new Element(null)) );

        // 1 2 3 4> 5  6 7 8 9
        // 1 2 3 4 <5> 6 7 8 9
        $rangeA = new IntegerRange(new Element(null), new Element(4));
        $this->assertFalse( $rangeA->has(new Element(5)) );
        $this->assertFalse( $rangeA->has(new Element(null)) );
    }

    public function testDomainRelation()
    {
        //--------------------------------------------------------------------
        // Regular ranges
        //--------------------------------------------------------------------
        //startsWithLocalDomainEndsWithOuterDomain
        $rangeA = new IntegerRange(new Element(10), new Element(30));
        $rangeB = new IntegerRange(new Element(20), new Element(40));
        //
        $this->assertTrue( $rangeA->startsWithLocalDomainEndsWithOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->startsWithOuterDomainEndsWithLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->outerDomainContainsLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainContainsOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheLeft($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheRight($rangeB) );

        //startsWithOuterDomainEndsWithLocalDomain
        $rangeA = new IntegerRange(new Element(20), new Element(40));
        $rangeB = new IntegerRange(new Element(10), new Element(30));
        //
        $this->assertFalse( $rangeA->startsWithLocalDomainEndsWithOuterDomain($rangeB) );
        $this->assertTrue( $rangeA->startsWithOuterDomainEndsWithLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->outerDomainContainsLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainContainsOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheLeft($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheRight($rangeB) );

        //outerDomainContainsLocalDomain
        $rangeA = new IntegerRange(new Element(20), new Element(40));
        $rangeB = new IntegerRange(new Element(0), new Element(100));
        //
        $this->assertFalse( $rangeA->startsWithLocalDomainEndsWithOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->startsWithOuterDomainEndsWithLocalDomain($rangeB) );
        $this->assertTrue( $rangeA->outerDomainContainsLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainContainsOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheLeft($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheRight($rangeB) );

        //localDomainContainsOuterDomain
        $rangeA = new IntegerRange(new Element(0), new Element(100));
        $rangeB = new IntegerRange(new Element(40), new Element(50));
        //
        $this->assertFalse( $rangeA->startsWithLocalDomainEndsWithOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->startsWithOuterDomainEndsWithLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->outerDomainContainsLocalDomain($rangeB) );
        $this->assertTrue( $rangeA->localDomainContainsOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheLeft($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheRight($rangeB) );

        //localDomainTouchesOuterDomainFromTheLeft
        $rangeA = new IntegerRange(new Element(0), new Element(100), 1);
        $rangeB = new IntegerRange(new Element(101), new Element(150), 1);
        //
        $this->assertFalse( $rangeA->startsWithLocalDomainEndsWithOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->startsWithOuterDomainEndsWithLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->outerDomainContainsLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainContainsOuterDomain($rangeB) );
        $this->assertTrue( $rangeA->localDomainTouchesOuterDomainFromTheLeft($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheRight($rangeB) );

        //localDomainTouchesOuterDomainFromTheRight
        $rangeA = new IntegerRange(new Element(200), new Element(300), 1);
        $rangeB = new IntegerRange(new Element(0), new Element(199), 1);
        //
        $this->assertFalse( $rangeA->startsWithLocalDomainEndsWithOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->startsWithOuterDomainEndsWithLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->outerDomainContainsLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainContainsOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheLeft($rangeB) );
        $this->assertTrue( $rangeA->localDomainTouchesOuterDomainFromTheRight($rangeB) );

        //--------------------------------------------------------------------
        // test with infinity ranges
        //--------------------------------------------------------------------
        //startsWithLocalDomainEndsWithOuterDomain
        $rangeA = new IntegerRange(new Element(null), new Element(30));
        $rangeB = new IntegerRange(new Element(20), new Element(null));
        // 0 10  20 30> 40 50
        // 0 10 <20 30  40 50
        $this->assertTrue( $rangeA->startsWithLocalDomainEndsWithOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->startsWithOuterDomainEndsWithLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->outerDomainContainsLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainContainsOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheLeft($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheRight($rangeB) );

        //startsWithOuterDomainEndsWithLocalDomain
        $rangeA = new IntegerRange(new Element(20), new Element(null));
        $rangeB = new IntegerRange(new Element(null), new Element(30));
        // 0 10 <20 30  40 50
        // 0 10  20 30> 40 50
        $this->assertFalse( $rangeA->startsWithLocalDomainEndsWithOuterDomain($rangeB) );
        $this->assertTrue( $rangeA->startsWithOuterDomainEndsWithLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->outerDomainContainsLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainContainsOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheLeft($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheRight($rangeB) );

        //outerDomainContainsLocalDomain
        $rangeA = new IntegerRange(new Element(20), new Element(50));
        $rangeB = new IntegerRange(new Element(10), new Element(null));
        // 0  10 <20 30 40 50> 100 200
        // 0 <10  20 30 40 50  100 200
        $this->assertFalse( $rangeA->startsWithLocalDomainEndsWithOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->startsWithOuterDomainEndsWithLocalDomain($rangeB) );
        $this->assertTrue( $rangeA->outerDomainContainsLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainContainsOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheLeft($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheRight($rangeB) );

        //outerDomainContainsLocalDomain
        $rangeA = new IntegerRange(new Element(20), new Element(50));
        $rangeB = new IntegerRange(new Element(null), new Element(100));
        // 0 10 <20 30 40 50> 100
        // 0 10  20 30 40 50  100>
        $this->assertFalse( $rangeA->startsWithLocalDomainEndsWithOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->startsWithOuterDomainEndsWithLocalDomain($rangeB) );
        $this->assertTrue( $rangeA->outerDomainContainsLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainContainsOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheLeft($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheRight($rangeB) );

        //outerDomainContainsLocalDomain
        $rangeA = new IntegerRange(new Element(0), new Element(100));
        $rangeB = new IntegerRange(new Element(null), new Element(null));
        // <0 10 20 30 40 50 100>
        //          ><
        $this->assertFalse( $rangeA->startsWithLocalDomainEndsWithOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->startsWithOuterDomainEndsWithLocalDomain($rangeB) );
        $this->assertTrue( $rangeA->outerDomainContainsLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainContainsOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheLeft($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheRight($rangeB) );

        //outerDomainContainsLocalDomain
        $rangeA = new IntegerRange(new Element(null), new Element(100));
        $rangeB = new IntegerRange(new Element(null), new Element(null));
        // 0 10 20 30 40 50 100>
        //          ><
        $this->assertFalse( $rangeA->startsWithLocalDomainEndsWithOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->startsWithOuterDomainEndsWithLocalDomain($rangeB) );
        $this->assertTrue( $rangeA->outerDomainContainsLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainContainsOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheLeft($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheRight($rangeB) );

        //outerDomainContainsLocalDomain
        $rangeA = new IntegerRange(new Element(0), new Element(null));
        $rangeB = new IntegerRange(new Element(null), new Element(null));
        // <0 10 20 30 40 50
        //         ><
        $this->assertFalse( $rangeA->startsWithLocalDomainEndsWithOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->startsWithOuterDomainEndsWithLocalDomain($rangeB) );
        $this->assertTrue( $rangeA->outerDomainContainsLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainContainsOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheLeft($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheRight($rangeB) );

        //localDomainContainsOuterDomain
        $rangeA = new IntegerRange(new Element(10), new Element(null));
        $rangeB = new IntegerRange(new Element(20), new Element(50));
        // 0 <10  20 30 40 50  100 200
        // 0  10 <20 30 40 50> 100 200
        $this->assertFalse( $rangeA->startsWithLocalDomainEndsWithOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->startsWithOuterDomainEndsWithLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->outerDomainContainsLocalDomain($rangeB) );
        $this->assertTrue( $rangeA->localDomainContainsOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheLeft($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheRight($rangeB) );

        //localDomainContainsOuterDomain
        $rangeA = new IntegerRange(new Element(null), new Element(100));
        $rangeB = new IntegerRange(new Element(20), new Element(50));
        // 0 10  20 30 40 50  100>
        // 0 10 <20 30 40 50> 100
        $this->assertFalse( $rangeA->startsWithLocalDomainEndsWithOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->startsWithOuterDomainEndsWithLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->outerDomainContainsLocalDomain($rangeB) );
        $this->assertTrue( $rangeA->localDomainContainsOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheLeft($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheRight($rangeB) );

        //localDomainContainsOuterDomain
        $rangeA = new IntegerRange(new Element(null), new Element(null));
        $rangeB = new IntegerRange(new Element(0), new Element(100));
        //          ><
        // <0 10 20 30 40 50 100>
        $this->assertFalse( $rangeA->startsWithLocalDomainEndsWithOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->startsWithOuterDomainEndsWithLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->outerDomainContainsLocalDomain($rangeB) );
        $this->assertTrue( $rangeA->localDomainContainsOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheLeft($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheRight($rangeB) );

        //localDomainContainsOuterDomain
        $rangeA = new IntegerRange(new Element(null), new Element(null));
        $rangeB = new IntegerRange(new Element(null), new Element(100));
        //          ><
        // 0 10 20 30 40 50 100>
        $this->assertFalse( $rangeA->startsWithLocalDomainEndsWithOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->startsWithOuterDomainEndsWithLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->outerDomainContainsLocalDomain($rangeB) );
        $this->assertTrue( $rangeA->localDomainContainsOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheLeft($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheRight($rangeB) );

        //localDomainContainsOuterDomain
        $rangeA = new IntegerRange(new Element(null), new Element(null));
        $rangeB = new IntegerRange(new Element(0), new Element(null));
        //         ><
        // <0 10 20 30 40 50
        $this->assertFalse( $rangeA->startsWithLocalDomainEndsWithOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->startsWithOuterDomainEndsWithLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->outerDomainContainsLocalDomain($rangeB) );
        $this->assertTrue( $rangeA->localDomainContainsOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheLeft($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheRight($rangeB) );

        //localDomainTouchesOuterDomainFromTheLeft
        $rangeA = new IntegerRange(new Element(null), new Element(100));
        $rangeB = new IntegerRange(new Element(101), new Element(null));
        //
        $this->assertFalse( $rangeA->startsWithLocalDomainEndsWithOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->startsWithOuterDomainEndsWithLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->outerDomainContainsLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainContainsOuterDomain($rangeB) );
        $this->assertTrue( $rangeA->localDomainTouchesOuterDomainFromTheLeft($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheRight($rangeB) );

        //localDomainTouchesOuterDomainFromTheRight
        $rangeA = new IntegerRange(new Element(200), new Element(null));
        $rangeB = new IntegerRange(new Element(null), new Element(199));
        //
        $this->assertFalse( $rangeA->startsWithLocalDomainEndsWithOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->startsWithOuterDomainEndsWithLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->outerDomainContainsLocalDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainContainsOuterDomain($rangeB) );
        $this->assertFalse( $rangeA->localDomainTouchesOuterDomainFromTheLeft($rangeB) );
        $this->assertTrue( $rangeA->localDomainTouchesOuterDomainFromTheRight($rangeB) );
    }

    public function testStartAndEndValues()
    {
        $range = new IntegerRange(new Element(9), new Element(15));

        $this->assertEquals(new Element(9), $range->getStartValue());
        $this->assertEquals(new Element(15), $range->getEndValue());

        // 8 <9 10 14 15> 16
        $this->assertFalse( $range->has(new Element(8)) );
        $this->assertTrue( $range->has(new Element(9)) );
        $this->assertTrue( $range->has(new Element(10)) );
        $this->assertTrue( $range->has(new Element(14)) );
        $this->assertTrue( $range->has(new Element(15)) );
        $this->assertFalse( $range->has(new Element(16)) );
    }

    public function testFromIntegerToInfinityRange()
    {
        $range = new IntegerRange(new Element(9), new Element());

        // -99 0 1 8 <9 16 1000000<
        $this->assertFalse( $range->has(new Element(-99)) );
        $this->assertFalse( $range->has(new Element(0)) );
        $this->assertFalse( $range->has(new Element(1)) );
        $this->assertFalse( $range->has(new Element(8)) );
        $this->assertTrue( $range->has(new Element(9)) );
        $this->assertTrue( $range->has(new Element(16)) );
        $this->assertTrue( $range->has(new Element(1000000)) );
    }

    public function testFromInfinityToInterger()
    {
        $range = new IntegerRange(new Element(), new Element(9));

        // >-1 1 0 8 9> 10 16
        $this->assertTrue( $range->has(new Element(-1)) );
        $this->assertTrue( $range->has(new Element(1)) );
        $this->assertTrue( $range->has(new Element(0)) );
        $this->assertTrue( $range->has(new Element(8)) );
        $this->assertTrue( $range->has(new Element(9)) );
        $this->assertFalse( $range->has(new Element(10)) );
        $this->assertFalse( $range->has(new Element(16)) );
    }

    public function testFromInfinityToInfinity()
    {
        $range = new IntegerRange(new Element(), new Element());

        // >-1000 -100 -10 -1 0 1 10 100 1000<
        $this->assertTrue( $range->has(new Element(-1000)) );
        $this->assertTrue( $range->has(new Element(-100)) );
        $this->assertTrue( $range->has(new Element(-10)) );
        $this->assertTrue( $range->has(new Element(-1)) );
        $this->assertTrue( $range->has(new Element(0)) );
        $this->assertTrue( $range->has(new Element(1)) );
        $this->assertTrue( $range->has(new Element(10)) );
        $this->assertTrue( $range->has(new Element(100)) );
        $this->assertTrue( $range->has(new Element(1000)) );
    }

    public function testUnionDomain()
    {
        // Starts with rangeA, ends with rangeB
        $rangeA = new IntegerRange(new Element(1), new Element(9));
        $rangeB = new IntegerRange(new Element(7), new Element(15));
        $this->assertTrue( $rangeB->reaches($rangeA) , 'rangeB reaches rangeA');
        $resultList = $rangeA->union($rangeB);
        $rangeC = $resultList->get(0);

        // 0 <1 7 8 9> 15 16
        // 0 1 <7 8 9 15> 16
        // 0 <1 7 8 9 15> 16
        $this->assertFalse( $rangeC->has( new Element(0) ) );
        $this->assertTrue( $rangeC->has( new Element(1) ) );
        $this->assertTrue( $rangeC->has( new Element(8) ) );
        $this->assertTrue( $rangeC->has( new Element(15) ) );
        $this->assertFalse( $rangeC->has( new Element(16) ) );

        // Starts with rangeB, ends with rangeA
        $rangeA = new IntegerRange(new Element(15), new Element(18));
        $rangeB = new IntegerRange(new Element(1), new Element(16));
        $this->assertTrue( $rangeB->reaches($rangeA) , 'rangeB reaches rangeA');
        $resultList = $rangeA->union($rangeB);
        $rangeC = $resultList->get(0);

        // 0 1 <15 16 18> 19
        // 0 <1 15 16> 18 19
        // 0 <1 15 16 18> 19
        $this->assertFalse( $rangeC->has( new Element(0) ) );
        $this->assertTrue( $rangeC->has( new Element(1) ) );
        $this->assertTrue( $rangeC->has( new Element(18) ) );
        $this->assertFalse( $rangeC->has( new Element(19) ) );

        // RangeA covers rangeB
        $rangeA = new IntegerRange(new Element(1), new Element(20));
        $rangeB = new IntegerRange(new Element(5), new Element(17));
        $this->assertTrue( $rangeB->reaches($rangeA) , 'rangeB reaches rangeA');
        $resultList = $rangeA->union($rangeB);
        $rangeC = $resultList->get(0);

        // 0 <1 5 17 20> 21
        // 0 1 <5 17> 20 21
        // 0 <1 5 17 20> 21
        $this->assertFalse( $rangeC->has( new Element(0) ) );
        $this->assertTrue( $rangeC->has( new Element(1) ) );
        $this->assertTrue( $rangeC->has( new Element(20) ) );
        $this->assertFalse( $rangeC->has( new Element(21) ) );

        // RangeB covers rangeA
        $rangeA = new IntegerRange(new Element(18), new Element(26));
        $rangeB = new IntegerRange(new Element(-92), new Element(57));
        $this->assertTrue( $rangeB->reaches($rangeA) , 'rangeB reaches rangeA');
        $resultList = $rangeA->union($rangeB);
        $rangeC = $resultList->get(0);

        // -92 0 1 <18 26> 57
        // <-92 0 1 18 26 57>
        // <-92 0 1 18 26 57>
        $this->assertFalse( $rangeC->has( new Element(-93) ) );
        $this->assertTrue( $rangeC->has( new Element(-92) ) );
        $this->assertTrue( $rangeC->has( new Element(57) ) );
        $this->assertFalse( $rangeC->has( new Element(58) ) );

        // Both ranges are equals
        $rangeA = new IntegerRange(new Element(1), new Element(10));
        $rangeB = new IntegerRange(new Element(1), new Element(10));
        $this->assertTrue( $rangeB->reaches($rangeA) , 'rangeB reaches rangeA');
        $resultList = $rangeA->union($rangeB);
        $rangeC = $resultList->get(0);

        // 0 <1 10> 11
        // 0 <1 10> 11
        // 0 <1 10> 11
        $this->assertFalse( $rangeC->has( new Element(0) ) );
        $this->assertTrue( $rangeC->has( new Element(1) ) );
        $this->assertTrue( $rangeC->has( new Element(10) ) );
        $this->assertFalse( $rangeC->has( new Element(11) ) );

        // Ranges that never meet each other, result in a composite domain
        $rangeA = new IntegerRange(new Element(1), new Element(10));
        $rangeB = new IntegerRange(new Element(18), new Element(22));
        $this->assertFalse( $rangeB->reaches($rangeA) , 'rangeB dont reaches rangeA');
        $resultList = $rangeA->union($rangeB);
        $rangeC = $resultList->get(0);
        $rangeD = $resultList->get(1);

        // 0 <1 10> 18 22 23
        // 0 1 10 <18 22> 23
        // 0 <1 10> <18 22> 23
        $this->assertFalse( $rangeC->has(new Element(0)) );
        $this->assertTrue( $rangeC->has(new Element(1)) );
        $this->assertTrue( $rangeC->has(new Element(10)) );
        $this->assertFalse( $rangeC->has(new Element(11)) );
        //
        $this->assertFalse( $rangeD->has(new Element(17)) );
        $this->assertTrue( $rangeD->has(new Element(18)) );
        $this->assertTrue( $rangeD->has(new Element(22)) );
        $this->assertFalse( $rangeD->has(new Element(23)) );

        //--------------------------------------------------------------------------
        // (( Infinity Tests ))
        //--------------------------------------------------------------------------
        // infinity over range
        //       > <
        // 0 <1 2 3> 4 5 6 7
        //       > <
        $rangeA = new IntegerRange(new Element(null), new Element(null));
        $rangeB = new IntegerRange(new Element(1), new Element(3));
        $resultList = $rangeA->union($rangeB);
        $this->assertEquals(1, $resultList->count());
        $resultRange = $resultList->get(0);
        $this->assertEquals(null, $resultRange->getStartValue()->getValue());
        $this->assertEquals(null, $resultRange->getEndValue()->getValue());

        // range over infinity
        // 0 <1 2 3 4 5 6> 7
        //        > <
        //        > <
        $rangeA = new IntegerRange(new Element(1), new Element(6));
        $rangeB = new IntegerRange(new Element(null), new Element(null));
        $resultList = $rangeA->union($rangeB);
        $this->assertEquals(1, $resultList->count());
        $resultRange = $resultList->get(0);
        $this->assertEquals(null, $resultRange->getStartValue()->getValue());
        $this->assertEquals(null, $resultRange->getEndValue()->getValue());

        // infinity from left with range (precision cases)
        // 0 1 2 3> 4 5 6 7
        // 0 1 2 3 <4 5 6 7>
        // 0 1 2 3  4 5 6 7>
        $rangeA = new IntegerRange(new Element(null), new Element(3));
        $rangeB = new IntegerRange(new Element(4), new Element(7));
        $resultList = $rangeA->union($rangeB);
        $this->assertEquals(1, $resultList->count());
        $resultRange = $resultList->get(0);
        $this->assertEquals(null, $resultRange->getStartValue()->getValue());
        $this->assertEquals(7, $resultRange->getEndValue()->getValue());

        // infinity from left with infinity (precision cases)
        // 0 1 2 3> 4 5 6 7
        // 0 1 2 3 <4 5 6 7
        //        ><
        $rangeA = new IntegerRange(new Element(null), new Element(3));
        $rangeB = new IntegerRange(new Element(4), new Element(null));
        $resultList = $rangeA->union($rangeB);
        $this->assertEquals(1, $resultList->count());
        $resultRange = $resultList->get(0);
        $this->assertEquals(null, $resultRange->getStartValue()->getValue());
        $this->assertEquals(null, $resultRange->getEndValue()->getValue());

        // infinity from right with range (precision cases)
        //  0 1 2 3 <4 5 6 7
        // <0 1 2 3> 4 5 6 7
        // <0
        $rangeA = new IntegerRange(new Element(4), new Element(null));
        $rangeB = new IntegerRange(new Element(0), new Element(3));
        $resultList = $rangeA->union($rangeB);
        $this->assertEquals(1, $resultList->count());
        $resultRange = $resultList->get(0);
        $this->assertEquals(0, $resultRange->getStartValue()->getValue());
        $this->assertEquals(null, $resultRange->getEndValue()->getValue());

        // infinity from right with infinity (precision cases)
        // 0 1 2 3 <4 5 6 7
        // 0 1 2 3> 4 5 6 7
        //        ><
        $rangeA = new IntegerRange(new Element(4), new Element(null));
        $rangeB = new IntegerRange(new Element(null), new Element(3));
        $resultList = $rangeA->union($rangeB);
        $this->assertEquals(1, $resultList->count());
        $resultRange = $resultList->get(0);
        $this->assertEquals(null, $resultRange->getStartValue()->getValue());
        $this->assertEquals(null, $resultRange->getEndValue()->getValue());

        // infinity from left with range (cross cases)
        // 0 1 2  3> 4 5 6 7
        // 0 1 2 <3  4 5 6 7>
        // 0 1 2  3  4 5 6 7>
        $rangeA = new IntegerRange(new Element(null), new Element(3));
        $rangeB = new IntegerRange(new Element(3), new Element(7));
        $resultList = $rangeA->union($rangeB);
        $this->assertEquals(1, $resultList->count());
        $resultRange = $resultList->get(0);
        $this->assertEquals(null, $resultRange->getStartValue()->getValue());
        $this->assertEquals(7, $resultRange->getEndValue()->getValue());

        // infinity from left with infinity (cross cases)
        // 0 1 2  3> 4 5 6 7
        // 0 1 2 <3  4 5 6 7
        //        ><
        $rangeA = new IntegerRange(new Element(null), new Element(3));
        $rangeB = new IntegerRange(new Element(3), new Element(null));
        $resultList = $rangeA->union($rangeB);
        $this->assertEquals(1, $resultList->count());
        $resultRange = $resultList->get(0);
        $this->assertEquals(null, $resultRange->getStartValue()->getValue());
        $this->assertEquals(null, $resultRange->getEndValue()->getValue());

        // infinity from right with range (cross cases)
        //  0 1 2 3 <4  5 6 7
        // <0 1 2 3  4> 5 6 7
        // <0 1 2 3  4  5 6 7
        $rangeA = new IntegerRange(new Element(4), new Element(null));
        $rangeB = new IntegerRange(new Element(0), new Element(4));
        $resultList = $rangeA->union($rangeB);
        $this->assertEquals(1, $resultList->count());
        $resultRange = $resultList->get(0);
        $this->assertEquals(0, $resultRange->getStartValue()->getValue());
        $this->assertEquals(null, $resultRange->getEndValue()->getValue());

        // infinity from left with range (non-cross cases)
        // 0 1 2 3> 4 5  6 7
        // 0 1 2 3  4 5 <6 7>
        // 0 1 2 3> 4 5 <6 7>
        $rangeA = new IntegerRange(new Element(null), new Element(3));
        $rangeB = new IntegerRange(new Element(6), new Element(7));
        $resultList = $rangeA->union($rangeB);
        $this->assertEquals(2, $resultList->count());
        $resultRange1 = $resultList->get(0);
        $this->assertEquals(null, $resultRange1->getStartValue()->getValue());
        $this->assertEquals(3, $resultRange1->getEndValue()->getValue());
        $resultRange2 = $resultList->get(1);
        $this->assertEquals(6, $resultRange2->getStartValue()->getValue());
        $this->assertEquals(7, $resultRange2->getEndValue()->getValue());

        // infinity from left with infinity (non-cross cases)
        // 0 1 2 3> 4 5  6 7
        // 0 1 2 3  4 5 <6 7
        // 0 1 2 3> 4 5 <6 7
        $rangeA = new IntegerRange(new Element(null), new Element(3));
        $rangeB = new IntegerRange(new Element(6), new Element(null));
        $resultList = $rangeA->union($rangeB);
        $this->assertEquals(2, $resultList->count());
        $resultRange1 = $resultList->get(0);
        $this->assertEquals(null, $resultRange1->getStartValue()->getValue());
        $this->assertEquals(3, $resultRange1->getEndValue()->getValue());
        $resultRange2 = $resultList->get(1);
        $this->assertEquals(6, $resultRange2->getStartValue()->getValue());
        $this->assertEquals(null, $resultRange2->getEndValue()->getValue());

        // infinity from right with range (non-cross cases)
        //  0 1 2  3 <4 5 6 7
        // <0 1 2> 3  4 5 6 7
        // <0 1 2> 3 <4 5 6 7
        $rangeA = new IntegerRange(new Element(0), new Element(2));
        $rangeB = new IntegerRange(new Element(4), new Element(null));
        $resultList = $rangeA->union($rangeB);
        $this->assertEquals(2, $resultList->count());
        $resultRange1 = $resultList->get(0);
        $this->assertEquals(0, $resultRange1->getStartValue()->getValue());
        $this->assertEquals(2, $resultRange1->getEndValue()->getValue());
        $resultRange2 = $resultList->get(1);
        $this->assertEquals(4, $resultRange2->getStartValue()->getValue());
        $this->assertEquals(null, $resultRange2->getEndValue()->getValue());

        // infinity from right with infinity (non-cross cases)
        // 0 1  2 3 <4 5 6 7
        // 0 1> 2 3  4 5 6 7
        // 0 1> 2 3 <4 5 6 7
        $rangeA = new IntegerRange(new Element(null), new Element(1));
        $rangeB = new IntegerRange(new Element(4), new Element(null));
        $resultList = $rangeA->union($rangeB);
        $this->assertEquals(2, $resultList->count());
        $resultRange1 = $resultList->get(0);
        $this->assertEquals(null, $resultRange1->getStartValue()->getValue());
        $this->assertEquals(1, $resultRange1->getEndValue()->getValue());
        $resultRange2 = $resultList->get(1);
        $this->assertEquals(4, $resultRange2->getStartValue()->getValue());
        $this->assertEquals(null, $resultRange2->getEndValue()->getValue());
    }

    public function testSubtractDomain()
    {
        // Starts with rangeA, and rangeB removes a tail of rangeA
        $rangeA = new IntegerRange(new Element(1), new Element(9));
        $rangeB = new IntegerRange(new Element(7), new Element(15));
        $resultList = $rangeA->difference($rangeB);
        $rangeC = $resultList->get(0);

        // 0 <1 6 7 9> 15 16
        // 0 1 6 <7 9 15> 16
        // 0 <1 6> 7 9 15 16
        $this->assertFalse( $rangeC->has(new Element(0)) );
        $this->assertTrue( $rangeC->has(new Element(1)) );
        $this->assertTrue( $rangeC->has(new Element(4)) );
        $this->assertTrue( $rangeC->has(new Element(6)) );
        $this->assertFalse( $rangeC->has(new Element(7)) );

        // Starts with rangeB, and rangeB removes the first part of rangeA
        $rangeA = new IntegerRange(new Element(9), new Element(26));
        $rangeB = new IntegerRange(new Element(1), new Element(12));
        $resultList = $rangeA->difference($rangeB);
        $rangeC = $resultList->get(0);

        // 0 1 <9 12 13 26> 27
        // 0 <1 9 12> 13 26 27
        // 0 1 9 12 <13 26> 27
        $this->assertFalse( $rangeC->has(new Element(12)) );
        $this->assertTrue( $rangeC->has(new Element(13)) );
        $this->assertTrue( $rangeC->has(new Element(17)) );
        $this->assertTrue( $rangeC->has(new Element(26)) );
        $this->assertFalse( $rangeC->has(new Element(27)) );

        // RangeB covers all rangeA, and the result is an empty domain.
        $rangeA = new IntegerRange(new Element(5), new Element(8));
        $rangeB = new IntegerRange(new Element(1), new Element(12));
        $resultList = $rangeA->difference($rangeB);

        // 0 1 4 <5 7 8> 11 12 13
        // 0 <1 4 5 7 8 11 12> 13
        // 0 1 4 5 7 8 11 12 13
        $this->assertEquals(0, $resultList->count());


        // RangeA and rangeB are the same domain, and the result is an empty domain
        $rangeA = new IntegerRange(new Element(216), new Element(300));
        $rangeB = new IntegerRange(new Element(216), new Element(300));
        $resultList = $rangeA->difference($rangeB);

        // 215 <216 300> 301
        // 215 <216 300> 301
        // 215 216 300 301
        $this->assertEquals(0, $resultList->count());


        // RangeA covers all rangeB, and rangeB splits rangeA resulting a composite domain
        $rangeA = new IntegerRange(new Element(1), new Element(30));
        $rangeB = new IntegerRange(new Element(12), new Element(21));
        $resultList = $rangeA->difference($rangeB);
        $rangeC = $resultList->get(0);
        $rangeD = $resultList->get(1);

        // 0 <1 11 12 21 22 30> 31
        // 0 1 11 <12 21> 22 30 31
        // 0 <1 11> 12 21 <22 30> 31
        $this->assertFalse( $rangeC->has(new Element(0)) );
        $this->assertTrue( $rangeC->has(new Element(1)) );
        $this->assertTrue( $rangeC->has(new Element(11)) );
        $this->assertFalse( $rangeC->has(new Element(12)) );
        //
        $this->assertFalse( $rangeD->has(new Element(21)) );
        $this->assertTrue( $rangeD->has(new Element(22)) );
        $this->assertTrue( $rangeD->has(new Element(30)) );
        $this->assertFalse( $rangeD->has(new Element(31)) );


        // rangeA and rangeB never meet each other, so the result is rangeA
        $rangeA = new IntegerRange(new Element(1), new Element(22));
        $rangeB = new IntegerRange(new Element(56), new Element(198));
        $resultList = $rangeA->difference($rangeB);
        $rangeC = $resultList->get(0);

        // 0 <1 22> 23 56 198
        // 0 1 22 23 <56 198>
        // 0 <1 22> 23 56 198
        $this->assertFalse( $rangeC->has(new Element(0)) );
        $this->assertTrue( $rangeC->has(new Element(1)) );
        $this->assertTrue( $rangeC->has(new Element(22)) );
        $this->assertFalse( $rangeC->has(new Element(23)) );
    }

    public function testPrecisionOnAddOperation()
    {
        $rangeA = new IntegerRange(new Element(1), new Element(8), 1);
        $rangeB = new IntegerRange(new Element(9), new Element(22), 1);
        $this->assertTrue( $rangeA->reaches($rangeB) );
        $resultList = $rangeA->union($rangeB);
        $rangeC = $resultList->get(0);
        //
        $this->assertFalse( $rangeC->has(new Element(0)) );
        $this->assertTrue( $rangeC->has(new Element(1)) );
        $this->assertTrue( $rangeC->has(new Element(22)) );
        $this->assertFalse( $rangeC->has(new Element(23)) );

        $rangeA = new IntegerRange(new Element(25), new Element(32), 1);
        $rangeB = new IntegerRange(new Element(1), new Element(24), 1);
        $resultList = $rangeA->union($rangeB);
        $rangeC = $resultList->get(0);
        //
        $this->assertFalse( $rangeC->has(new Element(0)) );
        $this->assertTrue( $rangeC->has(new Element(1)) );
        $this->assertTrue( $rangeC->has(new Element(32)) );
        $this->assertFalse( $rangeC->has(new Element(33)) );

        $rangeA = new IntegerRange(new Element(10), new Element(22), 2);
        $rangeB = new IntegerRange(new Element(1), new Element(8), 2);
        $this->assertTrue( $rangeA->reaches($rangeB) );
        $resultList = $rangeA->union($rangeB);
        $rangeC = $resultList->get(0);

        // 0 <1 8> 9 10 22 23
        // 0 1 8 9 <10 22> 23
        // 0 <1 8 9 10 22> 23
        $this->assertFalse( $rangeC->has(new Element(0)) );
        $this->assertTrue( $rangeC->has(new Element(1)) );
        $this->assertTrue( $rangeC->has(new Element(22)) );
        $this->assertFalse( $rangeC->has(new Element(23)) );
    }

    public function testARangeReachesAnother()
    {
        $rangeA = new IntegerRange(new Element(10), new Element(20), 1);

        // reaches rangeA
        $rangeB = new IntegerRange(new Element(0), new Element(11), 1);
        $rangeC = new IntegerRange(new Element(19), new Element(30), 1);
        $this->assertTrue( $rangeB->reaches($rangeA) );
        $this->assertTrue( $rangeC->reaches($rangeA) );

        // don't reaches rangeA
        $rangeM = new IntegerRange(new Element(0), new Element(8), 1);
        $rangeN = new IntegerRange(new Element(22), new Element(30), 1);
        $this->assertFalse( $rangeM->reaches($rangeA) );
        $this->assertFalse( $rangeN->reaches($rangeA) );
    }
}
