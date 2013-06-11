<?php
use Mockery as m;
use DeSmart\Pagination\Environment;

class EnvironmentTest extends PHPUnit_Framework_TestCase {

  public function tearDown() {
    m::close();
  }

  public function testCreationOfEnvironment() {
    $env = $this->getEnvironment();
  }

  public function testPaginatorCanBeCreated() {
    $env = $this->getEnvironment();
    $request = Illuminate\Http\Request::create('http://foo.com', 'GET');
    $env->setRequest($request);

    $this->assertInstanceOf('DeSmart\Pagination\Paginator', $env->make(array('foo', 'bar'), 2, 2));
  }

  public function testCurrentPageCanBeRetrieved() {
    $env = $this->getEnvironment();
    $request = Illuminate\Http\Request::create('http://foo.com?page=2', 'GET');
    $env->setRequest($request);

    $this->assertEquals(2, $env->getCurrentPage());

    $env = $this->getEnvironment();
    $request = Illuminate\Http\Request::create('http://foo.com?page=-1', 'GET');
    $env->setRequest($request);

    $this->assertEquals(1, $env->getCurrentPage());
  }

  public function testCurrentUrlCanBeRetrievedFromRoute() {
    $route = m::mock('Illuminate\Routing\Route');
    $route->shouldReceive('getParameter')->with('page', null)->andReturn(2);
    $router = m::mock('Illuminate\Routing\Router');
    $router->shouldReceive('getCurrentRoute')->andReturn($route);

    $env = $this->getEnvironment($router);
    $request = Illuminate\Http\Request::create('http://foo.com', 'GET');
    $env->setRequest($request);

    $this->assertEquals(2, $env->getCurrentPage());
  }

  public function testSettingCurrentUrlOverrulesRequest() {
    $env = $this->getEnvironment();
    $request = Illuminate\Http\Request::create('http://foo.com?page=2', 'GET');
    $env->setRequest($request);
    $env->setCurrentPage(3);

    $this->assertEquals(3, $env->getCurrentPage());
  }

  public function testPaginationViewCanBeCreated() {
    $env = $this->getEnvironment();
    $paginator = m::mock('DeSmart\Pagination\Paginator');
    $env->getViewDriver()->shouldReceive('make')->once()->with('pagination::slider', array('environment' => $env, 'paginator' => $paginator))->andReturn('foo');

    $this->assertEquals('foo', $env->getPaginationView($paginator));
  }

  public function testPaginationWithCustomViewCanBeCreated() {
    $env = $this->getEnvironment();
    $paginator = m::mock('DeSmart\Pagination\Paginator');
    $env->getViewDriver()->shouldReceive('make')->once()->with($view = 'foo.test', array('environment' => $env, 'paginator' => $paginator))->andReturn('foo');

    $this->assertEquals('foo', $env->getPaginationView($paginator, $view));
  }

  protected function getEnvironment($router = null, $urlGenerator = null) {
    $request = m::mock('Illuminate\Http\Request');
    $view = m::mock('Illuminate\View\Environment');
    $trans = m::mock('Symfony\Component\Translation\TranslatorInterface');
    $view->shouldReceive('addNamespace')->once()->with('pagination', realpath(__DIR__.'/../vendor/illuminate/pagination/Illuminate/Pagination').'/views');

    $env = new Environment($request, $view, $trans, 'page');

    if(null === $router) {
      $route = m::mock('Illuminate\Routing\Route');
      $route->shouldReceive('getParameter')->with('page', null)->andReturn(null);
      $router = m::mock('Illuminate\Routing\Router');
      $router->shouldReceive('getCurrentRoute')->andReturn($route);
    }

    if(null === $urlGenerator) {
      $urlGenerator = m::mock('Illuminate\Routing\UrlGenerator');
    }

    $env->setRouter($router);
    $env->setUrlGenerator($urlGenerator);

    return $env;
  }

}
