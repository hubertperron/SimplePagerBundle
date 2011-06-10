<?php

namespace Ideato\SimplePagerBundle\Tests\Paginator;

use Ideato\SimplePagerBundle\Paginator\Pager;

class QueryStub
{
    const SCALAR = 1;
}

class PagerTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->pager = new Pager($this->getMock('Ideato\SimplePagerBundle\Pager\sfParameterHolder'), 4);
    }

    public function testConstruct()
    {
        $this->assertEquals(4, $this->pager->getMaxPerPage());
        $this->assertInstanceOf('Ideato\SimplePagerBundle\Pager\sfParameterHolder', $this->pager->getParameterHolder());
    }

    public function testSetQuery()
    {
        $query = $this->getMockBuilder('\Doctrine\ORM\AbstractQuery')
                      ->disableOriginalConstructor()
                      ->setMethods(array('_doExecute', 'getSQL'))
                      ->getMock();
        $this->pager->setQuery($query);

        $this->assertInstanceOf(\get_class($query), $this->pager->getQuery());
    }

    public function testSetPage()
    {
        $this->pager->setPage(3);

        $this->assertEquals(3, $this->pager->getPage());
    }

    protected function buildPager()
    {
        return $this->getMockBuilder('Ideato\SimplePagerBundle\Paginator\Pager')
                    ->setMethods(array('getNbResults'))
                    ->setConstructorArgs(array($this->getMock('Ideato\SimplePagerBundle\Pager\sfParameterHolder'), 4))
                    ->getMock();
    }

    public function testHaveToPaginate()
    {
        $pager = $this->buildPager();

        $pager->expects($this->exactly(2))
              ->method('getNbResults')
              ->will($this->onConsecutiveCalls(2, 5));

        $this->assertFalse($pager->haveToPaginate());
        $this->assertTrue($pager->haveToPaginate());
    }

    public function testGetNextPage()
    {
        $pager = $this->getMockBuilder('Ideato\SimplePagerBundle\Paginator\Pager')
                      ->setMethods(array('getLastPage'))
                      ->setConstructorArgs(array($this->getMock('Ideato\SimplePagerBundle\Pager\sfParameterHolder'), 4))
                      ->getMock();

        $pager->expects($this->exactly(2))
              ->method('getLastPage')
              ->will($this->onConsecutiveCalls(5, 5));

        $pager->setPage(1);
        $this->assertEquals(2, $pager->getNextPage());

        $pager->setPage(5);
        $this->assertEquals(5, $pager->getNextPage());
    }

    /**
     * @expectedException Exception
     */
    public function testInitException()
    {
        $this->pager->init();
    }

    protected function buildQueryMock($results, $set_getResult = true)
    {
        $query = $this->getMockBuilder('\Doctrine\ORM\AbstractQuery')
                      ->disableOriginalConstructor()
                      ->setMethods(array('setMaxResults', 'setFirstResult', 'execute', 'getResult', '_doExecute', 'getSQL'))
                      ->getMock();

        if ($set_getResult)
        {
            $query->expects($this->once())
                  ->method('getResult')
                  ->with(3)
                  ->will($this->returnValue($results));
        }

        $query->expects($this->once())
              ->method('execute')
              ->will($this->returnValue($results));

        $query->expects($this->once())
              ->method('setFirstResult')
              ->with(0)
              ->will($this->returnValue($query));

        $query->expects($this->once())
              ->method('setMaxResults')
              ->with(4)
              ->will($this->returnValue($query));

        return $query;
    }

    protected function buildExtendedPager($query)
    {
        $pager = $this->getMockBuilder('Ideato\SimplePagerBundle\Paginator\Pager')
              ->setMethods(array('cloneQuery'))
              ->setConstructorArgs(array($this->getMock('Ideato\SimplePagerBundle\Pager\sfParameterHolder'), 4))
              ->getMock();

        $pager->expects($this->any())
              ->method('cloneQuery')
              ->will($this->returnValue($query));

        return $pager;
    }

    public function testInit()
    {
        $array = range(0, 9);

        $query = $this->buildQueryMock($array);

        $pager= $this->buildExtendedPager($query);
        $pager->setQuery($query);
        $pager->init();

        $this->assertEquals(10, $pager->getNbResults());
        $this->assertEquals(3, $pager->getLastPage());
        
    }

    public function testGetResults()
    {
        $array = range(0, 9);

        $query = $this->buildQueryMock($array, false);

        $pager= $this->buildExtendedPager($query);
        $pager->setQuery($query);

        $this->assertEquals($array, $pager->getResults());
        $this->assertEquals($array, $pager->getResults());
    }

    public function testGetLinks()
    {
        $links = range(1, 5);
        $array = range(1, 21);
        $query = $this->buildQueryMock($array);

        $pager= $this->buildExtendedPager($query);
        $pager->setQuery($query);
        $pager->init();

        $this->assertEquals($links, $pager->getLinks(5));
        $this->assertEquals(5, $pager->getCurrentMaxLink());
    }

    public function testSetCursor()
    {
        $array = range(0, 9);
        $query = $this->buildQueryMock($array);

        $pager= $this->buildExtendedPager($query);
        $pager->setQuery($query);
        $pager->init();

        $pager->setCursor(-1);
        $this->assertEquals(1, $pager->getCursor());

        $pager->setCursor(1000);
        $this->assertEquals(10, $pager->getCursor());

        $pager->setCursor(5);
        $this->assertEquals(5, $pager->getCursor());

    }

    public function testGetObjectByCursor()
    {
        $array = range(0, 9);
        $query = $this->buildQueryMock($array);

        $pager= $this->buildExtendedPager($query);
        $pager->setQuery($query);
        $pager->init();

        $this->assertEquals(0, $pager->getObjectByCursor(-1));
        $this->assertEquals(9, $pager->getObjectByCursor(1000));
        $this->assertEquals(4, $pager->getObjectByCursor(5));
    }

    public function testGetCurrent()
    {
        $array = range(0, 9);
        $query = $this->buildQueryMock($array);

        $pager= $this->buildExtendedPager($query);
        $pager->setQuery($query);
        $pager->init();

        $this->assertEquals(0, $pager->getCurrent());
    }

    public function testGetNext()
    {
        $array = range(0, 9);
        $query = $this->buildQueryMock($array);

        $pager= $this->buildExtendedPager($query);
        $pager->setQuery($query);
        $pager->init();

        $pager->setCursor(-1);
        $this->assertEquals(1, $pager->getNext());

        $pager->setCursor(1000);
        $this->assertEquals(null, $pager->getNext());

        $pager->setCursor(5);
        $this->assertEquals(5, $pager->getNext());
    }

    public function testGetPrevious()
    {
        $array = range(0, 9);
        $query = $this->buildQueryMock($array);

        $pager= $this->buildExtendedPager($query);
        $pager->setQuery($query);
        $pager->init();

        $pager->setCursor(-1);
        $this->assertEquals(null, $pager->getPrevious());

        $pager->setCursor(10);
        $this->assertEquals(8, $pager->getPrevious());

        $pager->setCursor(5);
        $this->assertEquals(3, $pager->getPrevious());
    }

    public function testGetFirstIndice()
    {
        $this->assertEquals(1, $this->pager->getFirstIndice());

        $this->pager->setPage(2);
        $this->assertEquals(5, $this->pager->getFirstIndice());
    }

    public function testGetLastIndice()
    {
        $this->assertEquals(0, $this->pager->getLastIndice());

        $array = range(0, 9);
        $query = $this->buildQueryMock($array);

        $pager= $this->buildExtendedPager($query);
        $pager->setQuery($query);
        $pager->init();

        $pager->setPage(12);
        $this->assertEquals(10, $pager->getLastIndice());

        $pager->setPage(2);
        $this->assertEquals(8, $pager->getLastIndice());
    }

    public function testGetPreviousPage()
    {
        $this->assertEquals(1, $this->pager->getPreviousPage());

        $this->pager->setPage(3);
        $this->assertEquals(2, $this->pager->getPreviousPage());
    }

    public function testSetMaxPerPage()
    {
        $this->pager->setMaxPerPage(10);
        $this->assertEquals(10, $this->pager->getMaxPerPage());
        $this->assertEquals(1, $this->pager->getPage());

        $this->pager->setMaxPerPage(0);
        $this->assertEquals(0, $this->pager->getMaxPerPage());
        $this->assertEquals(0, $this->pager->getPage());

        $this->pager->setMaxPerPage(-1);
        $this->assertEquals(1, $this->pager->getMaxPerPage());
        $this->assertEquals(1, $this->pager->getPage());
    }

    public function testIsFirstPage()
    {
        $this->assertTrue($this->pager->isFirstPage());

        $this->pager->setPage(3);
        $this->assertFalse($this->pager->isFirstPage());
    }

    public function testIsLastPage()
    {
        $this->assertTrue($this->pager->isLastPage());
    }

    public function testGetParameterHolder()
    {
        $this->assertInstanceOf('Ideato\SimplePagerBundle\Pager\sfParameterHolder', $this->pager->getParameterHolder());
    }

    public function testIterator()
    {
        $array = range(0, 2);
        $query = $this->buildQueryMock($array);

        $pager= $this->buildExtendedPager($query);
        $pager->setQuery($query);
        $pager->init();
        $pager->rewind();

        $this->assertEquals(0, $pager->current());
        $this->assertEquals(0, $pager->key());

        $pager->next();
        $this->assertEquals(1, $pager->current());
        $this->assertEquals(1, $pager->key());
        $this->assertTrue($pager->valid());

        $pager->next();
        $pager->next();
        $this->assertFalse($pager->valid());

        $this->assertEquals(3, $pager->count());
    }

    public function testGetScalarHydrationValue()
    {
        $pager = new Pager($this->getMock('Ideato\SimplePagerBundle\Pager\sfParameterHolder'), 4);
        $pager->setQueryClass('Ideato\SimplePagerBundle\Tests\Paginator\QueryStub');
        $pager->setQueryScalarHydrationMode('SCALAR');

        $this->assertEquals(1, $pager->getScalarHydrationValue());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetQueryException()
    {
        $this->pager->setQuery(new QueryStub);
    }
}
