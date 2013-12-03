<?php

use Mockery as m;
use DeSmart\Pagination\Paginator;

class PaginatorTest extends PHPUnit_Framework_TestCase {

  public function tearDown() {
    m::close();
  }

  public function testGetUrlProperlyFormatsUrl() {
    $p = new Paginator($env = m::mock('DeSmart\Pagination\Environment'), array('foo', 'bar', 'baz'), 3, 2);
    $env->shouldReceive('getCurrentUrl')->twice()->andReturn('http://foo.com');
    $env->shouldReceive('getPageName')->twice()->andReturn('page');

    $this->assertEquals('http://foo.com?page=1', $p->getUrl(1));
    $p->addQuery('foo', 'bar');
    $this->assertEquals('http://foo.com?page=1&foo=bar', $p->getUrl(1));
  }

  public function testGetUrlFromRoute() {
    $generator = m::mock('Illuminate\Routing\UrlGenerator');
    $request = m::mock('Illuminate\Http\Request');
    $request->shouldReceive('query')->once()->andReturn($query = array('a' => 1));

    $p = new Paginator($env = m::mock('DeSmart\Pagination\Environment'), array('foo', 'bar', 'baz'), 3, 2);
    $p->setUrlGenerator($generator);
    $env->shouldReceive('getRequest')->once()->andReturn($request);

    $env->shouldReceive('getPageName')->once()->andReturn('page');
    $generator->shouldReceive('route')->once()->with($name = 'test.route', array('a' => 1, 'page' => 1), null);
    $p->route($name);

    $p->getUrl(1);
  }

  public function testGetUrlFromRouteWithoutQuery() {
    $generator = m::mock('Illuminate\Routing\UrlGenerator');

    $p = new Paginator($env = m::mock('DeSmart\Pagination\Environment'), array('foo', 'bar', 'baz'), 3, 2);
    $p->setUrlGenerator($generator);
    $p->withoutQuery();
    $env->shouldReceive('getRequest')->never();
    $env->shouldReceive('getPageName')->andReturn('page');

    $generator->shouldReceive('route')->once()->with($name = 'test.route', array('page' => 1), null);
    $p->route($name);

    $p->getUrl(1);
  }

  public function testGetUrlFromRouteWithGeneratorArguments() {
    $generator = m::mock('Illuminate\Routing\UrlGenerator');
    $params = array('b' => 'foo');

    $p = new Paginator($env = m::mock('DeSmart\Pagination\Environment'), array('foo', 'bar', 'baz'), 3, 2);
    $p->setUrlGenerator($generator);
    $p->withoutQuery();
    $env->shouldReceive('getPageName')->andReturn('page');

    $generator->shouldReceive('route')->once()->with($name = 'test.route', array_merge($params, array('page' => 1)), $absolute = false);
    $p->route($name, $params, $absolute);

    $p->getUrl(1);
  }

  public function testGetUrlFromCurrentRoute() {
    $generator = m::mock('Illuminate\Routing\UrlGenerator');
    $route = m::mock('Illuminate\Routing\Route');
    $route->shouldReceive('parameters')->once()->andReturn($params = array('b' => 'foo'));
    $router = m::mock('Illuminate\Routing\Router');
    $router->shouldReceive('current')->once()->andReturn($route);

    $p = new Paginator($env = m::mock('DeSmart\Pagination\Environment'), array('foo', 'bar', 'baz'), 3, 2);
    $p->setUrlGenerator($generator);
    $p->setRouter($router);
    $p->withoutQuery();
    $p->useCurrentRoute();
    $env->shouldReceive('getPageName')->andReturn('page');

    $generator->shouldReceive('route')->once()->with(null, array_merge($params, array('page' => 1)), $route);

    $p->getUrl(1);
  }

  public function testPaginatorIsCountable() {
    $p = new Paginator($env = m::mock('DeSmart\Pagination\Environment'), array('foo', 'bar', 'baz'), 3, 2);

    $this->assertEquals(3, count($p));
  }

  public function testPaginatorIsIterable() {
    $p = new Paginator($env = m::mock('DeSmart\Pagination\Environment'), array('foo', 'bar', 'baz'), 3, 2);

    $this->assertInstanceOf('ArrayIterator', $p->getIterator());
    $this->assertEquals(array('foo', 'bar', 'baz'), $p->getIterator()->getArrayCopy());
  }

  public function testGetLinksCallsEnvironmentProperly() {
    $p = new Paginator($env = m::mock('DeSmart\Pagination\Environment'), array('foo', 'bar', 'baz'), 3, 2);
    $env->shouldReceive('getPaginationView')->once()->with($p, null)->andReturn('foo');

    $this->assertEquals('foo', $p->links());

    $p = new Paginator($env = m::mock('DeSmart\Pagination\Environment'), array('foo', 'bar', 'baz'), 3, 2);
    $env->shouldReceive('getPaginationView')->once()->with($p, $view = 'foo');

    $p->links($view);
  }

  public function testGetPagesRangeReturnsValidRangeWithoutProximity() {
    $p = m::mock('DeSmart\Pagination\Paginator[getLastPage]');
    $p->shouldReceive('getLastPage')->once()->andReturn($last_page = 10);

    $this->assertEquals(range(1, $last_page), $p->getPagesRange());
  }

  public function testGetPagesRangeReturnsValidRangeWithProximity() {
    $p = m::mock('DeSmart\Pagination\Paginator[getLastPage,getCurrentPage]');
    $p->shouldReceive('getLastPage')->andReturn($last_page = 10);
    $p->shouldReceive('getCurrentPage')->andReturn(5, 5, 1, $last_page);

    $p->pagesProximity(2);
    $this->assertEquals(array(3, 4, 5, 6, 7), $p->getPagesRange());

    $p->pagesProximity(10);
    $this->assertEquals(range(1, $last_page), $p->getPagesRange());

    $p->pagesProximity(2);
    $this->assertEquals(array(1, 2, 3, 4, 5), $p->getPagesRange());

    $p->pagesProximity(2);
    $this->assertEquals(array(6, 7, 8, 9, 10), $p->getPagesRange());
  }

  public function testGetPagesRangeWithProximityForShortPagesRange() {
    $p = m::mock('DeSmart\Pagination\Paginator[getLastPage,getCurrentPage]');
    $p->shouldReceive('getLastPage')->andReturn($last_page = 2);
    $p->shouldReceive('getCurrentPage')->andReturn(1, 2);

    $p->pagesProximity(4);
    $this->assertEquals(array(1, 2), $p->getPagesRange());

    $p->pagesProximity(4);
    $this->assertEquals(array(1, 2), $p->getPagesRange());

    $p->pagesProximity(1);
    $this->assertEquals(array(1, 2), $p->getPagesRange());
  }

  public function testCanShowFirstPage() {
    $p = m::mock('DeSmart\Pagination\Paginator[getPagesRange]');
    $p->shouldReceive('getPagesRange')->andReturn(array(1, 2, 3), array(2, 3, 4), array(5, 6, 7));

    $this->assertFalse($p->canShowFirstPage());
    $this->assertTrue($p->canShowFirstPage());
    $this->assertTrue($p->canShowFirstPage());
  }

  public function testCanShowLastPage() {
    $p = m::mock('DeSmart\Pagination\Paginator[getPagesRange,getLastPage]');
    $p->shouldReceive('getPagesRange')->andReturn(array(1, 2, 3), array(2, 3, 4), array(5, 6, 7));
    $p->shouldReceive('getLastPage')->andReturn(7);

    $this->assertTrue($p->canShowLastPage());
    $this->assertTrue($p->canShowLastPage());
    $this->assertFalse($p->canShowLastPage());
  }

}
