<?php
use Mockery as m;
use DeSmart\Pagination\Factory;

class FactoryTest extends PHPUnit_Framework_TestCase {

  public function setUp() {
    m::getConfiguration()->allowMockingNonExistentMethods(false);
  }

  public function tearDown() {
    m::close();
  }

  public function testCreationOfFactory() {
    $env = $this->getFactory();
  }

  public function testPaginatorCanBeCreated() {
    $env = $this->getFactory();
    $request = Illuminate\Http\Request::create('http://foo.com', 'GET');
    $env->setRequest($request);

    $this->assertInstanceOf('DeSmart\Pagination\Paginator', $env->make(array('foo', 'bar'), 2, 2));
  }

  public function testCurrentPageCanBeRetrieved() {
    $env = $this->getFactory();
    $request = Illuminate\Http\Request::create('http://foo.com?page=2', 'GET');
    $env->setRequest($request);

    $this->assertEquals(2, $env->getCurrentPage());

    $env = $this->getFactory();
    $request = Illuminate\Http\Request::create('http://foo.com?page=-1', 'GET');
    $env->setRequest($request);

    $this->assertEquals(1, $env->getCurrentPage());
  }

  public function testCurrentUrlCanBeRetrievedFromRoute() {
    $route = m::mock('Illuminate\Routing\Route');
    $route->shouldReceive('parameter')->with('page', null)->andReturn(2);
    $router = m::mock('Illuminate\Routing\Router');
    $router->shouldReceive('current')->andReturn($route);

    $env = $this->getFactory($router);
    $request = Illuminate\Http\Request::create('http://foo.com', 'GET');
    $env->setRequest($request);

    $this->assertEquals(2, $env->getCurrentPage());
  }

  public function testSettingCurrentUrlOverrulesRequest() {
    $env = $this->getFactory();
    $request = Illuminate\Http\Request::create('http://foo.com?page=2', 'GET');
    $env->setRequest($request);
    $env->setCurrentPage(3);

    $this->assertEquals(3, $env->getCurrentPage());
  }

  public function testPaginationViewCanBeCreated() {
    $env = $this->getFactory();
    $paginator = m::mock('DeSmart\Pagination\Paginator');
    $env->getViewFactory()->shouldReceive('make')->once()->with('pagination::slider', array('environment' => $env, 'paginator' => $paginator))->andReturn('foo');

    $this->assertEquals('foo', $env->getPaginationView($paginator));
  }

  public function testPaginationWithCustomViewCanBeCreated() {
    $env = $this->getFactory();
    $paginator = m::mock('DeSmart\Pagination\Paginator');
    $env->getViewFactory()->shouldReceive('make')->once()->with($view = 'foo.test', array('environment' => $env, 'paginator' => $paginator))->andReturn('foo');

    $this->assertEquals('foo', $env->getPaginationView($paginator, $view));
  }

  protected function getFactory($router = null, $urlGenerator = null) {
    $request = m::mock('Illuminate\Http\Request');
    $view = m::mock('Illuminate\View\Factory');
    $trans = m::mock('Symfony\Component\Translation\TranslatorInterface');
    $view->shouldReceive('addNamespace')->once()->with('pagination', realpath(__DIR__.'/../vendor/illuminate/pagination/Illuminate/Pagination').'/views');

    $env = new Factory($request, $view, $trans, 'page');

    if(null === $router) {
      $route = m::mock('Illuminate\Routing\Route');
      $route->shouldReceive('parameter')->with('page', null)->andReturn(null);
      $router = m::mock('Illuminate\Routing\Router');
      $router->shouldReceive('current')->andReturn($route);
    }

    if(null === $urlGenerator) {
      $urlGenerator = m::mock('Illuminate\Routing\UrlGenerator');
    }

    $env->setRouter($router);
    $env->setUrlGenerator($urlGenerator);

    return $env;
  }

}
